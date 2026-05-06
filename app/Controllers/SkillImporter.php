<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class SkillImporter extends Controller
{
    /**
     * Импорт из текстового файла с табуляцией (\t)
     * Путь: writable/imports/skills_data.txt
     */
    public function import()
    {
        $filePath = WRITEPATH . 'imports/skills_data.txt';
    
    if (!file_exists($filePath)) {
        return "Файл не найден в: " . $filePath;
    }

    // 1. Читаем сырые данные
    $rawContent = file_get_contents($filePath);

    // 2. Определяем кодировку и конвертируем в UTF-8
    // UTF-16LE часто идет с BOM или без, mb_convert_encoding справится
    $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE, UTF-16, Windows-1251, ASCII');

    // 3. Создаем временный указатель на файл из сконвертированной строки
    $handle = fopen('php://temp', 'r+');
    fwrite($handle, $content);
    rewind($handle);

    $db = \Config\Database::connect();
    $db->transStart(); 

    // Выставляем локаль для корректного парсинга многобайтовых строк
    setlocale(LC_ALL, 'ru_RU.UTF-8');

    // Пропускаем заголовки
    fgetcsv($handle, 0, "\t"); 

    while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
        if (empty($row) || count($row) < 6) continue;

        // Теперь print_r должен показать нормальный русский текст
        //print_r($row[2]); die; 

        $skillData = [
            'domain'      => trim($row[1]),
            'title'       => trim($row[2]),
            'description' => trim($row[3]),
            'level'       => 1
        ];
        $db->table('skills')->insert($skillData);
        $skillId = $db->insertID();

        // Парсим списки (разделитель внутри ячейки оставляем ;)
        
        $purposes     = explode('|', $row[4]);
        $instructions = explode('|', $row[5]); // Если в инструкции тоже |, замените разделитель

        // ПРОВЕРКА СООТВЕТСТВИЯ
        if (count($purposes) !== count($instructions)) {
            $db->transRollback();
            return "Ошибка в строке (Навык: '{$row[2]}'): " . 
                "Количество целей (" . count($purposes) . ") не совпадает с " . 
                "количеством инструкций (" . count($instructions) . "). " .
                "Проверьте разделители '|'.";
        }
        // 3. Склеиваем их в skill_stages
        // Используем count($purposes), так как массивы должны быть параллельны
        $maxStages = count($purposes);

        for ($i = 0; $i < $maxStages; $i++) {
            $desc = trim($purposes[$i] ?? '');
            $inst = trim($instructions[$i] ?? '');

            // Пропускаем, если обе части пусты
            if (empty($desc) && empty($inst)) continue;

            $db->table('skill_stages')->insert([
                'skill_id'    => $skillId,
                'description' => $desc,            // Из колонки Purposes
                'instruction' => $inst,            // Из колонки Instructions
                'order_index' => $i + 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    fclose($handle);
    $db->transComplete();

    if ($db->transStatus() === FALSE) {
        return "Ошибка транзакции. Проверьте структуру данных.";
    }

    return "Данные успешно импортированы (UTF-8 коррекция применена).";
    }
}