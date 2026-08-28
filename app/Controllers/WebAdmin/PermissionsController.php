<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class PermissionsController extends BaseController
{
    public function index()
    {
        return view('webadmin/permissions/index');
    }
    
    public function create()
    {
        return view('webadmin/permissions/form');
    }
    
    public function edit($id = null)
    {
        $permModel = new \App\Models\PermissionModel();
        $permission = $permModel->find($id);
        
        if (!$permission) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Permission not found');
        }
        
        return view('webadmin/permissions/form', ['permission' => $permission]);
    }
}