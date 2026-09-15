<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Roles</h2>
                <div class="text-secondary mt-1">Manage system roles and their permissions</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button type="button" class="btn btn-primary" onclick="openCreateRole()">
                    <i class="ti ti-plus me-2"></i>Create Role
                </button>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div class="row g-2 align-items-center w-100">
                    <div class="col-md-4">
                        <div class="input-icon">
                            <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                            <input type="text" id="tableSearch" class="form-control" placeholder="Search roles...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterSystem" class="form-select">
                            <option value="">All Types</option>
                            <option value="1">System Roles</option>
                            <option value="0">Custom Roles</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="tableRoles">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Permissions</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Role Form Modal -->
<div class="modal modal-blur fade" id="roleFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Create Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" name="id" id="roleId">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleName" class="form-control" placeholder="e.g. content_manager" required>
                        <div class="form-hint">Lowercase with underscores only</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="roleDescription" class="form-control" rows="2" placeholder="Role description..."></textarea>
                    </div>
                    <div class="mb-3" id="roleIsSystemGroup">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_system" id="roleIsSystem" value="1">
                            <span class="form-check-label">System role (cannot be deleted)</span>
                        </label>
                    </div>
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Permissions</h6>
                    <div id="permissionsTree"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRole">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal modal-blur fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                <p class="text-danger fw-semibold" id="deleteRoleName"></p>
                <input type="hidden" id="deleteRoleId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    // Tunggu auth siap agar request pertama selalu pakai token valid
    window.AdminApp.ready.then(function() {
        loadAllPermissions();
        initRolesTable();

        $('#tableSearch').on('keyup', debounce(function() {
            rolesTable.search(this.value).draw();
        }, 300));

        $('#filterSystem').on('change', function() {
            var map = { '1': 'System', '0': 'Custom' };
            rolesTable.column(3).search(map[this.value] || '').draw();
        });

        $('#btnSaveRole').on('click', function() {
            saveRole();
        });

        $('#btnConfirmDeleteRole').on('click', function() {
            confirmDeleteRole();
        });
    });
});

function initRolesTable() {
    rolesTable = $('#tableRoles').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: baseUrl + 'api/admin/roles',
            type: 'GET',
            headers: getAuthHeaders(),
            data: { per_page: 500 },
            dataSrc: function(json) {
                if (json.success && json.data && Array.isArray(json.data.roles)) {
                    return json.data.roles.map(function(item) {
                        return [
                            item.id,
                            '<code>' + escapeHtml(item.name) + '</code>',
                            escapeHtml(item.description || '-'),
                            item.is_system == 1 ? '<span class="badge bg-red-lt">System</span>' : '<span class="badge bg-green-lt">Custom</span>',
                            '<span class="badge bg-blue-lt">' + (item.permission_count || 0) + ' permissions</span>',
                            formatDate(item.created_at),
                            '<div class="d-flex gap-1 justify-content-end">' +
                                '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="editRole(' + item.id + ')" title="Edit">' +
                                    '<i class="ti ti-pencil"></i>' +
                                '</button>' +
                                (item.is_system != 1 ?
                                    '<button class="btn btn-icon btn-outline-danger btn-sm" onclick="openDeleteRole(' + item.id + ', \'' + escapeHtml(item.name) + '\')" title="Delete">' +
                                        '<i class="ti ti-trash"></i>' +
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
            processing: '<div class="spinner-border text-primary" role="status"></div>',
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
            if (response.success && response.data && Array.isArray(response.data.permissions)) {
                allPermissions = response.data.permissions;
            }
        }
    });
}

function openCreateRole() {
    resetRoleForm();
    $('#roleModalTitle').text('Create Role');
    $('#roleIsSystemGroup').show();
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
                $('#roleIsSystem').prop('checked', role.is_system == 1);
                $('#roleIsSystemGroup').toggle(role.is_system != 1);

                // API mengembalikan permissions sebagai array objek;
                // tree memakai array nama permission.
                var selectedNames = (role.permissions || []).map(function(p) {
                    return typeof p === 'string' ? p : p.name;
                });
                buildPermissionsTree(selectedNames);

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
            '<div class="card"><div class="card-body py-3">' +
                '<label class="form-check mb-2">' +
                    '<input class="form-check-input perm-resource" type="checkbox" data-resource="' + escapeHtml(resource) + '" ' +
                        (selectedPermissions.some(function(sp) { return sp.startsWith(resource + '.') || sp === '*'; }) ? 'checked' : '') + '>' +
                    '<span class="form-check-label fw-semibold text-uppercase text-secondary">' + escapeHtml(resource) + '</span>' +
                '</label>' +
                '<div class="ms-3">';

        grouped[resource].forEach(function(p) {
            var inputId = 'perm_' + p.name.replace(/\./g, '_');
            var isSelected = selectedPermissions.includes(p.name) || selectedPermissions.includes('*');
            html += '<label class="form-check">' +
                '<input class="form-check-input perm-action" type="checkbox" name="permissions[]" value="' + escapeHtml(p.name) + '" ' +
                    'id="' + inputId + '" data-resource="' + escapeHtml(resource) + '" ' +
                    (isSelected ? 'checked' : '') + '>' +
                '<span class="form-check-label"><code>' + escapeHtml(p.name) + '</code></span>' +
            '</label>';
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
                var savedRoleId = isEdit ? $('#roleId').val() : (response.data && response.data.id);
                syncRolePermissions(savedRoleId, permissions, isEdit ? 'Role updated successfully' : 'Role created successfully');
            } else {
                showError(response.message || 'Failed to save role');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save role');
        }
    });
}

function syncRolePermissions(roleId, permissionNames, successMessage) {
    if (!roleId) {
        showError('Role saved but ID unknown, please refresh');
        return;
    }

    // Petakan nama permission -> id berdasarkan daftar yang sudah dimuat.
    var nameToId = {};
    allPermissions.forEach(function(p) { nameToId[p.name] = p.id; });
    var permissionIds = (permissionNames || [])
        .map(function(name) { return nameToId[name]; })
        .filter(function(id) { return id !== undefined; });

    $.ajax({
        url: baseUrl + 'api/admin/roles/' + roleId + '/permissions',
        method: 'PUT',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify({ permission_ids: permissionIds }),
        success: function(syncResponse) {
            if (syncResponse.success) {
                showSuccess(successMessage);
                bootstrap.Modal.getInstance(document.getElementById('roleFormModal')).hide();
                rolesTable.ajax.reload();
            } else {
                showError(syncResponse.message || 'Role saved, but failed to sync permissions');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Role saved, but failed to sync permissions');
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
                bootstrap.Modal.getInstance(document.getElementById('deleteRoleModal')).hide();
                rolesTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to delete role');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to delete role');
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var date = new Date(dateStr);
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
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
