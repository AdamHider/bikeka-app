<?php 

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;

class Dashboard extends BaseController {
    use ResponseTrait;

    public function getStats($childId) {
        
        return $this->respond($stats);
    }
}