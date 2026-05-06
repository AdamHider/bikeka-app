<?php 

namespace App\Models;

use CodeIgniter\Model;

class ChildModel extends Model
{
    protected $table      = 'children';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'name', 'avatar', 'birth_date', 'gender'];

    public function getItem($child_id)
    {
        $SkillToChildModel = model('SkillToChildModel');
        $SkillModel = model('SkillModel');

        $child = $this->where('id', $child_id)->get()->getRowArray();
        
        if (empty($child)) return false;

        $child['avatar'] = base_url($child['avatar']);

        $child['age'] = $this->calculateAge($child['birth_date']);

        $child['statistics'] = [
            'total_mastered'   => $SkillToChildModel->where(['child_id' => $child_id, 'status' => 'mastered'])->countAllResults(),
            'mastered_monthly' => $SkillToChildModel->where(['child_id' => $child_id, 'status' => 'mastered'])->where('updated_at >=', date('Y-m-01 00:00:00'))->countAllResults(),
            'to_learn'         => $SkillToChildModel->where(['child_id' => $child_id, 'status !=' => 'mastered'])->countAllResults(),
        ];

        $child['skills'] = [
            'active' => $SkillModel->getList($child_id, ['status' => 'in_progress', 'limit' => 5]),
            'mastered' => $SkillModel->getList($child_id, ['status' => 'mastered', 'limit' => 5])
        ];

        return $child;
    }

    /**
     * Вспомогательная функция для расчета возраста
     */
    private function calculateAge($birthDate)
    {
        $birth = new \DateTime($birthDate);
        $today = new \DateTime();
        $diff  = $today->diff($birth);

        $years  = $diff->y;
        $months = $diff->m;

        if ($years == 0) {
            return ['display' => "$months мес.", 'years' => 0, 'months' => $months];
        }

        return [
            'display' => "$years г. $months мес.",
            'years'   => $years,
            'months'  => $months
        ];
    }
}