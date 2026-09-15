<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Dashboard</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">Welcome back! Here's an overview of your application.</div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-5 g-xl-10 mb-10">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100">
                        <div class="card-body py-5">
                            <div class="d-flex flex-stack">
                                <div>
                                    <span class="card-label fw-bold text-muted">Total Users</span>
                                    <h2 class="card-title fw-bolder text-gray-900 fs-1 mt-3" id="statUsers">-</h2>
                                </div>
                                <div class="symbol symbol-60px symbol-circle symbol-light-primary">
                                    <i class="ki-duotone ki-people fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100">
                        <div class="card-body py-5">
                            <div class="d-flex flex-stack">
                                <div>
                                    <span class="card-label fw-bold text-muted">Active Conversations</span>
                                    <h2 class="card-title fw-bolder text-gray-900 fs-1 mt-3" id="statConversations">-</h2>
                                </div>
                                <div class="symbol symbol-60px symbol-circle symbol-light-success">
                                    <i class="ki-duotone ki-messages fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100">
                        <div class="card-body py-5">
                            <div class="d-flex flex-stack">
                                <div>
                                    <span class="card-label fw-bold text-muted">Total Messages</span>
                                    <h2 class="card-title fw-bolder text-gray-900 fs-1 mt-3" id="statMessages">-</h2>
                                </div>
                                <div class="symbol symbol-60px symbol-circle symbol-light-warning">
                                    <i class="ki-duotone ki-message-text-2 fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100">
                        <div class="card-body py-5">
                            <div class="d-flex flex-stack">
                                <div>
                                    <span class="card-label fw-bold text-muted">Registered Devices</span>
                                    <h2 class="card-title fw-bolder text-gray-900 fs-1 mt-3" id="statDevices">-</h2>
                                </div>
                                <div class="symbol symbol-60px symbol-circle symbol-light-danger">
                                    <i class="ki-duotone ki-devices fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts / Recent Activity -->
            <div class="row g-5 g-xl-10">
                <div class="col-xl-8">
                    <div class="card card-flush h-100">
                        <div class="card-header border-0 pt-5">
                            <div class="card-title">
                                <h3 class="fw-bolder text-gray-900 fs-3">Recent Activity</h3>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4" id="tableRecentActivity">
                                    <thead>
                                        <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">Time</th>
                                            <th>Event</th>
                                            <th class="min-w-150px">User</th>
                                            <th class="text-end min-w-100px">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentActivityBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <div class="card card-flush h-100">
                        <div class="card-header border-0 pt-5">
                            <div class="card-title">
                                <h3 class="fw-bolder text-gray-900 fs-3">System Status</h3>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-column">
                                <div class="d-flex flex-stack mb-5 pb-5 border-bottom border-gray-200">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-10px symbol-circle symbol-success me-3"></div>
                                        <div class="fw-semibold">API Server</div>
                                    </div>
                                    <span class="badge badge-light-success fw-bold">Online</span>
                                </div>
                                <div class="d-flex flex-stack mb-5 pb-5 border-bottom border-gray-200">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-10px symbol-circle symbol-success me-3"></div>
                                        <div class="fw-semibold">Socket Server</div>
                                    </div>
                                    <span class="badge badge-light-success fw-bold">Online</span>
                                </div>
                                <div class="d-flex flex-stack mb-5 pb-5 border-bottom border-gray-200">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-10px symbol-circle symbol-success me-3"></div>
                                        <div class="fw-semibold">Database</div>
                                    </div>
                                    <span class="badge badge-light-success fw-bold">Connected</span>
                                </div>
                                <div class="d-flex flex-stack mb-5">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-10px symbol-circle symbol-warning me-3"></div>
                                        <div class="fw-semibold">FCM</div>
                                    </div>
                                    <span class="badge badge-light-warning fw-bold">Configured</span>
                                </div>
                            </div>
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
    loadDashboardStats();
    loadRecentActivity();
    
    // Refresh every 30 seconds
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivity();
    }, 30000);
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
                        '<td class="text-muted fw-semibold">' + item.time + '</td>' +
                        '<td><span class="badge ' + badgeClass + '">' + item.event + '</span></td>' +
                        '<td>' + item.user + '</td>' +
                        '<td class="text-end text-muted">' + item.details + '</td>' +
                    '</tr>';
                });
                $('#recentActivityBody').html(html);
            }
        }
    });
}

function getBadgeClass(type) {
    switch(type) {
        case 'user': return 'badge-light-primary';
        case 'conversation': return 'badge-light-success';
        case 'message': return 'badge-light-warning';
        case 'ban': return 'badge-light-danger';
        default: return 'badge-light-secondary';
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
</script>
<?= $this->endSection() ?>