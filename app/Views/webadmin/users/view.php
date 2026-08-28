<div class="d-flex flex-column">
    <div class="d-flex flex-stack mb-8">
        <div class="d-flex align-items-center gap-5">
            <div class="symbol symbol-80px">
                <img alt="Avatar" src="<?= base_url('assets/media/avatars/300-' . ($user['id'] % 10 + 1) . '.jpg') ?>"/>
            </div>
            <div>
                <h3 class="fw-bolder text-gray-900 fs-2hx mb-1"><?= esc($user['name']) ?></h3>
                <div class="text-muted fw-semibold">@<?= esc($user['username']) ?></div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge badge-lg <?= $user['is_banned'] ? 'badge-light-danger' : 'badge-light-success' ?> fw-semibold">
                <?= $user['is_banned'] ? 'Banned' : 'Active' ?>
            </span>
        </div>
    </div>
    
    <div class="separator mb-8"></div>
    
    <div class="row g-5 mb-8">
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">Email</div>
            <div class="fw-bold"><?= esc($user['email']) ?></div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">User ID</div>
            <div class="fw-bold font-monospace">#<?= $user['id'] ?></div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">Registered</div>
            <div class="fw-bold"><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">Last Updated</div>
            <div class="fw-bold"><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></div>
        </div>
        <?php if ($user['banned_at']): ?>
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">Banned At</div>
            <div class="fw-bold text-danger"><?= date('M d, Y H:i', strtotime($user['banned_at'])) ?></div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold text-muted fs-7">Ban Reason</div>
            <div class="fw-bold"><?= esc($user['banned_reason'] ?? 'No reason provided') ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="separator mb-8"></div>
    
    <!-- Roles -->
    <div class="mb-8">
        <h6 class="fw-bold text-gray-900 mb-4">Roles</h6>
        <div class="d-flex flex-wrap gap-2">
            <?php if (empty($user['roles'])): ?>
                <span class="badge badge-light-secondary">No roles assigned</span>
            <?php else: ?>
                <?php foreach ($user['roles'] as $role): ?>
                    <span class="badge badge-lg 
                        <?= $role === 'super_admin' ? 'badge-light-danger' : 
                            ($role === 'admin' ? 'badge-light-primary' : 
                            ($role === 'moderator' ? 'badge-light-warning' : 'badge-light-success')) ?>">
                        <?= $role ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Devices -->
    <div class="mb-8">
        <h6 class="fw-bold text-gray-900 mb-4">Registered Devices (<?= count($user['devices']) ?>)</h6>
        <?php if (empty($user['devices'])): ?>
            <div class="text-muted">No devices registered</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th>Platform</th>
                            <th>FCM Token</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user['devices'] as $device): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-lg 
                                        <?= $device['platform'] === 'android' ? 'badge-light-success' : 
                                            ($device['platform'] === 'ios' ? 'badge-light-primary' : 'badge-light-info') ?>">
                                        <?= ucfirst($device['platform']) ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="text-muted"><?= substr($device['fcm_token'], 0, 30) ?>...</code>
                                </td>
                                <td>
                                    <span class="badge <?= $device['is_active'] ? 'badge-light-success' : 'badge-light-secondary' ?>">
                                        <?= $device['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= $device['last_seen_at'] ? date('M d, Y H:i', strtotime($device['last_seen_at'])) : 'Never' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>