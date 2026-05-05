<?php 

namespace App\Controllers;

use App\Models\SkillModel;
use CodeIgniter\API\ResponseTrait;

class Skill extends BaseController
{
    use ResponseTrait;

    public function getList()
    {
        $model = model('SkillModel');
        
        $child_id = $this->request->getVar('child_id');
        $status   = $this->request->getVar('status'); // фильтр: not_started, in_progress, mastered

        if (!$child_id) {
            return $this->fail('ID ребенка обязателен');
        }

        $skills = $model->getList($child_id, $status);

        return $this->respond($skills);
    }

    public function updateStatus()
    {
        $model = model('SkillModel');
        
        $childId = $this->request->getVar('child_id');
        $skillId = $this->request->getVar('skill_id');
        $status  = $this->request->getVar('status');

        if (!$childId || !$skillId) {
            return $this->fail('Недостаточно данных для обновления статуса');
        }

        $result = $model->updateStatus($childId, $skillId, $status);

        return $this->respond(['success' => $result]);
    }

    public function updateStage()
    {
        $SkillStageModel = model('SkillStageModel'); // Работаем через модель этапов
        
        $childId     = $this->request->getVar('child_id');
        $stageId     = $this->request->getVar('stage_id');
        $isCompleted = $this->request->getVar('is_completed');

        if (!$childId || !$stageId) {
            return $this->fail('Недостаточно данных для обновления этапа');
        }

        $result = $SkillStageModel->updateItem($childId, $stageId, $isCompleted);

        return $this->respond(['success' => $result]);
    }
}