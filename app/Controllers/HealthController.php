<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends BaseController
{
    public function index(): ResponseInterface
    {
        try {
            db_connect()->query('SELECT 1')->getRow();
        } catch (\Throwable $exception) {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'unavailable',
                'database' => 'unavailable',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'database' => 'connected',
        ]);
    }
}
