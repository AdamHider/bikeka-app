<?php 

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;

class Dashboard extends BaseController {
    use ResponseTrait;

    public function getStats($childId) {
        $SkillToChildModel = model('SkillToChildModel');
        
        $stats = [
            'not_started' => $SkillToChildModel->where(['child_id' => $childId, 'status' => 'not_started'])->countAllResults(),
            'in_progress' => $SkillToChildModel->where(['child_id' => $childId, 'status' => 'in_progress'])->countAllResults(),
            'mastered'    => $SkillToChildModel->where(['child_id' => $childId, 'status' => 'mastered'])->countAllResults(),
        ];

        return $this->respond($stats);
    }
}