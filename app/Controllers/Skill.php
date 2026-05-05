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
}