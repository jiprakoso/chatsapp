<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class RolesController extends BaseController
{
    private function ensure(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $uid  = (int) session()->get('admin_user_id');
        $auth = $uid ? new \App\Services\AuthorizationService() : null;
        if ($auth === null || ! $auth->userHasPermission($uid, 'roles.manage')) {
            return redirect()->to(base_url('chat'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensure()) return $r;
        return view('webadmin/roles/index');
    }
    
    public function create()
    {
        if ($r = $this->ensure()) return $r;
        return view('webadmin/roles/form');
    }
    
    public function edit($id = null)
    {
        if ($r = $this->ensure()) return $r;
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->find($id);
        
        if (!$role) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Role not found');
        }
        
        // Get permissions for this role
        $rpModel = new \App\Models\RolePermissionModel();
        $permissionIds = $rpModel->permissionIdsForRole((int)$id);
        
        return view('webadmin/roles/form', ['role' => $role, 'permissionIds' => $permissionIds]);
    }
}