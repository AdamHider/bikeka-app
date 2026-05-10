<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if ($this->request->getMethod() === 'head') {
            return $this->response->setStatusCode(200);
        }

        return $this->response->setJSON(['status' => 'online']);
    }
}