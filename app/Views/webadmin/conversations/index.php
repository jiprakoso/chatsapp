<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Conversations</h2>
                <div class="text-secondary mt-1">Manage conversations and messages</div>
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
                            <input type="text" id="tableSearch" class="form-control" placeholder="Search conversations...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterType" class="form-select">
                            <option value="">All Types</option>
                            <option value="private">Private</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="tableConversations">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Members</th>
                            <th>Last Message</th>
                            <th>Last Activity</th>
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

<!-- Conversation Detail Modal -->
<div class="modal modal-blur fade" id="conversationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convModalTitle">Conversation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="conversationDetailContent">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
var conversationsTable;

$(document).ready(function() {
    // Tunggu auth siap agar request pertama selalu pakai token valid
    window.AdminApp.ready.then(function() {
        initConversationsTable();

        $('#tableSearch').on('keyup', debounce(function() {
            conversationsTable.search(this.value).draw();
        }, 300));

        $('#filterType').on('change', function() {
            conversationsTable.column(2).search(this.value).draw();
        });
    });
});

function initConversationsTable() {
    conversationsTable = $('#tableConversations').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: baseUrl + 'api/admin/conversations',
            type: 'GET',
            headers: getAuthHeaders(),
            data: { per_page: 200 },
            dataSrc: function(json) {
                if (json.success && json.data && Array.isArray(json.data.data)) {
                    return json.data.data.map(function(item) {
                        var lastMsg = item.last_message_preview ?
                            '<div class="text-secondary text-truncate" style="max-width: 250px;">' + escapeHtml(item.last_message_preview) + '</div>' :
                            '<span class="text-secondary">No messages</span>';
                        var lastSender = item.last_sender_name ?
                            '<div class="text-secondary small">by ' + escapeHtml(item.last_sender_name) + '</div>' : '';

                        return [
                            item.id,
                            item.name ? '<span class="fw-semibold">' + escapeHtml(item.name) + '</span>' : '<span class="text-secondary">(Private)</span>',
                            '<span class="badge ' + (item.type === 'group' ? 'bg-blue-lt' : 'bg-green-lt') + '">' + escapeHtml(item.type) + '</span>',
                            '<span class="badge bg-azure-lt">' + item.member_count + ' members</span>',
                            lastMsg + lastSender,
                            formatDateTime(item.last_message_at),
                            '<div class="d-flex gap-1 justify-content-end">' +
                                '<button class="btn btn-icon btn-outline-primary btn-sm" onclick="viewConversation(' + item.id + ')" title="View">' +
                                    '<i class="ti ti-eye"></i>' +
                                '</button>' +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) window.location.href = baseUrl + 'login';
                showError('Failed to load conversations');
            }
        },
        columns: [
            { data: 0, className: 'fw-semibold' },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, className: 'text-end' }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"></div>',
            zeroRecords: 'No conversations found'
        }
    });
}

function viewConversation(id) {
    $.ajax({
        url: baseUrl + 'conversations/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(html) {
            $('#conversationDetailContent').html(html);
            new bootstrap.Modal(document.getElementById('conversationDetailModal')).show();
        }
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '<span class="text-secondary">Never</span>';
    var date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
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
</script>
<?= $this->endSection() ?>
