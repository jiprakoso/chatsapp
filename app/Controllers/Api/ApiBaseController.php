<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class ApiBaseController extends BaseController
{
    protected function success($data = null, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => true,
            'data'    => $data,
        ]);
    }

    protected function error(string $message, int $status = 400, $errors = null): ResponseInterface
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    /**
     * getJSON() melempar exception jika raw body bukan JSON valid — termasuk untuk
     * request multipart/form-data (dipakai upload attachment). Cek Content-Type dulu
     * supaya request non-JSON (multipart, urlencoded) jatuh ke getPost() dengan aman.
     */
    protected function input(): array
    {
        if (str_contains($this->request->getHeaderLine('Content-Type'), 'application/json')) {
            return $this->request->getJSON(true) ?? [];
        }

        return $this->request->getPost() ?? [];
    }

    protected function currentUserId(): int
    {
        return (int) service('currentUser')->id();
    }
}
