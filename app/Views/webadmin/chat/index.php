<?= $this->extend('webadmin/layout') ?>

<?= $this->section('pageStyles') ?>
<style>
#messageList { height: 480px; overflow-y: auto; }
.chat-bubble { max-width: 75%; padding: .5rem .75rem; border-radius: .5rem; }
.chat-bubble-mine { background: var(--tblr-primary); color: #fff; margin-left: auto; }
.chat-bubble-theirs { background: var(--tblr-bg-surface-secondary); }
.chat-day-divider { display: flex; align-items: center; gap: .5rem; }
#typingIndicator { min-height: 1.5rem; }
.conv-item.active { border-left: 3px solid var(--tblr-primary); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Live Chat</h2>
                <div class="text-secondary mt-1">Read and reply conversations as admin <span class="badge bg-azure-lt ms-1" id="socketStatus">Connecting...</span></div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button type="button" class="btn btn-outline-primary" onclick="openNewPrivate()">
                    <i class="ti ti-plus me-1"></i>New Chat
                </button>
                <button type="button" class="btn btn-primary" onclick="openNewGroup()">
                    <i class="ti ti-users me-1"></i>New Group
                </button>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <!-- Conversation list -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div class="input-icon w-100">
                            <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                            <input type="text" id="convSearch" class="form-control" placeholder="Search conversations...">
                        </div>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" id="conversationList" style="max-height: 560px;">
                    </div>
                </div>
            </div>
            <!-- Room -->
            <div class="col-lg-8">
                <div class="card">
                    <?php if ($activeConversationId): ?>
                    <div class="card-header">
                        <div>
                            <h3 class="card-title" id="roomTitle">Conversation #<?= (int) $activeConversationId ?></h3>
                            <div class="text-secondary small" id="roomSubtitle">Loading...</div>
                        </div>
                        <div class="card-actions">
                            <a class="btn btn-outline-primary btn-sm" href="<?= base_url('conversations') ?>">
                                <i class="ti ti-arrow-left me-1"></i>All
                            </a>
                        </div>
                    </div>
                    <div class="card-body" id="messageList">
                    </div>
                    <div class="card-body py-2 border-top">
                        <div class="d-flex flex-wrap gap-2 align-items-center" id="memberBar">
                            <span class="text-secondary small">Members:</span>
                            <span class="text-secondary small" id="memberChips">Loading...</span>
                        </div>
                    </div>
                    <div class="card-footer" id="roomFooter">
                        <div class="text-secondary small" id="typingIndicator"></div>
                        <div id="viewOnlyAlert" class="alert alert-warning py-2 mb-2 d-none">
                            <i class="ti ti-eye me-1"></i>View only — Anda bukan member conversation ini, tidak bisa mengirim pesan.
                        </div>
                        <div class="input-group">
                            <input type="text" id="messageInput" class="form-control" placeholder="Type a message..." autocomplete="off">
                            <button class="btn btn-primary" id="btnSend" type="button">
                                <i class="ti ti-send me-1"></i>Send
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card-body">
                        <div class="empty py-5">
                            <div class="empty-icon"><i class="ti ti-messages" style="font-size: 3rem;"></i></div>
                            <p class="empty-title">No conversation selected</p>
                            <p class="empty-subtitle text-secondary">Pick a conversation on the left to start chatting.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Private Chat Modal -->
<div class="modal modal-blur fade" id="newPrivateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Private Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Chat with</label>
                    <select id="privateUserSelect" class="form-select">
                        <option value="">Select user...</option>
                    </select>
                    <div class="form-hint">Kalau private chat dengan user ini sudah ada, room yang lama yang dibuka (tidak duplikat).</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnCreatePrivate">Open Chat</button>
            </div>
        </div>
    </div>
</div>

<!-- New Group Modal -->
<div class="modal modal-blur fade" id="newGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Group name</label>
                    <input type="text" id="groupName" class="form-control" placeholder="e.g. Tim Support" maxlength="150">
                </div>
                <div class="mb-0">
                    <label class="form-label">Members (min. 1)</label>
                    <div id="groupMemberList" style="max-height: 240px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnCreateGroup">Create Group</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/vendor/socketio/socket.io.min.js') ?>"></script>
<script>
var activeConversationId = <?= $activeConversationId ? (int) $activeConversationId : 'null' ?>;
var adminUserId = <?= (int) $adminUserId ?>;
var isViewOnly = <?= !empty($isViewOnly) ? 'true' : 'false' ?>;
var userMap = {};
var renderedIds = new Set();
var socket = null;
var typingTimer = null;

function hasPermission(perm) {
    var u = window.AdminApp.getUser && window.AdminApp.getUser();
    if (!u || !Array.isArray(u.permissions)) return false;
    if (u.permissions.includes('*') || u.permissions.includes(perm)) return true;
    var prefix = perm.split('.')[0] + '.*';
    if (u.permissions.includes(prefix)) return true;
    return false;
}
function canViewAllConversations() {
    return hasPermission('conversations.view_all') || hasPermission('conversations.manage');
}

function applyViewOnly() {
    if (isViewOnly) {
        $('#messageInput').prop('disabled', true).attr('placeholder', 'View only — bukan member');
        $('#btnSend').prop('disabled', true);
        $('#viewOnlyAlert').removeClass('d-none');
        $('#roomSubtitle').append(' <span class="badge bg-yellow-lt ms-1">View only</span>');
    }
}
$(document).ready(function() {
    window.AdminApp.ready.then(function() {
        loadConversationList();
        loadChatUsers();
        if (activeConversationId) {
            applyViewOnly();
            loadRoom(activeConversationId);
            if (!isViewOnly) connectSocket();
        }
        $('#convSearch').on('keyup', debounce(function() {
            filterConversationList(this.value);
        }, 300));
        $('#btnSend').on('click', sendMessage);
        $('#btnCreatePrivate').on('click', createPrivateChat);
        $('#btnCreateGroup').on('click', createGroupChat);
        $('#messageInput').on('keydown', function(e) {
            if (e.key === 'Enter') sendMessage();
            else handleTyping();
        });
    });
});

function socketBaseUrl() {
    return window.location.protocol + '//' + window.location.hostname + ':4000';
}

// ---------- Conversation list ----------

function loadConversationList() {
    function handleAdmin(data) {
        if (Array.isArray(data.data)) renderConversationList(data.data);
    }
    function handlePublic(data) {
        if (Array.isArray(data.conversations)) {
            var mapped = data.conversations.map(function(c) {
                return {
                    id: c.id,
                    name: c.name,
                    display_name: c.display_name || c.name,
                    type: c.type,
                    last_message_preview: c.last_message_preview,
                    last_message_at: c.last_message_at
                };
            });
            renderConversationList(mapped);
        }
    }
    function loadPublic() {
        $.ajax({
            url: baseUrl + 'api/conversations',
            method: 'GET',
            headers: getAuthHeaders(),
            success: function(response) {
                if (response.success && response.data) handlePublic(response.data);
            },
            error: function(xhr) {
                if (xhr.status === 401) window.location.href = baseUrl + 'login';
            }
        });
    }
    // user/moderator hanya Live Chat — langsung pakai endpoint publik (membership)
    // agar tidak menembak api/admin/conversations yang pasti 403
    if (!canViewAllConversations()) { loadPublic(); return; }

    $.ajax({
        url: baseUrl + 'api/admin/conversations',
        method: 'GET',
        headers: getAuthHeaders(),
        data: { per_page: 200 },
        success: function(response) {
            if (response.success && response.data) {
                if (Array.isArray(response.data.data)) handleAdmin(response.data);
                else if (Array.isArray(response.data.conversations)) handlePublic(response.data);
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) { window.location.href = baseUrl + 'login'; return; }
            if (xhr.status === 403) loadPublic();
        }
    });
}

function renderConversationList(items) {
    var html = '';
    items.forEach(function(item) {
        // private: admin non-member -> "UserA - UserB", member -> lawan bicara, group -> name
        var name = item.display_name || item.name || '(Private)';
        var preview = item.last_message_preview || 'No messages';
        html += '<a href="' + baseUrl + 'chat/' + item.id + '" class="list-group-item list-group-item-action conv-item' +
            (item.id == activeConversationId ? ' active' : '') + '" data-name="' + escapeHtml(name).toLowerCase() + '">' +
            '<div class="d-flex w-100 justify-content-between">' +
                '<strong>' + escapeHtml(name) + '</strong>' +
                '<small class="text-secondary">' + formatTime(item.last_message_at) + '</small>' +
            '</div>' +
            '<div class="d-flex w-100 justify-content-between align-items-center">' +
                '<small class="text-secondary text-truncate" style="max-width: 70%;">' + escapeHtml(preview) + '</small>' +
                '<span class="badge ' + (item.type === 'group' ? 'bg-blue-lt' : 'bg-green-lt') + '">' + escapeHtml(item.type) + '</span>' +
            '</div>' +
        '</a>';
    });
    $('#conversationList').html(html || '<div class="p-3 text-secondary">No conversations</div>');
}

function filterConversationList(q) {
    q = (q || '').toLowerCase();
    $('#conversationList .conv-item').each(function() {
        $(this).toggle($(this).data('name').indexOf(q) !== -1);
    });
}

// ---------- Room ----------

function loadRoom(conversationId) {
    function handle(conv, members, preloadedMessages) {
        members.forEach(function(m) {
            if (m.user) userMap[m.user.id] = m.user.name;
            else if (m.name) userMap[m.id] = m.name;
        });
        // private: admin non-member (viewOnly) -> "UserA - UserB", member -> lawan bicara, group -> name
        var title = conv.display_name || conv.name;
        if (conv.type === 'private') {
            if (isViewOnly) {
                var names = members.map(function(m){ return m.user ? m.user.name : ''; }).filter(Boolean);
                title = names.length ? names.join(' - ') : (conv.name || 'Private');
            } else {
                var other = null;
                members.forEach(function(m){
                    var uid = m.user ? m.user.id : m.user_id;
                    if (parseInt(uid,10) !== adminUserId) other = m;
                });
                title = (other && other.user && other.user.name) ? other.user.name : (conv.name || 'Private');
            }
        } else {
            title = conv.name || title || ('Conversation #' + conv.id);
        }
        $('#roomTitle').text(title);
        $('#roomSubtitle').text(members.length + ' members');
        renderMemberChips(members);
        if (preloadedMessages && Array.isArray(preloadedMessages) && isViewOnly) {
            preloadedMessages.forEach(appendMessage);
            scrollBottom();
        } else {
            loadHistory(conversationId);
        }
    }
    function loadPublicRoom() {
        $.ajax({
            url: baseUrl + 'api/conversations/' + conversationId,
            method: 'GET',
            headers: getAuthHeaders(),
            success: function(response) {
                if (response.success && response.data) {
                    var conv = response.data;
                    var members = (conv.members || []).map(function(u) { return { user: u, user_id: u.id }; });
                    handle(conv, members);
                }
            },
            error: function(xhr2) {
                if (xhr2.responseJSON && xhr2.responseJSON.message) showError(xhr2.responseJSON.message);
            }
        });
    }
    if (!canViewAllConversations()) { loadPublicRoom(); return; }

    $.ajax({
        url: baseUrl + 'api/admin/conversations/' + conversationId,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                if (response.data.conversation && response.data.members) {
                    handle(response.data.conversation, response.data.members, response.data.messages);
                } else if (response.data.id) {
                    var conv = response.data;
                    var members = (conv.members || []).map(function(u) { return { user: u, user_id: u.id }; });
                    handle(conv, members);
                }
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) loadPublicRoom();
            else if (xhr.responseJSON && xhr.responseJSON.message) showError(xhr.responseJSON.message);
        }
    });
}

function renderMemberChips(members) {
    if (!members.length) {
        $('#memberChips').html('<span class="text-secondary small">No members</span>');
        return;
    }
    var html = members.map(function(m) {
        var name = (m.user && m.user.name) || ('User #' + m.user_id);
        var initial = escapeHtml(String(name).charAt(0).toUpperCase());
        var roleBadge = m.role && m.role !== 'member'
            ? ' <span class="badge ' + (m.role === 'owner' ? 'bg-red-lt' : 'bg-blue-lt') + '">' + escapeHtml(m.role) + '</span>'
            : '';
        return '<span class="avatar avatar-xs me-1" title="' + escapeHtml(name) + '">' + initial + '</span>' +
            '<span class="me-2 small">' + escapeHtml(name) + roleBadge + '</span>';
    }).join('');
    $('#memberChips').html(html);
}

function loadHistory(conversationId) {
    $.ajax({
        url: baseUrl + 'api/conversations/' + conversationId + '/messages',
        method: 'GET',
        headers: getAuthHeaders(),
        data: { limit: 50 },
        success: function(response) {
            if (response.success && response.data && Array.isArray(response.data.messages)) {
                response.data.messages.forEach(appendMessage);
                scrollBottom();
                markRead(conversationId);
            }
        }
    });
}

function appendMessage(msg) {
    if (!msg || renderedIds.has(String(msg.id))) return;
    renderedIds.add(String(msg.id));
    if (msg.deleted_at) return;

    var mine = parseInt(msg.sender_id, 10) === adminUserId;
    var name = userMap[msg.sender_id] || ('User #' + msg.sender_id);
    var html = '<div class="d-flex mb-2 ' + (mine ? 'justify-content-end' : '') + '" id="msg-' + msg.id + '">' +
        '<div class="chat-bubble ' + (mine ? 'chat-bubble-mine' : 'chat-bubble-theirs') + '">' +
            (mine ? '' : '<div class="small fw-semibold mb-1">' + escapeHtml(name) + '</div>') +
            '<div>' + escapeHtml(msg.message || '') + '</div>' +
            '<div class="small mt-1 ' + (mine ? '' : 'text-secondary') + '">' + formatTime(msg.created_at) +
                (msg.edited_at ? ' · edited' : '') + '</div>' +
        '</div>' +
    '</div>';
    $('#messageList').append(html);
}

function scrollBottom() {
    var el = document.getElementById('messageList');
    if (el) el.scrollTop = el.scrollHeight;
}

function markRead(conversationId) {
    $.ajax({
        url: baseUrl + 'api/conversations/' + conversationId + '/read',
        method: 'POST',
        headers: getAuthHeaders()
    });
}

// ---------- Realtime ----------

function connectSocket() {
    var token = localStorage.getItem('admin_token');
    if (!token || typeof io === 'undefined') {
        setSocketStatus(false, 'Socket.IO unavailable');
        return;
    }
    socket = io(socketBaseUrl());
    socket.on('connect', function() {
        socket.emit('authenticate', { token: token }, function(res) {
            if (res && res.success) {
                setSocketStatus(true, 'Connected');
                socket.emit('join_conversation', { conversation_id: activeConversationId });
            } else {
                setSocketStatus(false, 'Auth failed');
            }
        });
    });
    socket.on('disconnect', function() { setSocketStatus(false, 'Disconnected'); });
    socket.on('new_message', function(msg) {
        if (parseInt(msg.conversation_id, 10) !== activeConversationId) return;
        appendMessage(msg);
        scrollBottom();
        markRead(activeConversationId);
        loadConversationList();
    });
    socket.on('message_updated', function(msg) {
        var el = document.getElementById('msg-' + msg.id);
        if (el) {
            renderedIds.delete(String(msg.id));
            el.remove();
            appendMessage(msg);
            scrollBottom();
        }
    });
    socket.on('message_deleted', function(payload) {
        var el = document.getElementById('msg-' + (payload.message_id || payload.id));
        if (el) el.remove();
    });
    socket.on('user_typing', function(payload) {
        if (parseInt(payload.conversation_id, 10) !== activeConversationId) return;
        if (parseInt(payload.user_id, 10) === adminUserId) return;
        var name = userMap[payload.user_id] || 'Someone';
        $('#typingIndicator').text(name + ' is typing...');
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() { $('#typingIndicator').text(''); }, 3000);
    });
    socket.on('user_stopped_typing', function() { $('#typingIndicator').text(''); });
}

function setSocketStatus(online, text) {
    $('#socketStatus').text(text)
        .removeClass('bg-green-lt bg-red-lt bg-azure-lt')
        .addClass(online ? 'bg-green-lt' : 'bg-red-lt');
}

function handleTyping() {
    if (!socket || !socket.connected) return;
    socket.emit('typing_start', { conversation_id: activeConversationId });
    clearTimeout(typingTimer);
    typingTimer = setTimeout(function() {
        if (socket) socket.emit('typing_stop', { conversation_id: activeConversationId });
    }, 2000);
}

function sendMessage() {
    if (isViewOnly) { showError('View only — bukan member, tidak bisa mengirim'); return; }
    var input = $('#messageInput');
    var text = (input.val() || '').trim();
    if (!text || !activeConversationId) return;

    var payload = { conversation_id: activeConversationId, type: 'text', message: text };
    var done = false;
    var finish = function(ok) {
        if (done) return;
        done = true;
        if (ok) input.val('');
        else sendViaRest(text);
    };

    if (socket && socket.connected) {
        socket.emit('send_message', payload, function(ack) {
            if (ack && ack.success && ack.message) {
                appendMessage(ack.message);
                scrollBottom();
                finish(true);
            } else {
                finish(false);
            }
        });
        setTimeout(function() { finish(false); }, 5000);
    } else {
        sendViaRest(text);
    }
}

function sendViaRest(text) {
    $.ajax({
        url: baseUrl + 'api/conversations/' + activeConversationId + '/messages',
        method: 'POST',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify({ type: 'text', message: text }),
        success: function(response) {
            if (response.success && response.data) {
                appendMessage(response.data);
                scrollBottom();
                $('#messageInput').val('');
            } else {
                showError(response.message || 'Failed to send');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to send');
        }
    });
}

// ---------- New conversation ----------

var chatUsers = [];

function loadChatUsers() {
    // Direktori publik untuk semua role (user/moderator hanya Live Chat).
    // Fallback ke api/admin/users bila ada instansi lama yang belum punya /api/users.
    function handleUsers(users) {
        chatUsers = (users || []).filter(function(u) {
            return parseInt(u.id, 10) !== adminUserId;
        });
    }
    $.ajax({
        url: baseUrl + 'api/users',
        method: 'GET',
        headers: getAuthHeaders(),
        data: { per_page: 500 },
        success: function(response) {
            if (response.success && response.data && Array.isArray(response.data.users)) {
                handleUsers(response.data.users);
            }
        },
        error: function(xhr) {
            if (xhr.status === 404) {
                $.ajax({
                    url: baseUrl + 'api/admin/users',
                    method: 'GET',
                    headers: getAuthHeaders(),
                    data: { per_page: 500 },
                    success: function(response) {
                        if (response.success && response.data && Array.isArray(response.data.users)) {
                            handleUsers(response.data.users);
                        }
                    }
                });
            }
        }
    });
}

function openNewPrivate() {
    var html = '<option value="">Select user...</option>';
    chatUsers.forEach(function(u) {
        html += '<option value="' + u.id + '">' + escapeHtml(u.name) + ' (@' + escapeHtml(u.username || '-') + ')</option>';
    });
    $('#privateUserSelect').html(html);
    new bootstrap.Modal(document.getElementById('newPrivateModal')).show();
}

function openNewGroup() {
    $('#groupName').val('');
    var html = '';
    chatUsers.forEach(function(u) {
        var inputId = 'gm_' + u.id;
        html += '<label class="form-check">' +
            '<input class="form-check-input group-member" type="checkbox" value="' + u.id + '" id="' + inputId + '">' +
            '<span class="form-check-label">' + escapeHtml(u.name) + ' <span class="text-secondary">@' + escapeHtml(u.username || '-') + '</span></span>' +
        '</label>';
    });
    $('#groupMemberList').html(html || '<div class="text-secondary small">No users available</div>');
    new bootstrap.Modal(document.getElementById('newGroupModal')).show();
}

function createPrivateChat() {
    var userId = $('#privateUserSelect').val();
    if (!userId) {
        showError('Please select a user');
        return;
    }
    $.ajax({
        url: baseUrl + 'api/conversations',
        method: 'POST',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify({ type: 'private', user_id: parseInt(userId, 10) }),
        statusCode: {
            // Penjagaan duplikat (khusus private): 200 = room lama dibuka,
            // 201 = room baru dibuat.
            200: function(response) {
                if (response.success && response.data) {
                    showSuccess('Opening existing chat');
                    window.location.href = baseUrl + 'chat/' + response.data.id;
                }
            },
            201: function(response) {
                if (response.success && response.data) {
                    showSuccess('New chat created');
                    window.location.href = baseUrl + 'chat/' + response.data.id;
                }
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to open chat');
        }
    });
}

function createGroupChat() {
    var name = ($('#groupName').val() || '').trim();
    var memberIds = $('.group-member:checked').map(function() {
        return parseInt($(this).val(), 10);
    }).get();

    if (name.length < 2) {
        showError('Group name min. 2 characters');
        return;
    }
    if (!memberIds.length) {
        showError('Select at least 1 member');
        return;
    }

    $.ajax({
        url: baseUrl + 'api/conversations',
        method: 'POST',
        headers: getAuthHeaders(),
        contentType: 'application/json',
        data: JSON.stringify({ type: 'group', name: name, member_ids: memberIds }),
        success: function(response) {
            if (response.success && response.data) {
                showSuccess('Group created');
                window.location.href = baseUrl + 'chat/' + response.data.id;
            } else {
                showError(response.message || 'Failed to create group');
            }
        },
        error: function(xhr) {
            showError((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to create group');
        }
    });
}

// ---------- Shared ----------

function formatTime(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var now = new Date();
    var sameDay = date.toDateString() === now.toDateString();
    if (sameDay) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
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

function showError(message) {
    toastr.error(message);
}

function showSuccess(message) {
    toastr.success(message);
}
</script>
<?= $this->endSection() ?>
