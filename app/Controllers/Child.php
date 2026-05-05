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
}