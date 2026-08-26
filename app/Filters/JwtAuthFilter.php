<?php

namespace App\Filters;

use App\Libraries\Jwt;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Jwt as JwtConfig;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if ($header === '' || ! str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized('Token tidak ditemukan.');
        }

        $token  = substr($header, 7);
        $secret = (new JwtConfig())->secret;

        $payload = Jwt::decode($token, $secret);

        if ($payload === null || empty($payload['sub'])) {
            return $this->unauthorized('Token tidak valid atau kedaluwarsa.');
        }

        service('currentUser')->setId((int) $payload['sub']);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }

    private function unauthorized(string $message): ResponseInterface
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON(['success' => false, 'message' => $message]);
    }
}
