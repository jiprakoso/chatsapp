<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Users</h2>
                <div class="text-secondary mt-1">Manage application users</div>
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
                            <input type="text" id="tableSearch" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterRole" class="form-select">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="moderator">Moderator</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="tableUsers">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Registered</th>
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

<!-- User Details Modal -->
<div class="modal modal-blur fade" id="userDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal modal-blur fade" id="assignRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm">
                    <input type="hidden" name="user_id" id="assignRoleUserId">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" id="assignRoleSelect" class="form-select" required>
                            <option value="">Select role...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAssignRole">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Ban/Unban Modal -->
<div class="modal modal-blur fade" id="banModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banModalTitle">Ban User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="banForm">
                    <input type="hidden" name="user_id" id="banUserId">
                    <input type="hidden" name="action" id="banAction">
                    <div class="mb-3" id="banReasonGroup">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter ban reason..."></textarea>
                    </div>
                    <div class="alert alert-warning" id="banWarning" style="display: none;">
                        This will prevent the user from logging in.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                            '<div class="d-flex gap-1 justify-content-end">' +
                                '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="viewUser(' + item.id + ')" title="View">' +
                                    '<i class="ti ti-eye"></i>' +
                                '</button>' +
                                '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="openAssignRole(' + item.id + ')" title="Assign Role">' +
                                    '<i class="ti ti-shield-lock"></i>' +
                                '</button>' +
                                '<button class="btn btn-icon btn-outline-' + (item.is_banned ? 'success' : 'danger') + ' btn-sm" onclick="openBanModal(' + item.id + ', ' + (item.is_banned ? 'true' : 'false') + ')" title="' + (item.is_banned ? 'Unban' : 'Ban') + '">' +
                                    '<i class="ti ti-' + (item.is_banned ? 'shield-check' : 'lock') + '"></i>' +
                                '</button>' +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = baseUrl + 'login';
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
            processing: '<div class="spinner-border text-primary" role="status"></div>',
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
                        html += '<option value="' + role.id + '">' + escapeHtml(role.name) + '</option>';
                    }
                });
                $('#assignRoleSelect').html(html);
            }
        }
    });
}

function viewUser(id) {
    $.ajax({
        url: baseUrl + 'users/' + id,
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
                bootstrap.Modal.getInstance(document.getElementById('assignRoleModal')).hide();
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to assign role');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to assign role');
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
                bootstrap.Modal.getInstance(document.getElementById('banModal')).hide();
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Failed to ' + action + ' user');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to ' + action + ' user');
        }
    });
}

function formatRoles(roles) {
    if (!roles || roles.length === 0) {
        return '<span class="text-secondary">No roles</span>';
    }
    return roles.map(function(role) {
        var badgeClass = 'bg-azure-lt';
        if (role === 'super_admin') badgeClass = 'bg-red-lt';
        else if (role === 'admin') badgeClass = 'bg-blue-lt';
        else if (role === 'moderator') badgeClass = 'bg-yellow-lt';
        else if (role === 'user') badgeClass = 'bg-green-lt';
        return '<span class="badge ' + badgeClass + ' me-1">' + escapeHtml(role) + '</span>';
    }).join(' ');
}

function formatStatus(isBanned) {
    if (isBanned == 1 || isBanned === true) {
        return '<span class="badge bg-red-lt">Banned</span>';
    }
    return '<span class="badge bg-green-lt">Active</span>';
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

function getAuthHeaders() {
    var token = localStorage.getItem('admin_token');
    return token ? { 'Authorization': 'Bearer ' + token } : {};
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function showSuccess(message) {
    toastr.success(message);
}

function showError(message) {
    toastr.error(message);
}
</script>
<?= $this->endSection() ?>
