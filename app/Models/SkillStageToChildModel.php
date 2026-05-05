<?php 

namespace App\Models;
use CodeIgniter\Model;

class SkillToChildModel extends Model {
    protected $table = 'skills_to_children';
    protected $primaryKey = 'id';
    protected $allowedFields = ['child_id', 'skill_id', 'status', 'created_at'];
    protected $useTimestamps = true;
}