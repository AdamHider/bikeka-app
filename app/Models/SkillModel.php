<?php 

namespace App\Models;

use CodeIgniter\Model;

class SkillModel extends Model
{
    protected $table = 'skills';

    public function getList($child_id, $status)
    {
        $SkillStageModel = model('SkillStageModel');

        $this->select('skills.*, COALESCE(skills_to_children.status, "not_started") as current_status');
        
        $this->join('skills_to_children', 'skills_to_children.skill_id = skills.id AND skills_to_children.child_id = ' . $child_id, 'left');

        if ($status === 'not_started') {
            $this->groupStart()
                    ->where('skills_to_children.status', 'not_started')
                    ->orWhere('skills_to_children.status', null)
                    ->groupEnd();
        } else {
            $this->where('skills_to_children.status', $status);
        }

        $skills = $this->get()->getResultArray();

        foreach ($skills as &$skill) {
            $skill['stages'] = $SkillStageModel->getList($skill['id'], $child_id);
        }

        return $skills;
    }
    
    public function updateStatus($child_id, $skill_id, $status)
    {
        $SkillToChildModel = model('SkillToChildModel');
    
        $exists = $SkillToChildModel->where(['child_id' => $child_id, 'skill_id' => $skill_id])->countAllResults(false);
    
        if ($exists > 0) {
            return $SkillToChildModel->where(['child_id' => $child_id, 'skill_id' => $skill_id])->set(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();
        } else {
            return $SkillToChildModel->insert([
                'child_id' => $child_id,
                'skill_id' => $skill_id,
                'status'   => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}