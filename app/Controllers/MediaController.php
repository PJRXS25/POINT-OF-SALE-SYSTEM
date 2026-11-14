<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class MediaController extends BaseController
{
    public function show(string $token): ResponseInterface
    {
        if (preg_match('/^[a-f0-9]{48}$/', $token) !== 1) {
            return $this->response->setStatusCode(404);
        }

        $asset = db_connect()->table('media_assets')
            ->select('mime_type, data')
            ->where('token', $token)
            ->get()
            ->getRowArray();

        if (!$asset) {
            return $this->response->setStatusCode(404);
        }

        return $this->response
            ->setHeader('Content-Type', $asset['mime_type'])
            ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->setBody($asset['data']);
    }
}
