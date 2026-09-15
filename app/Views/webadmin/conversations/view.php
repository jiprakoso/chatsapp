<div class="p-3">
    <div class="d-flex align-items-center gap-3 mb-3">
        <h3 class="mb-0">
            <?= esc($conversation['name'] ?? '(Private Conversation)') ?>
            <span class="badge <?= $conversation['type'] === 'group' ? 'bg-blue-lt' : 'bg-green-lt' ?> ms-2">
                <?= esc($conversation['type']) ?>
            </span>
        </h3>
        <div class="text-secondary">ID: #<?= $conversation['id'] ?></div>
    </div>

    <hr>

    <!-- Members -->
    <div class="mb-3">
        <h6 class="fw-bold mb-3">Members (<?= count($members) ?>)</h6>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <?php $user = $member['user'] ?? ['name' => 'Unknown', 'username' => 'deleted', 'email' => '']; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-green-lt"><?= esc(strtoupper(mb_substr($user['name'] ?? '?', 0, 1))) ?></span>
                                    <div>
                                        <div class="fw-semibold"><?= esc($user['name']) ?></div>
                                        <div class="text-secondary small">@<?= esc($user['username']) ?> · <?= esc($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge
                                    <?= $member['role'] === 'owner' ? 'bg-red-lt' :
                                        ($member['role'] === 'admin' ? 'bg-blue-lt' : 'bg-green-lt') ?>">
                                    <?= ucfirst(esc($member['role'])) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($member['joined_at'])) ?></td>
                            <td><?= $member['left_at'] ? date('M d, Y H:i', strtotime($member['left_at'])) : '<span class="text-secondary">-</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <hr>

    <!-- Messages -->
    <div class="mb-3">
        <h6 class="fw-bold mb-3">Recent Messages (<?= count($messages) ?>)</h6>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Type</th>
                        <th>Content</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <?php $sender = $msg['sender'] ?? ['name' => 'Unknown', 'username' => '-']; ?>
                        <tr>
                            <td class="fw-semibold font-monospace">#<?= $msg['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-xs bg-azure-lt"><?= esc(strtoupper(mb_substr($sender['name'] ?? '?', 0, 1))) ?></span>
                                    <div>
                                        <div class="fw-semibold"><?= esc($sender['name']) ?></div>
                                        <div class="text-secondary small">@<?= esc($sender['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge
                                    <?= $msg['type'] === 'image' ? 'bg-green-lt' :
                                        ($msg['type'] === 'file' ? 'bg-blue-lt' : 'bg-azure-lt') ?>">
                                    <?= ucfirst(esc($msg['type'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 350px;">
                                    <?= esc($msg['message'] ?? '') ?>
                                    <?php if ($msg['edited_at']): ?>
                                        <span class="badge bg-yellow-lt ms-2">Edited</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <?php if ($msg['deleted_at']): ?>
                                    <span class="badge bg-red-lt">Deleted</span>
                                <?php else: ?>
                                    <span class="badge bg-green-lt">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No messages in this conversation</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
