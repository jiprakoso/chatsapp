<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Roles</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">Manage system roles and their permissions</div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="openCreateRole()">
                        <i class="ki-duotone ki-plus-square fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Create Role
                    </button>
                </div>
            </div>
            
            <div class="card card-flush">
                <div class="card-header border-0 pt-5">
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Search:</label>
                            <input type="text" id="tableSearch" class="form-control form-control-solid w-250px" placeholder="Search roles...">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Type:</label>
                            <select id="filterSystem" class="form-select form-select-solid w-200px">
                                <option value="">All Types</option>
                                <option value="1">System Roles</option>
                                <option value="0">Custom Roles</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4" id="tableRoles">
                            <thead>
                                <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-80px">ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th class="min-w-120px">Type</th>
                                    <th class="min-w-120px">Permissions</th>
                                    <th class="min-w-150px">Created</th>
                                    <th class="text-end min-w-150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Form Modal -->
<div class="modal fade" id="roleFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Create Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" name="id" id="roleId">
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleName" class="form-control form-control-solid" placeholder="e.g. content_manager" required>
                        <div class="form-text">Lowercase with underscores only</div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="roleDescription" class="form-control form-control-solid" rows="2" placeholder="Role description..."></textarea>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold">System Role</label>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_system" id="roleIsSystem" value="1">
                            <label class="form-check-label" for="roleIsSystem">
                                System role (cannot be deleted)
                            </label>
                        </div>
                    </div>
                    <hr class="my-5">
                    <h6 class="fw-bold mb-4">Permissions</h6>
                    <div id="permissionsTree"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRole">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                <p class="text-danger fw-semibold" id="deleteRoleName"></p>
                <input type="hidden" id="deleteRoleId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteRole">Delete</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
var rolesTable;
var allPermissions = [];

$(document).ready(function() {
    loadAllPermissions();
    initRolesTable();
    
    $('#tableSearch').on('keyup', debounce(function() {
        rolesTable.search(this.value).draw();
    }, 300));
    
    $('#filterSystem').on('change', function() {
        rolesTable.draw();
    });
    
    $('#btnSaveRole').on('click', function() {
        saveRole();
    });
    
    $('#btnConfirmDeleteRole').on('click', function() {
        confirmDeleteRole();
    });
});

function initRolesTable() {
    rolesTable = $('#tableRoles').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: baseUrl + 'api/admin/roles',
            type: 'GET',
            headers: getAuthHeaders(),
            data: function(d) {
                d.is_system = $('#filterSystem').val();
            },
            dataSrc: function(json) {
                if (json.success && json.data) {
                    return json.data.roles.map(function(item) {
                        return [
                            item.id,
                            item.name,
                            item.description || '-',
                            item.is_system == 1 ? '<span class="badge badge-light-danger">System</span>' : '<span class="badge badge-light-success">Custom</span>',
                            '<span class="badge badge-light-primary">' + item.permission_count + ' permissions</span>',
                            formatDate(item.created_at),
                            '<div class="d-flex gap-2 justify-content-end">' +
                                '<button class="btn btn-icon btn-light-primary btn-sm" onclick="editRole(' + item.id + ')" title="Edit">' +
                                    '<i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>' +
                                '</button>' +
                                (item.is_system != 1 ? 
                                    '<button class="btn btn-icon btn-light-danger btn-sm" onclick="openDeleteRole(' + item.id + ', \'' + escapeHtml(item.name) + '\')" title="Delete">' +
                                        '<i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>' +
                                    '</button>' : ''
                                ) +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) window.location.href = baseUrl + 'login';
                showError('Failed to load roles');
            }
        },
        columns: [
            { data: 0, className: 'fw-semibold' },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, className: 'text-end' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border spinner-border-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'No roles found'
        }
    });
}

function loadAllPermissions() {
    $.ajax({
        url: baseUrl + 'api/admin/permissions',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                allPermissions = response.data.permissions;
            }
        }
    });
}

function openCreateRole() {
    resetRoleForm();
    $('#roleModalTitle').text('Create Role');
    $('#roleIsSystem').prop('checked', false).closest('.form-check').show();
    new bootstrap.Modal(document.getElementById('roleFormModal')).show();
}

function editRole(id) {
    resetRoleForm();
    $('#roleModalTitle').text('Edit Role');
    
    $.ajax({
        url: baseUrl + 'api/admin/roles/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var role = response.data;
                $('#roleId').val(role.id);
                $('#roleName').val(role.name).prop('disabled', role.is_system == 1);
                $('#roleDescription').val(role.description || '');
                $('#roleIsSystem').prop('checked', role.is_system == 1).closest('.form-check').toggle(role.is_system != 1);
                
                // Build permissions tree
                buildPermissionsTree(role.permissions || []);
                
                new bootstrap.Modal(document.getElementById('roleFormModal')).show();
            }
        }
    });
}

function resetRoleForm() {
    $('#roleForm')[0].reset();
    $('#roleId').val('');
    $('#roleName').prop('disabled', false);
    $('#permissionsTree').html('');
}

function buildPermissionsTree(selectedPermissions) {
    // Group permissions by resource
    var grouped = {};
    allPermissions.forEach(function(p) {
        var parts = p.name.split('.');
        var resource = parts[0];
        if (!grouped[resource]) grouped[resource] = [];
        grouped[resource].push(p);
    });
    
    var html = '<div class="row g-3">';
    Object.keys(grouped).sort().forEach(function(resource) {
        html += '<div class="col-md-6">' +
            '<div class="card card-flush">' +
                '<div class="card-body py-3">' +
                    '<div class="form-check form-check-custom form-check-solid mb-3">' +
                        '<input class="form-check-input perm-resource" type="checkbox" id="perm_' + resource + '" data-resource="' + resource + '" ' +
                            (selectedPermissions.some(function(sp) { return sp.startsWith(resource + '.') || sp === '*'; }) ? 'checked' : '') + '>' +
                        '<label class="form-check-label fw-semibold text-uppercase fs-7 text-gray-500" for="perm_' + resource + '">' + resource + '</label>' +
                    '</div>' +
                    '<div class="ms-4">';
        
        grouped[resource].forEach(function(p) {
            var isSelected = selectedPermissions.includes(p.name) || selectedPermissions.includes('*');
            html += '<div class="form-check form-check-custom form-check-solid mb-2">' +
                '<input class="form-check-input perm-action" type="checkbox" name="permissions[]" value="' + p.name + '" ' +
                    'id="perm_' + p.name.replace(/\./g, '_') + '" data-resource="' + resource + '" ' +
                    (isSelected ? 'checked' : '') + '>' +
                '<label class="form-check-label" for="perm_' + p.name.replace(/\./g, '_') + '">' + p.name + '</label>' +
            '</div>';
        });
        
        html += '</div></div></div></div>';
    });
    html += '</div>';
    
    $('#permissionsTree').html(html);
    
    // Event handlers for resource checkboxes
    $('.perm-resource').on('change', function() {
        var resource = $(this).data('resource');
        var checked = $(this).is(':checked');
        $('.perm-action[data-resource="' + resource + '"]').prop('checked', checked);
    });
    
    $('.perm-action').on('change', function() {
        var resource = $(this).data('resource');
        var allChecked = $('.perm-action[data-resource="' + resource + '"]:checked').length === 
                         $('.perm-action[data-resource="' + resource + '"]').length;
        $('.perm-resource[data-resource="' + resource + '"]').prop('checked', allChecked);
    });
}

function saveRole() {
    var formData = $('#roleForm').serializeArray();
    var data = {};
    var permissions = [];
    
    formData.forEach(function(field) {
        if (field.name === 'permissions[]') {
            permissions.push(field.value);
        } else if (field.name === 'is_system') {
            data[field.name] = 1;
        } else {
            data[field.name] = field.value;
        }
    });
    
    data.permissions = permissions;
    
    var isEdit = $('#roleId').val() !== '';
    var url = isEdit ? baseUrl + 'api/admin/roles/' + $('#roleId').val() : baseUrl + 'api/admin/roles';
    var method = isEdit ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showSuccess(isEdit ? 'Role updated successfully' : 'Role created successfully');
                $('#roleFormModal').modal('hide');
                rolesTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to save role');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to save role');
        }
    });
}

function openDeleteRole(id, name) {
    $('#deleteRoleId').val(id);
    $('#deleteRoleName').text(name);
    new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
}

function confirmDeleteRole() {
    var id = $('#deleteRoleId').val();
    
    $.ajax({
        url: baseUrl + 'api/admin/roles/' + id,
        method: 'DELETE',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success) {
                showSuccess('Role deleted successfully');
                $('#deleteRoleModal').modal('hide');
                rolesTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to delete role');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to delete role');
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var date = new Date(dateStr);
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    var map = {'&': '&', '<': '<', '>': '>', '"': '"', "'": '&#039;'};
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function getAuthHeaders() {
    var token = localStorage.getItem('admin_token');
    return token ? { 'Authorization': 'Bearer ' + token } : {};
}

function showSuccess(message) {
    toastr.success(message);
}

function showError(message) {
    toastr.error(message);
}
</script>
<?= $this->endSection() ?>