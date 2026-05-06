<?php 

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Child extends BaseController
{
    use ResponseTrait;
    public function getItem(){
        $model = model('ChildModel');
        $child_id = $this->request->getVar('child_id');

        if (!$child_id) {
            return $this->failNotFound('ID ребенка не указан');
        }

        $child = $model->getItem($child_id);

        if (!$child) {
            return $this->failNotFound('Ребенок не найден');
        }

        return $this->respond($child);
    }
    public function updateImage()
    {
        $ChildModel = model('ChildModel');
        
        $childId = $this->request->getPost('child_id');
        $file = $this->request->getFile('avatar');

        if (!$childId) {
            return $this->fail('ID ребенка не указан');
        }

        if (!$file || !$file->isValid()) {
            return $this->fail('Файл не загружен или поврежден');
        }

        if (!$file->hasMoved() && !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return $this->fail('Недопустимый формат файла (разрешены jpg, png, webp)');
        }

        $child = $ChildModel->find($childId);
        if (!$child) {
            return $this->failNotFound('Ребенок не найден');
        }

        $newName = $file->getRandomName();
        $uploadPath = 'uploads/avatars/';
        
        if ($file->move(FCPATH . $uploadPath, $newName)) {
            
            $newAvatarUrl = base_url($uploadPath . $newName);
            $oldAvatarPath = $child['avatar'];

            $ChildModel->update($childId, [
                'avatar' => $uploadPath . $newName
            ]);

            if ($oldAvatarPath) {
                $filename = basename($oldAvatarPath);
                $fullPath = FCPATH . $uploadPath . $filename;
                if (file_exists($fullPath) && is_file($fullPath)) {
                    unlink($fullPath);
                }
            }

            return $this->respond([
                'status'  => 'success',
                'message' => 'Аватар успешно обновлен',
                'avatar'  => $newAvatarUrl
            ]);
        }

        return $this->fail('Не удалось переместить файл');
    }
}