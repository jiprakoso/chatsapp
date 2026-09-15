<div class="d-flex flex-column">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="avatar avatar-lg bg-blue-lt"><?= esc(strtoupper(mb_substr($user['name'] ?? '?', 0, 1))) ?></span>
        <div>
            <h3 class="mb-0"><?= esc($user['name']) ?></h3>
            <div class="text-secondary">@<?= esc($user['username']) ?></div>
        </div>
        <div class="ms-auto">
            <span class="badge <?= $user['is_banned'] ? 'bg-red-lt' : 'bg-green-lt' ?>">
                <?= $user['is_banned'] ? 'Banned' : 'Active' ?>
            </span>
        </div>
    </div>

    <hr>

    <div class="datagrid mb-3">
        <div class="datagrid-item">
            <div class="datagrid-title">Email</div>
            <div class="datagrid-content"><?= esc($user['email']) ?></div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title">User ID</div>
            <div class="datagrid-content font-monospace">#<?= $user['id'] ?></div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title">Registered</div>
            <div class="datagrid-content"><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title">Last Updated</div>
            <div class="datagrid-content"><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></div>
        </div>
        <?php if ($user['banned_at']): ?>
        <div class="datagrid-item">
            <div class="datagrid-title">Banned At</div>
            <div class="datagrid-content text-danger"><?= date('M d, Y H:i', strtotime($user['banned_at'])) ?></div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title">Ban Reason</div>
            <div class="datagrid-content"><?= esc($user['banned_reason'] ?? 'No reason provided') ?></div>
        </div>
        <?php endif; ?>
    </div>

    <hr>

    <!-- Roles -->
    <div class="mb-3">
        <h6 class="fw-bold mb-2">Roles</h6>
        <div class="d-flex flex-wrap gap-1">
            <?php if (empty($user['roles'])): ?>
                <span class="badge bg-azure-lt">No roles assigned</span>
            <?php else: ?>
                <?php foreach ($user['roles'] as $role): ?>
                    <span class="badge
                        <?= $role === 'super_admin' ? 'bg-red-lt' :
                            ($role === 'admin' ? 'bg-blue-lt' :
                            ($role === 'moderator' ? 'bg-yellow-lt' : 'bg-green-lt')) ?>">
                        <?= esc($role) ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Devices -->
    <div class="mb-3">
        <h6 class="fw-bold mb-2">Registered Devices (<?= count($user['devices']) ?>)</h6>
        <?php if (empty($user['devices'])): ?>
            <div class="text-secondary">No devices registered</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
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
                                    <span class="badge
                                        <?= $device['platform'] === 'android' ? 'bg-green-lt' :
                                            ($device['platform'] === 'ios' ? 'bg-blue-lt' : 'bg-azure-lt') ?>">
                                        <?= ucfirst(esc($device['platform'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="text-secondary"><?= esc(substr($device['fcm_token'], 0, 30)) ?>...</code>
                                </td>
                                <td>
                                    <span class="badge <?= $device['is_active'] ? 'bg-green-lt' : 'bg-azure-lt' ?>">
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
