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
        return redirect()->to(base_url('login'));
    }
}