<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Health extends Controller
{
    use ResponseTrait;

    public function index(): ResponseInterface
    {
        return $this->respond([
            'status' => 'ok',
            'message' => 'API is running',
        ]);
    }

}