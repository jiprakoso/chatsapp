<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\RoleModel;
use CodeIgniter\Cookie\Cookie;

class AuthController extends BaseController
{
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('admin_logged_in')) {
            return redirect()->to(base_url('/'));
        }
        
        return view('webadmin/auth/login');
    }
    
    public function loginPost()
    {
        $username_or_email = $this->request->getPost('username_or_email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');
        
        if (!$username_or_email || !$password) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username or email and password are required'
            ])->setStatusCode(422);
        }
        
        $userModel = new UserModel();
        $user = $userModel->findByLogin($username_or_email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid credentials'
            ])->setStatusCode(401);
        }
        
        if ($user['is_banned']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Account is banned: ' . ($user['banned_reason'] ?? 'No reason provided')
            ])->setStatusCode(403);
        }
        
        // Check if user has admin role
        $userRoleModel = new UserRoleModel();
        $roleIds = $userRoleModel->roleIdsForUser((int)$user['id']);
        
        $roleModel = new RoleModel();
        $isAdmin = false;
        foreach ($roleIds as $roleId) {
            $role = $roleModel->find($roleId);
            if ($role && in_array($role['name'], ['super_admin', 'admin', 'moderator'])) {
                $isAdmin = true;
                break;
            }
        }
        
        if (!$isAdmin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ])->setStatusCode(403);
        }
        
        // Generate JWT token (using the same library as API)
        $jwt = new \App\Libraries\Jwt();
        $secret = env('JWT_SECRET');
        $token = $jwt->encode([
            'sub' => (int)$user['id'],
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
            'type' => 'admin'
        ], $secret);
        
        // Store in session
        session()->set([
            'admin_logged_in' => true,
            'admin_user_id' => $user['id'],
            'admin_token' => $token,
            'admin_user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
        
        // If remember me, set longer cookie
        if ($remember) {
            $cookie = new Cookie('admin_token', $token, [
                'expires'  => time() + 30 * 24 * 60 * 60, // 30 days
                'httponly' => true,
                'secure'   => $this->request->isSecure(),
                'samesite' => 'Lax',
            ]);
            $this->response->setCookie($cookie);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'token' => $token,
            'redirect' => base_url('/')
        ]);
    }
    
    public function logout()
    {
        session()->destroy();
        $this->response->deleteCookie('admin_token');
        // Flag logged_out: halaman login membuang sisa token di localStorage
        // supaya tidak auto-redirect balik (loop home <-> login).
        return redirect()->to(base_url('login?logged_out=1'));
    }

    /**
     * Kembalikan JWT milik session yang sedang aktif. Dipakai JS untuk
     * menyelaraskan localStorage (token hilang/kedaluarsa padahal session
     * masih hidup) sehingga tidak terjadi loop / <-> /login.
     * Dilindungi filter webadminauth = bukti session valid.
     */
    public function token()
    {
        $userId = (int) session()->get('admin_user_id');

        $userModel = new UserModel();
        $user      = $userModel->find($userId);

        if (! $user || ! empty($user['is_banned'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun tidak tersedia.',
            ])->setStatusCode(401);
        }

        $roleIds = (new UserRoleModel())->roleIdsForUser($userId);
        $roleModel = new RoleModel();
        $isAdmin = false;
        foreach ($roleIds as $roleId) {
            $role = $roleModel->find($roleId);
            if ($role && in_array($role['name'], ['super_admin', 'admin', 'moderator'], true)) {
                $isAdmin = true;
                break;
            }
        }

        if (! $isAdmin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.',
            ])->setStatusCode(403);
        }

        $token = session()->get('admin_token');
        $payload = is_string($token) ? \App\Libraries\Jwt::decode($token, (string) env('JWT_SECRET')) : null;

        // Token hilang/kedaluarsa selagi session hidup -> terbitkan baru.
        if ($payload === null) {
            $jwt   = new \App\Libraries\Jwt();
            $token = $jwt->encode([
                'sub'  => (int) $user['id'],
                'iat'  => time(),
                'exp'  => time() + 3600,
                'type' => 'admin',
            ], (string) env('JWT_SECRET'));
            session()->set('admin_token', $token);
        }

        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
        ]);
    }

    /**
     * Bangun ulang session web dari JWT yang masih valid.
     * Menutup celah desync: token valid tapi session server mati (logout,
     * kadaluarsa, dsb) tidak lagi menyebabkan loop / <-> /login.
     * Body JSON: { "token": "<jwt>" }
     */
    public function refresh()
    {
        $input = $this->request->getJSON(true) ?? [];
        $token = $input['token'] ?? '';

        if ($token === '') {
            $header = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($header, 'Bearer ')) {
                $token = substr($header, 7);
            }
        }

        if ($token === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token required.',
            ])->setStatusCode(422);
        }

        $payload = \App\Libraries\Jwt::decode($token, (string) env('JWT_SECRET'));

        if ($payload === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token tidak valid atau kedaluwarsa.',
            ])->setStatusCode(401);
        }

        $userModel = new UserModel();
        $user      = $userModel->find((int) ($payload['sub'] ?? 0));

        if (! $user || ! empty($user['is_banned'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun tidak tersedia.',
            ])->setStatusCode(401);
        }

        $roleIds = (new UserRoleModel())->roleIdsForUser((int) $user['id']);
        $roleModel = new RoleModel();
        $isAdmin = false;
        foreach ($roleIds as $roleId) {
            $role = $roleModel->find($roleId);
            if ($role && in_array($role['name'], ['super_admin', 'admin', 'moderator'], true)) {
                $isAdmin = true;
                break;
            }
        }

        if (! $isAdmin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.',
            ])->setStatusCode(403);
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_user_id'   => $user['id'],
            'admin_token'     => $token,
            'admin_user'      => [
                'id'       => $user['id'],
                'name'     => $user['name'],
                'username' => $user['username'],
                'email'    => $user['email'],
            ],
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'redirect' => base_url('/'),
        ]);
    }
}