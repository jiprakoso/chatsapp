<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Settings</h2>
                <div class="text-secondary mt-1">System configuration and management</div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-lg-6">
                <!-- JWT Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">JWT Configuration</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">JWT Secret</label>
                            <div class="input-group">
                                <input type="password" id="jwtSecret" class="form-control" value="<?= esc(env('JWT_SECRET')) ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('jwtSecret')">
                                    <i class="ti ti-eye" id="jwtSecretIcon"></i>
                                </button>
                            </div>
                            <div class="form-hint">Set in .env file (JWT_SECRET). Changing requires restart.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Token TTL (seconds)</label>
                            <input type="number" id="jwtTtl" class="form-control" value="3600" readonly>
                            <div class="form-hint">Set in app/Config/Jwt.php. Default: 3600 (1 hour)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Internal API Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Internal API (Service-to-Service)</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label class="form-label">Internal API Key</label>
                            <div class="input-group">
                                <input type="password" id="internalApiKey" class="form-control" value="<?= esc(env('INTERNAL_API_KEY')) ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('internalApiKey')">
                                    <i class="ti ti-eye" id="internalApiKeyIcon"></i>
                                </button>
                            </div>
                            <div class="form-hint">Used by Node Socket.IO server. Set in .env (INTERNAL_API_KEY).</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- FCM Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Firebase Cloud Messaging</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Service Account Path</label>
                            <input type="text" id="fcmServiceAccount" class="form-control" value="<?= esc(env('FIREBASE_SERVICE_ACCOUNT_PATH') ?? './service-account.json') ?>" readonly>
                            <div class="form-hint">Set in socket-server/.env (FIREBASE_SERVICE_ACCOUNT_PATH).</div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <div><strong>FCM Configuration:</strong> FCM is configured in the Node.js Socket.IO server (<code>socket-server/</code>). Generate the JSON from Firebase Console &gt; Project Settings &gt; Service Accounts.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Upload Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">File Upload Limits</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Max Image Size</div>
                                <div class="datagrid-content">20 MB <span class="text-secondary">(jpeg, png, gif, webp)</span></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Max Video Size</div>
                                <div class="datagrid-content">20 MB <span class="text-secondary">(mp4, webm, quicktime)</span></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Max Document Size</div>
                                <div class="datagrid-content">20 MB <span class="text-secondary">(pdf, doc, xls, zip, txt)</span></div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <div><strong>PHP Configuration Required:</strong> Ensure <code>upload_max_filesize</code> and <code>post_max_size</code> in <code>php.ini</code> are at least <code>20M</code>.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Database Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Database Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Driver</div>
                                <div class="datagrid-content"><?= esc(env('database.default.DBDriver') ?? 'MySQLi') ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Host</div>
                                <div class="datagrid-content font-monospace"><?= esc(env('database.default.hostname')) ?>:<?= esc(env('database.default.port')) ?></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Database</div>
                                <div class="datagrid-content font-monospace"><?= esc(env('database.default.database')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- System Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="clearCache()">
                                <i class="ti ti-trash me-2"></i>Clear Cache
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="runMigrations()">
                                <i class="ti ti-database me-2"></i>Run Migrations
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="runSeeder()">
                                <i class="ti ti-flask me-2"></i>Run RBAC Seeder
                            </button>
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
function togglePassword(inputId) {
    var input = $('#' + inputId);
    var icon = $('#' + inputId + 'Icon');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('ti-eye').addClass('ti-eye-off');
    } else {
        input.attr('type', 'password');
        icon.removeClass('ti-eye-off').addClass('ti-eye');
    }
}

function clearCache() {
    if (!confirm('Clear all cache files?')) return;

    showInfo('Clearing cache...');
    $.ajax({
        url: baseUrl + 'api/admin/cache/clear',
        method: 'POST',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success) {
                showSuccess('Cache cleared successfully');
            } else {
                showError(response.message || 'Failed to clear cache');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || ('Failed to clear cache (' + (xhr.status || 'error') + ')'));
        }
    });
}

function runMigrations() {
    if (!confirm('Run all pending migrations? This will modify the database.')) return;

    showInfo('Running migrations...');
    showError('Run migrations via CLI: php spark migrate');
}

function runSeeder() {
    if (!confirm('Run RBAC seeder? This will seed roles and permissions.')) return;

    showInfo('Running seeder...');
    showError('Run seeder via CLI: php spark db:seed RolePermissionSeeder');
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

function showInfo(message) {
    toastr.info(message);
}
</script>
<?= $this->endSection() ?>
