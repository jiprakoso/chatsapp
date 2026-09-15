<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Dashboard</h2>
                <div class="text-secondary mt-1">Welcome back! Here's an overview of your application.</div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <!-- Stats Cards -->
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Users</div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <span class="avatar bg-blue-lt me-3"><i class="ti ti-users"></i></span>
                            <span class="h1 mb-0" id="statUsers">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Conversations</div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <span class="avatar bg-green-lt me-3"><i class="ti ti-messages"></i></span>
                            <span class="h1 mb-0" id="statConversations">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Messages</div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <span class="avatar bg-yellow-lt me-3"><i class="ti ti-message"></i></span>
                            <span class="h1 mb-0" id="statMessages">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Registered Devices</div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <span class="avatar bg-red-lt me-3"><i class="ti ti-devices"></i></span>
                            <span class="h1 mb-0" id="statDevices">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table" id="tableRecentActivity">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivityBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Status</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="status-dot status-green me-2"></span>API Server</span>
                            <span class="badge bg-green-lt">Online</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="status-dot status-green me-2"></span>Socket Server</span>
                            <span class="badge bg-green-lt">Online</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="status-dot status-green me-2"></span>Database</span>
                            <span class="badge bg-green-lt">Connected</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="status-dot status-yellow me-2"></span>FCM</span>
                            <span class="badge bg-yellow-lt">Configured</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    // Tunggu auth siap agar request pertama selalu pakai token valid
    window.AdminApp.ready.then(function() {
        loadDashboardStats();
        loadRecentActivity();

        // Refresh every 30 seconds
        setInterval(function() {
            loadDashboardStats();
            loadRecentActivity();
        }, 30000);
    });
});

function loadDashboardStats() {
    $.ajax({
        url: baseUrl + 'api/admin/stats',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                $('#statUsers').text(formatNumber(response.data.total_users));
                $('#statConversations').text(formatNumber(response.data.total_conversations));
                $('#statMessages').text(formatNumber(response.data.total_messages));
                $('#statDevices').text(formatNumber(response.data.total_devices));
            }
        },
        error: function(xhr) {
            console.error('Failed to load stats:', xhr.responseText);
        }
    });
}

function loadRecentActivity() {
    $.ajax({
        url: baseUrl + 'api/admin/activity',
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var html = '';
                response.data.forEach(function(item) {
                    var badgeClass = getBadgeClass(item.type);
                    html += '<tr>' +
                        '<td class="text-secondary">' + escapeHtml(item.time) + '</td>' +
                        '<td><span class="badge ' + badgeClass + '">' + escapeHtml(item.event) + '</span></td>' +
                        '<td>' + escapeHtml(item.user) + '</td>' +
                        '<td class="text-end text-secondary">' + escapeHtml(item.details) + '</td>' +
                    '</tr>';
                });
                $('#recentActivityBody').html(html);
            }
        }
    });
}

function getBadgeClass(type) {
    switch(type) {
        case 'user': return 'bg-blue-lt';
        case 'conversation': return 'bg-green-lt';
        case 'message': return 'bg-yellow-lt';
        case 'ban': return 'bg-red-lt';
        default: return 'bg-azure-lt';
    }
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num;
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
</script>
<?= $this->endSection() ?>
