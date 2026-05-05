<?php 

namespace App\Models;
use CodeIgniter\Model;

class SkillStageModel extends Model {
    protected $table = 'skill_stages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['child_id', 'stage_id', 'is_completed', 'completed_at'];
    public function getList($skill_id, $child_id)
    {
        return $this->select('skill_stages.*, IF(skill_stages_to_children.id IS NULL, 0, 1) as is_completed')
            ->join('skill_stages_to_children', "skill_stages_to_children.stage_id = skill_stages.id AND skill_stages_to_children.child_id = $child_id", 'left')
            ->where('skill_stages.skill_id', $skill_id)
            ->orderBy('skill_stages.order_index', 'ASC')
            ->findAll();
    }
}