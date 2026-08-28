<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Jwt;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use Config\Jwt as JwtConfig;

class AuthController extends BaseController
{
    private const DEFAULT_ROLE = 'user';

    public function register()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'username' => 'required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9._]+$/]|is_unique[users.username]',
            'email'    => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($input ?? [], $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $userModel = new UserModel();

        $userId = $userModel->insert([
            'name'          => $input['name'],
            'username'      => $input['username'],
            'email'         => $input['email'],
            'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
        ]);

        if ($userId === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors'  => $userModel->errors(),
            ]);
        }

        $defaultRole = (new RoleModel())->findByName(self::DEFAULT_ROLE);

        if ($defaultRole !== null) {
            (new UserRoleModel())->assign((int) $userId, (int) $defaultRole['id']);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'success' => true,
            'data'    => [
                'token' => $this->issueToken((int) $userId),
                'user'  => $this->presentUser($userModel->find($userId)),
            ],
        ]);
    }

    public function login()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $rules = [
            'identifier' => 'required',
            'password'   => 'required',
        ];

        if (! $this->validateData($input ?? [], $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $userModel = new UserModel();
        $user      = $userModel->findByLogin($input['identifier']);

        if ($user === null || ! password_verify($input['password'], $user['password_hash'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Email/username atau password salah.',
            ]);
        }

        if ((bool) $user['is_banned']) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Akun ini diblokir.',
                'reason'  => $user['banned_reason'],
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => [
                'token' => $this->issueToken((int) $user['id']),
                'user'  => $this->presentUser($user),
            ],
        ]);
    }

    private function issueToken(int $userId): string
    {
        $config = new JwtConfig();

        return Jwt::encode(['sub' => $userId], $config->secret, $config->ttl);
    }

    private function presentUser(array $user): array
    {
        unset($user['password_hash'], $user['deleted_at']);

        return $user;
    }
}
