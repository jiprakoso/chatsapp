<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Users</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">Manage application users</div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-kt-menu="true" data-kt-menu-placement="bottom-end" data-kt-menu-trigger="click">
                        <i class="ki-duotone ki-plus-square fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Actions
                    </button>
                </div>
            </div>
            
            <!-- Table -->
            <div class="card card-flush">
                <div class="card-header border-0 pt-5">
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Search:</label>
                            <input type="text" id="tableSearch" class="form-control form-control-solid w-250px" placeholder="Search users...">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Role:</label>
                            <select id="filterRole" class="form-select form-select-solid w-200px">
                                <option value="">All Roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Status:</label>
                            <select id="filterStatus" class="form-select form-select-solid w-180px">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4" id="tableUsers">
                            <thead>
                                <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-100px">ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th class="min-w-120px">Status</th>
                                    <th class="min-w-150px">Registered</th>
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

<!-- User Details Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm">
                    <input type="hidden" name="user_id" id="assignRoleUserId">
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role_id" id="assignRoleSelect" class="form-select form-select-solid" required>
                            <option value="">Select role...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAssignRole">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Ban/Unban Modal -->
<div class="modal fade" id="banModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banModalTitle">Ban User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="banForm">
                    <input type="hidden" name="user_id" id="banUserId">
                    <input type="hidden" name="action" id="banAction">
                    <div class="mb-5" id="banReasonGroup">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" class="form-control form-control-solid" rows="3" placeholder="Enter ban reason..."></textarea>
                    </div>
                    <div class="alert alert-warning" id="banWarning" style="display: none;">
                        This will prevent the user from logging in.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmBan">Confirm</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
var usersTable;

$(document).ready(function() {
    initUsersTable();
    loadRolesForSelect();
    
    // Search input
    $('#tableSearch').on('keyup', debounce(function() {
        usersTable.search(this.value).draw();
    }, 300));
    
    // Filters (client-side: DataTables mencari teks polos di dalam badge HTML)
    $('#filterRole').on('change', function() {
        usersTable.column(4).search(this.value).draw();
    });
    $('#filterStatus').on('change', function() {
        var map = { banned: 'Banned', active: 'Active' };
        usersTable.column(5).search(map[this.value] || '').draw();
    });
    
    // Assign role form
    $('#btnSaveAssignRole').on('click', function() {
        saveAssignRole();
    });
    
    // Ban/Unban
    $('#btnConfirmBan').on('click', function() {
        confirmBanUnban();
    });
});

function initUsersTable() {
    usersTable = $('#tableUsers').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: baseUrl + 'api/admin/users',
            type: 'GET',
            headers: getAuthHeaders(),
            data: { per_page: 500 },
            dataSrc: function(json) {
                if (json.success && json.data && Array.isArray(json.data.users)) {
                    return json.data.users.map(function(item) {
                        return [
                            item.id,
                            escapeHtml(item.name),
                            escapeHtml(item.username || '-'),
                            escapeHtml(item.email),
                            formatRoles(item.roles),
                            formatStatus(item.is_banned),
                            formatDate(item.created_at),
                            '<div class="d-flex gap-2 justify-content-end">' +
                                '<button class="btn btn-icon btn-light-primary btn-sm" onclick="viewUser(' + item.id + ')" title="View">' +
                                    '<i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>' +
                                '</button>' +
                                '<button class="btn btn-icon btn-light-primary btn-sm" onclick="openAssignRole(' + item.id + ')" title="Assign Role">' +
                                    '<i class="ki-duotone ki-shield fs-3"><span class="path1"></span><span class="path2"></span></i>' +
                                '</button>' +
                                '<button class="btn btn-icon btn-light-' + (item.is_banned ? 'success' : 'danger') + ' btn-sm" onclick="openBanModal(' + item.id + ', ' + (item.is_banned ? 'true' : 'false') + ')" title="' + (item.is_banned ? 'Unban' : 'Ban') + '">' +
                                    '<i class="ki-duotone ki-' + (item.is_banned ? 'shield-tick' : 'lock') + ' fs-3">' + (item.is_banned ? '<span class="path1"></span><span class="path2"></span>' : '<span class="path1"></span><span class="path2"></span><span class="path3"></span>') + '</i>' +
                                '</button>' +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = baseUrl + 'admin/login';
                }
                showError('Failed to load users');
            }
        },
        columns: [
            { data: 0, className: 'fw-semibold' },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 },
            { data: 7, className: 'text-end' }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="spinner-border spinner-border-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'No users found',
            info: 'Showing _START_ to _END_ of _TOTAL_ users',
            infoEmpty: 'Showing 0 to 0 of 0 users',
            infoFiltered: '(filtered from _MAX_ total users)'
        }
    });
}

function loadRolesForSelect() {
    $.ajax({
        url: baseUrl + 'api/admin/roles',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var html = '<option value="">Select role...</option>';
                response.data.roles.forEach(function(role) {
                    if (role.name !== 'super_admin') { // Don't allow assigning super_admin via UI
                        html += '<option value="' + role.id + '">' + role.name + '</option>';
                    }
                });
                $('#assignRoleSelect').html(html);
            }
        }
    });
}

function viewUser(id) {
    $.ajax({
        url: baseUrl + 'admin/users/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(html) {
            $('#userDetailContent').html(html);
            new bootstrap.Modal(document.getElementById('userDetailModal')).show();
        }
    });
}

function openAssignRole(userId) {
    $('#assignRoleUserId').val(userId);
    $('#assignRoleSelect').val('').trigger('change');
    new bootstrap.Modal(document.getElementById('assignRoleModal')).show();
}

function saveAssignRole() {
    var userId = $('#assignRoleUserId').val();
    var roleId = $('#assignRoleSelect').val();
    
    if (!roleId) {
        showError('Please select a role');
        return;
    }
    
    $.ajax({
        url: baseUrl + 'api/admin/users/' + userId + '/role',
        method: 'PUT',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify({ role_id: parseInt(roleId) }),
        success: function(response) {
            if (response.success) {
                showSuccess('Role assigned successfully');
                $('#assignRoleModal').modal('hide');
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to assign role');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to assign role');
        }
    });
}

function openBanModal(userId, isBanned) {
    $('#banUserId').val(userId);
    $('#banAction').val(isBanned ? 'unban' : 'ban');
    $('#banReasonGroup').toggle(!isBanned);
    $('#banWarning').toggle(!isBanned);
    $('#banModalTitle').text(isBanned ? 'Unban User' : 'Ban User');
    $('#btnConfirmBan').text(isBanned ? 'Unban' : 'Ban').removeClass('btn-danger btn-success').addClass(isBanned ? 'btn-success' : 'btn-danger');
    $('#banForm')[0].reset();
    new bootstrap.Modal(document.getElementById('banModal')).show();
}

function confirmBanUnban() {
    var userId = $('#banUserId').val();
    var action = $('#banAction').val();
    var reason = $('[name="reason"]').val();
    
    var url = baseUrl + 'api/admin/users/' + userId + '/' + action;
    var data = action === 'ban' ? { reason: reason } : {};
    
    $.ajax({
        url: url,
        method: 'POST',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showSuccess(action === 'ban' ? 'User banned successfully' : 'User unbanned successfully');
                $('#banModal').modal('hide');
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to ' + action + ' user');
            }
        },
        error: function(xhr) {
            showError(xhr.responseJSON?.message || 'Failed to ' + action + ' user');
        }
    });
}

function formatRoles(roles) {
    if (!roles || roles.length === 0) {
        return '<span class="text-muted">No roles</span>';
    }
    return roles.map(function(role) {
        var badgeClass = 'badge-light-secondary';
        if (role === 'super_admin') badgeClass = 'badge-light-danger';
        else if (role === 'admin') badgeClass = 'badge-light-primary';
        else if (role === 'moderator') badgeClass = 'badge-light-warning';
        else if (role === 'user') badgeClass = 'badge-light-success';
        return '<span class="badge ' + badgeClass + ' me-1">' + role + '</span>';
    }).join(' ');
}

function formatStatus(isBanned) {
    if (isBanned) {
        return '<span class="badge badge-light-danger">Banned</span>';
    }
    return '<span class="badge badge-light-success">Active</span>';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
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

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
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