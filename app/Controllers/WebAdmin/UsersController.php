<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class UsersController extends BaseController
{
    private function ensureCanView(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $uid  = (int) session()->get('admin_user_id');
        $auth = $uid ? new \App\Services\AuthorizationService() : null;
        if ($auth === null || (! $auth->userHasPermission($uid, 'users.view') && ! $auth->userHasPermission($uid, 'users.manage'))) {
            return redirect()->to(base_url('chat'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureCanView()) return $r;
        return view('webadmin/users/index');
    }
    
    public function view($id = null)
    {
        if ($r = $this->ensureCanView()) return $r;
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);
        
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User not found');
        }
        
        // Get user roles
        $userRoleModel = new \App\Models\UserRoleModel();
        $roles = $userRoleModel->roleIdsForUser((int)$id);
        $roleModel = new \App\Models\RoleModel();
        $roleNames = [];
        foreach ($roles as $roleId) {
            $role = $roleModel->find($roleId);
            if ($role) $roleNames[] = $role['name'];
        }
        $user['roles'] = $roleNames;
        
        // Get user devices
        $deviceModel = new \App\Models\UserDeviceModel();
        $devices = $deviceModel->where('user_id', $id)->findAll();
        $user['devices'] = $devices;
        
        return view('webadmin/users/view', ['user' => $user]);
    }
}