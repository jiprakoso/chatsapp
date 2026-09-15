<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Permissions</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">Manage system permissions</div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="openCreatePermission()">
                        <i class="ki-duotone ki-plus-square fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Create Permission
                    </button>
                </div>
            </div>
            
            <div class="card card-flush">
                <div class="card-header border-0 pt-5">
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Search:</label>
                            <input type="text" id="tableSearch" class="form-control form-control-solid w-250px" placeholder="Search permissions...">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Resource:</label>
                            <select id="filterResource" class="form-select form-select-solid w-200px">
                                <option value="">All Resources</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4" id="tablePermissions">
                            <thead>
                                <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-80px">ID</th>
                                    <th>Name</th>
                                    <th>Resource</th>
                                    <th>Action</th>
                                    <th>Description</th>
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

<!-- Permission Form Modal -->
<div class="modal fade" id="permissionFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permModalTitle">Create Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="permissionForm">
                    <input type="hidden" name="id" id="permissionId">
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="permissionName" class="form-control form-control-solid" placeholder="e.g. users.view" required>
                        <div class="form-text">Format: resource.action (e.g., users.view, conversations.manage)</div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="permissionDescription" class="form-control form-control-solid" rows="2" placeholder="Permission description..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSavePermission">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this permission? This will remove it from all roles.</p>
                <p class="text-danger fw-semibold" id="deletePermissionName"></p>
                <input type="hidden" id="deletePermissionId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeletePermission">Delete</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
var permissionsTable;
var resources = [];

$(document).ready(function() {
    initPermissionsTable();
    loadResources();
    
    $('#tableSearch').on('keyup', debounce(function() {
        permissionsTable.search(this.value).draw();
    }, 300));
    
    $('#filterResource').on('change', function() {
        permissionsTable.column(2).search(this.value).draw();
    });
    
    $('#btnSavePermission').on('click', function() {
        savePermission();
    });
    
    $('#btnConfirmDeletePermission').on('click', function() {
        confirmDeletePermission();
    });
});

function initPermissionsTable() {
    permissionsTable = $('#tablePermissions').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: baseUrl + 'api/admin/permissions',
            type: 'GET',
            headers: getAuthHeaders(),
            dataSrc: function(json) {
                if (json.success && json.data && Array.isArray(json.data.permissions)) {
                    return json.data.permissions.map(function(item) {
                        var parts = item.name.split('.');
                        return [
                            item.id,
                            '<code class="fw-semibold">' + item.name + '</code>',
                            '<span class="badge badge-light-primary">' + (parts[0] || '-') + '</span>',
                            '<span class="badge badge-light-success">' + (parts[1] || '-') + '</span>',
                            item.description || '-',
                            formatDate(item.created_at),
                            '<div class="d-flex gap-2 justify-content-end">' +
                                '<button class="btn btn-icon btn-light-primary btn-sm" onclick="editPermission(' + item.id + ')" title="Edit">' +
                                    '<i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>' +
                                '</button>' +
                                '<button class="btn btn-icon btn-light-danger btn-sm" onclick="openDeletePermission(' + item.id + ', \'' + escapeHtml(item.name) + '\')" title="Delete">' +
                                    '<i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>' +
                                '</button>' +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) window.location.href = baseUrl + 'admin/login';
                showError('Failed to load permissions');
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
        order: [[2, 'asc'], [3, 'asc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border spinner-border-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'No permissions found'
        }
    });
}

function loadResources() {
    $.ajax({
        url: baseUrl + 'api/admin/permissions',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data && Array.isArray(response.data.permissions)) {
                var resourceSet = new Set();
                response.data.permissions.forEach(function(p) {
                    resourceSet.add(p.name.split('.')[0]);
                });
                resources = Array.from(resourceSet).sort();
                var html = '<option value="">All Resources</option>';
                resources.forEach(function(r) {
                    html += '<option value="' + r + '">' + r + '</option>';
                });
                $('#filterResource').html(html);
            }
        }
    });
}

function openCreatePermission() {
    $('#permissionForm')[0].reset();
    $('#permissionId').val('');
    $('#permModalTitle').text('Create Permission');
    new bootstrap.Modal(document.getElementById('permissionFormModal')).show();
}

function editPermission(id) {
    $.ajax({
        url: baseUrl + 'api/admin/permissions/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var perm = response.data;
                $('#permissionId').val(perm.id);
                $('#permissionName').val(perm.name);
                $('#permissionDescription').val(perm.description || '');
                $('#permModalTitle').text('Edit Permission');
                new bootstrap.Modal(document.getElementById('permissionFormModal')).show();
            }
        }
    });
}

function savePermission() {
    var formData = $('#permissionForm').serializeArray();
    var data = {};
    formData.forEach(function(field) {
        data[field.name] = field.value;
    });
    
    var isEdit = $('#permissionId').val() !== '';
    var url = isEdit ? baseUrl + 'api/admin/permissions/' + $('#permissionId').val() : baseUrl + 'api/admin/permissions';
    var method = isEdit ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showSuccess(isEdit ? 'Permission updated successfully' : 'Permission created successfully');
                $('#permissionFormModal').modal('hide');
                permissionsTable.ajax.reload();
                loadResources();
            } else {
                showError(response.message || 'Failed to save permission');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to save permission');
        }
    });
}

function openDeletePermission(id, name) {
    $('#deletePermissionId').val(id);
    $('#deletePermissionName').text(name);
    new bootstrap.Modal(document.getElementById('deletePermissionModal')).show();
}

function confirmDeletePermission() {
    var id = $('#deletePermissionId').val();
    
    $.ajax({
        url: baseUrl + 'api/admin/permissions/' + id,
        method: 'DELETE',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success) {
                showSuccess('Permission deleted successfully');
                $('#deletePermissionModal').modal('hide');
                permissionsTable.ajax.reload();
                loadResources();
            } else {
                showError(response.message || 'Failed to delete permission');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to delete permission');
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