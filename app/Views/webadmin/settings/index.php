<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Settings</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">System configuration and management</div>
                </div>
            </div>
            
            <!-- JWT Settings -->
            <div class="card card-flush mb-8">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">JWT Configuration</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form id="jwtSettingsForm">
                        <div class="row g-5 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">JWT Secret</label>
                                <div class="input-group">
                                    <input type="password" name="jwt_secret" id="jwtSecret" class="form-control form-control-solid" value="<?= esc(env('JWT_SECRET')) ?>" readonly>
                                    <button type="button" class="btn btn-light-secondary" onclick="togglePassword('jwtSecret')">
                                        <i class="ki-duotone ki-eye fs-3" id="jwtSecretIcon"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    </button>
                                </div>
                                <div class="form-text">Set in .env file (JWT_SECRET). Changing requires restart.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Token TTL (seconds)</label>
                                <input type="number" name="jwt_ttl" id="jwtTtl" class="form-control form-control-solid" value="3600" readonly>
                                <div class="form-text">Set in app/Config/Jwt.php. Default: 3600 (1 hour)</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Internal API Settings -->
            <div class="card card-flush mb-8">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">Internal API (Service-to-Service)</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form id="internalApiForm">
                        <div class="row g-5 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Internal API Key</label>
                                <div class="input-group">
                                    <input type="password" name="internal_api_key" id="internalApiKey" class="form-control form-control-solid" value="<?= esc(env('INTERNAL_API_KEY')) ?>" readonly>
                                    <button type="button" class="btn btn-light-secondary" onclick="togglePassword('internalApiKey')">
                                        <i class="ki-duotone ki-eye fs-3" id="internalApiKeyIcon"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    </button>
                                </div>
                                <div class="form-text">Used by Node Socket.IO server to call internal endpoints. Set in .env (INTERNAL_API_KEY).</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FCM Settings -->
            <div class="card card-flush mb-8">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">Firebase Cloud Messaging</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form id="fcmSettingsForm">
                        <div class="row g-5 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Account Path</label>
                                <input type="text" name="fcm_service_account" id="fcmServiceAccount" class="form-control form-control-solid" value="<?= esc(env('FIREBASE_SERVICE_ACCOUNT_PATH') ?? './service-account.json') ?>" readonly>
                                <div class="form-text">Path to Firebase service account JSON. Set in socket-server/.env (FIREBASE_SERVICE_ACCOUNT_PATH).</div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ki-duotone ki-information-2 fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div>
                                <strong>FCM Configuration:</strong> FCM is configured in the Node.js Socket.IO server (<code>socket-server/</code>). 
                                The service account JSON must be placed at the path specified above. Generate it from Firebase Console > Project Settings > Service Accounts.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Upload Settings -->
            <div class="card card-flush mb-8">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">File Upload Limits</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5 mb-5">
                        <div class="col-md-4">
                            <div class="card card-bordered h-100">
                                <div class="card-body text-center py-8">
                                    <div class="symbol symbol-60px symbol-circle symbol-light-primary mb-4 mx-auto">
                                        <i class="ki-duotone ki-picture fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <h5 class="fw-bold">Max Image Size</h5>
                                    <div class="fs-2hx fw-bolder text-gray-900">20 MB</div>
                                    <div class="text-muted fs-7 mt-2">MIME: jpeg, png, gif, webp</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-bordered h-100">
                                <div class="card-body text-center py-8">
                                    <div class="symbol symbol-60px symbol-circle symbol-light-success mb-4 mx-auto">
                                        <i class="ki-duotone ki-monitor-mobile fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <h5 class="fw-bold">Max Video Size</h5>
                                    <div class="fs-2hx fw-bolder text-gray-900">20 MB</div>
                                    <div class="text-muted fs-7 mt-2">MIME: mp4, webm, quicktime</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-bordered h-100">
                                <div class="card-body text-center py-8">
                                    <div class="symbol symbol-60px symbol-circle symbol-light-warning mb-4 mx-auto">
                                        <i class="ki-duotone ki-file fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <h5 class="fw-bold">Max Document Size</h5>
                                    <div class="fs-2hx fw-bolder text-gray-900">20 MB</div>
                                    <div class="text-muted fs-7 mt-2">MIME: pdf, doc, docx, xls, xlsx, zip, txt</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="ki-duotone ki-information-2 fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div>
                            <strong>PHP Configuration Required:</strong> Ensure <code>upload_max_filesize</code> and <code>post_max_size</code> in your <code>php.ini</code> are set to at least <code>20M</code> (or higher) for these limits to work.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Database Info -->
            <div class="card card-flush mb-8">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">Database Information</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-45px symbol-circle symbol-light-primary">
                                    <i class="ki-duotone ki-data fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Driver</div>
                                    <div class="text-muted"><?= env('database.default.DBDriver') ?? 'MySQLi' ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-45px symbol-circle symbol-light-success">
                                    <i class="ki-duotone ki-router fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Host</div>
                                    <div class="text-muted font-monospace"><?= env('database.default.hostname') ?>:<?= env('database.default.port') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-45px symbol-circle symbol-light-info">
                                    <i class="ki-duotone ki-folder fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Database</div>
                                    <div class="text-muted font-monospace"><?= env('database.default.database') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Actions -->
            <div class="card card-flush">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 fs-3">System Actions</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-wrap gap-3">
                        <button type="button" class="btn btn-light-primary" onclick="clearCache()">
                            <i class="ki-duotone ki-trash fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>Clear Cache
                        </button>
                        <button type="button" class="btn btn-light-warning" onclick="runMigrations()">
                            <i class="ki-duotone ki-data fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>Run Migrations
                        </button>
                        <button type="button" class="btn btn-light-danger" onclick="runSeeder()">
                            <i class="ki-duotone ki-flask fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Run RBAC Seeder
                        </button>
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
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function togglePassword(inputId) {
    var input = $('#' + inputId);
    var icon = $('#' + inputId + 'Icon');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('ki-eye').addClass('ki-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('ki-eye-slash').addClass('ki-eye');
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
        error: function() {
            showError('Cache clear endpoint not implemented');
        }
    });
}

function runMigrations() {
    if (!confirm('Run all pending migrations? This will modify the database.')) return;
    
    showInfo('Running migrations...');
    // This would need a CLI endpoint or spark command
    showError('Run migrations via CLI: php spark migrate');
}

function runSeeder() {
    if (!confirm('Run RBAC seeder? This will seed roles and permissions.')) return;
    
    showInfo('Running seeder...');
    // This would need a CLI endpoint
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