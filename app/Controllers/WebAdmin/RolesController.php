<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class RolesController extends BaseController
{
    public function index()
    {
        return view('webadmin/roles/index');
    }
    
    public function create()
    {
        return view('webadmin/roles/form');
    }
    
    public function edit($id = null)
    {
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