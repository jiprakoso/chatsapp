<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class UsersController extends BaseController
{
    public function index()
    {
        return view('webadmin/users/index');
    }
    
    public function view($id = null)
    {
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