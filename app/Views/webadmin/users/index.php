<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Users</h2>
                <div class="text-secondary mt-1">Manage application users</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button class="btn btn-primary" id="btnAddUser" style="display:none;">
                    <i class="ti ti-plus me-1"></i>Add User
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

<!-- Add User Modal -->
<div class="modal modal-blur fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="addUserName" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="addUserUsername" class="form-control" placeholder="username" required>
                        <div class="form-hint">Huruf, angka, titik dan underscore saja.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="addUserEmail" class="form-control" placeholder="user@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="addUserPassword" class="form-control" placeholder="Min. 8 karakter" required>
                    </div>
                    <div class="mb-3" id="addUserRoleGroup">
                        <label class="form-label">Role</label>
                        <select name="role_id" id="addUserRole" class="form-select">
                            <option value="">Default (user)</option>
                        </select>
                        <div class="form-hint">Hanya role selevel/di bawah Anda yang bisa dipilih.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAddUser">Create</button>
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

<!-- Edit User Modal -->
<div class="modal modal-blur fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="editUserUsername" class="form-control" placeholder="username" required>
                        <div class="form-hint">Huruf, angka, titik dan underscore saja.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" placeholder="user@example.com" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveEditUser">Save</button>
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
var currentUser = null;
var currentUserHighestRank = 0;
var ROLE_RANKS = { 'super_admin': 100, 'admin': 80, 'moderator': 60, 'user': 10 };

function getRoleRank(roleName) {
    return ROLE_RANKS[roleName] || 0;
}
function highestRankOf(roles) {
    if (!roles || roles.length === 0) return 0;
    var max = 0;
    roles.forEach(function(r) {
        var rank = getRoleRank(typeof r === 'string' ? r : (r && r.name ? r.name : ''));
        if (rank > max) max = rank;
    });
    return max;
}
function canManageTarget(targetRoles) {
    if (!currentUser) return false;
    return currentUserHighestRank >= highestRankOf(targetRoles);
}
function canAssignRoleId(roleId) {
    var role = allRoles.find(function(r) { return String(r.id) === String(roleId); });
    if (!role) return false;
    return currentUserHighestRank >= getRoleRank(role.name);
}
function hasPermission(perm) {
    if (!currentUser || !currentUser.permissions) return false;
    if (currentUser.permissions.includes(perm)) return true;
    if (currentUser.permissions.includes('*')) return true;
    // wildcard seperti users.* mencakup users.manage
    var prefix = perm.split('.')[0] + '.*';
    if (currentUser.permissions.includes(prefix)) return true;
    return false;
}

var allRoles = [];

$(document).ready(function() {
    // Tunggu auth siap agar request pertama selalu pakai token valid
    window.AdminApp.ready.then(function() {
        currentUser = window.AdminApp.getUser();
        currentUserHighestRank = highestRankOf(currentUser && currentUser.roles ? currentUser.roles : []);
        initUsersTable();
        loadRolesForSelect();

        if (hasPermission('users.manage')) {
            $('#btnAddUser').show();
        }

        $('#btnAddUser').on('click', function() {
            $('#addUserForm')[0].reset();
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        });

        $('#btnSaveAddUser').on('click', function() {
            saveAddUser();
        });

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

        // Edit user
        $('#btnSaveEditUser').on('click', function() {
            saveEditUser();
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
                        var canManage = canManageTarget(item.roles);
                        var editDisabled = canManage ? '' : ' disabled';
                        var editTitle = canManage ? 'Edit' : 'Tidak boleh edit user dengan level lebih tinggi';
                        var assignDisabled = canManage ? '' : ' disabled';
                        var banDisabled = canManage ? '' : ' disabled';
                        var canEdit = hasPermission('users.manage') && canManageTarget(item.roles);
                        var canAssign = hasPermission('roles.manage') && canManageTarget(item.roles);
                        var canBan = hasPermission('users.ban') && canManageTarget(item.roles);
                        var actions = '<div class="d-flex gap-1 justify-content-end">' +
                                '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="viewUser(' + item.id + ')" title="View">' +
                                    '<i class="ti ti-eye"></i>' +
                                '</button>';
                        if (canEdit) {
                            actions += '<button class="btn btn-icon btn-outline-warning btn-sm" onclick="openEditUser(' + item.id + ')" title="Edit">' +
                                    '<i class="ti ti-pencil"></i>' +
                                '</button>';
                        }
                        if (canAssign) {
                            actions += '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="openAssignRole(' + item.id + ')" title="Assign Role">' +
                                    '<i class="ti ti-shield-lock"></i>' +
                                '</button>';
                        }
                        if (canBan) {
                            actions += '<button class="btn btn-icon btn-outline-' + (item.is_banned ? 'success' : 'danger') + ' btn-sm" onclick="openBanModal(' + item.id + ', ' + (item.is_banned ? 'true' : 'false') + ')" title="' + (item.is_banned ? 'Unban' : 'Ban') + '">' +
                                    '<i class="ti ti-' + (item.is_banned ? 'shield-check' : 'lock') + '"></i>' +
                                '</button>';
                        }
                        actions += '</div>';
                        return [
                            item.id,
                            escapeHtml(item.name),
                            escapeHtml(item.username || '-'),
                            escapeHtml(item.email),
                            formatRoles(item.roles),
                            formatStatus(item.is_banned),
                            formatDate(item.created_at),
                            actions
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
    if (!hasPermission('roles.manage')) {
        allRoles = [];
        $('#assignRoleSelect').html('<option value="">Select role...</option>');
        $('#addUserRole').html('<option value="">Default (user)</option>');
        return;
    }
    $.ajax({
        url: baseUrl + 'api/admin/roles',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                allRoles = response.data.roles || [];
                var html = '<option value="">Select role...</option>';
                var addHtml = '<option value="">Default (user)</option>';
                allRoles.forEach(function(role) {
                    if (currentUserHighestRank >= getRoleRank(role.name)) {
                        html += '<option value="' + role.id + '">' + escapeHtml(role.name) + '</option>';
                        addHtml += '<option value="' + role.id + '">' + escapeHtml(role.name) + '</option>';
                    }
                });
                $('#assignRoleSelect').html(html);
                $('#addUserRole').html(addHtml);
            }
        },
        error: function() {
            allRoles = [];
            $('#assignRoleSelect').html('<option value="">Select role...</option>');
            $('#addUserRole').html('<option value="">Default (user)</option>');
        }
    });
}

function saveAddUser() {
    var payload = {
        name: $.trim($('#addUserName').val()),
        username: $.trim($('#addUserUsername').val()),
        email: $.trim($('#addUserEmail').val()),
        password: $('#addUserPassword').val()
    };
    var roleId = $('#addUserRole').val();
    if (roleId) payload.role_id = parseInt(roleId, 10);
    if (!payload.name || !payload.username || !payload.email || !payload.password) {
        showError('Name, username, email, dan password wajib diisi');
        return;
    }
    $.ajax({
        url: baseUrl + 'api/admin/users',
        method: 'POST',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.success) {
                showSuccess('User berhasil dibuat');
                bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Gagal membuat user');
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membuat user';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                var errs = xhr.responseJSON.errors;
                msg += ': ' + Object.values(errs).join(', ');
            }
            showError(msg);
        }
    });
}

function openEditUser(id) {
    // Client-side pre-check biar langsung terlihat penjagaannya
    // Ambil data target dari tabel cache (usersTable) untuk cek cepat
    var rowData = null;
    try {
        var tableData = usersTable.rows().data().toArray();
        // DataTables serverSide false menyimpan sebagai array rows; item.id di col 0
        // Fallback: ambil lewat API saja
    } catch(e) {}

    $.ajax({
        url: baseUrl + 'api/admin/users/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var user = response.data;
                var targetRoles = (user.roles || []).map(function(r){ return typeof r === 'string' ? r : r.name; });
                if (!canManageTarget(targetRoles)) {
                    showError('Tidak boleh edit user dengan level lebih tinggi dari Anda.');
                    return;
                }
                $('#editUserId').val(user.id);
                $('#editUserName').val(user.name);
                $('#editUserUsername').val(user.username);
                $('#editUserEmail').val(user.email);
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat data user');
        }
    });
}

function saveEditUser() {
    var userId = $('#editUserId').val();
    var payload = {
        name: $.trim($('#editUserName').val()),
        username: $.trim($('#editUserUsername').val()),
        email: $.trim($('#editUserEmail').val())
    };
    if (!payload.name || !payload.username || !payload.email) {
        showError('Name, username, dan email wajib diisi');
        return;
    }
    $.ajax({
        url: baseUrl + 'api/admin/users/' + userId,
        method: 'PUT',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.success) {
                showSuccess('User berhasil diupdate');
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                usersTable.ajax.reload();
            } else {
                showError(response.message || 'Gagal update user');
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal update user';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                var errs = xhr.responseJSON.errors;
                msg += ': ' + Object.values(errs).join(', ');
            }
            showError(msg);
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
    // Cek penjagaan sebelum buka modal
    $.ajax({
        url: baseUrl + 'api/admin/users/' + userId,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var targetRoles = (response.data.roles || []).map(function(r){ return typeof r === 'string' ? r : r.name; });
                if (!canManageTarget(targetRoles)) {
                    showError('Tidak boleh mengubah role user dengan level lebih tinggi.');
                    return;
                }
                $('#assignRoleUserId').val(userId);
                $('#assignRoleSelect').val('').trigger('change');
                new bootstrap.Modal(document.getElementById('assignRoleModal')).show();
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat data user');
        }
    });
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
    $.ajax({
        url: baseUrl + 'api/admin/users/' + userId,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var targetRoles = (response.data.roles || []).map(function(r){ return typeof r === 'string' ? r : r.name; });
                if (!canManageTarget(targetRoles)) {
                    showError('Tidak boleh ban/unban user dengan level lebih tinggi.');
                    return;
                }
                $('#banUserId').val(userId);
                $('#banAction').val(isBanned ? 'unban' : 'ban');
                $('#banReasonGroup').toggle(!isBanned);
                $('#banWarning').toggle(!isBanned);
                $('#banModalTitle').text(isBanned ? 'Unban User' : 'Ban User');
                $('#btnConfirmBan').text(isBanned ? 'Unban' : 'Ban').removeClass('btn-danger btn-success').addClass(isBanned ? 'btn-success' : 'btn-danger');
                $('#banForm')[0].reset();
                new bootstrap.Modal(document.getElementById('banModal')).show();
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat data user');
        }
    });
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
