<div class="p-5">
    <div class="d-flex flex-stack mb-8">
        <div>
            <h4 class="fw-bolder text-gray-900 mb-1">
                <?= esc($conversation['name'] ?? '(Private Conversation)') ?>
                <span class="badge <?= $conversation['type'] === 'group' ? 'badge-light-primary' : 'badge-light-success' ?> ms-2">
                    <?= $conversation['type'] ?>
                </span>
            </h4>
            <div class="text-muted fw-semibold">ID: #<?= $conversation['id'] ?></div>
        </div>
    </div>
    
    <div class="separator mb-8"></div>
    
    <!-- Members -->
    <div class="mb-8">
        <h6 class="fw-bold text-gray-900 mb-4">Members (<?= count($members) ?>)</h6>
        <div class="table-responsive">
            <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4">
                <thead>
                    <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
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
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-35px">
                                        <img alt="Avatar" src="<?= base_url('assets/media/avatars/300-' . (($member['user_id'] % 10) + 1) . '.jpg') ?>"/>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= esc($user['name']) ?></div>
                                        <div class="text-muted fs-7">@<?= esc($user['username']) ?> · <?= esc($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge 
                                    <?= $member['role'] === 'owner' ? 'badge-light-danger' : 
                                        ($member['role'] === 'admin' ? 'badge-light-primary' : 'badge-light-success') ?>">
                                    <?= ucfirst($member['role']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($member['joined_at'])) ?></td>
                            <td><?= $member['left_at'] ? date('M d, Y H:i', strtotime($member['left_at'])) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="separator mb-8"></div>
    
    <!-- Messages -->
    <div class="mb-8">
        <div class="d-flex flex-stack mb-4">
            <h6 class="fw-bold text-gray-900 mb-0">Recent Messages (<?= count($messages) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4">
                <thead>
                    <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-80px">ID</th>
                        <th>Sender</th>
                        <th>Type</th>
                        <th>Content</th>
                        <th class="min-w-180px">Time</th>
                        <th class="min-w-100px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <?php $sender = $msg['sender'] ?? ['name' => 'Unknown', 'username' => 'deleted']; ?>
                        <tr>
                            <td class="fw-semibold font-monospace">#<?= $msg['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="symbol symbol-25px">
                                        <img alt="Avatar" src="<?= base_url('assets/media/avatars/300-' . (($msg['sender_id'] % 10) + 1) . '.jpg') ?>"/>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= esc($sender['name']) ?></div>
                                        <div class="text-muted fs-7">@<?= esc($sender['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge 
                                    <?= $msg['type'] === 'image' ? 'badge-light-success' : 
                                        ($msg['type'] === 'file' ? 'badge-light-primary' : 'badge-light-secondary') ?>">
                                    <?= ucfirst($msg['type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 350px;">
                                    <?= esc($msg['content'] ?? '') ?>
                                    <?php if ($msg['edited_at']): ?>
                                        <span class="badge badge-light-warning ms-2">Edited</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <?php if ($msg['deleted_at']): ?>
                                    <span class="badge badge-light-danger">Deleted</span>
                                <?php else: ?>
                                    <span class="badge badge-light-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-8">No messages in this conversation</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>