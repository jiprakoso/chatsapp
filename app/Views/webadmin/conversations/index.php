<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row">
    <div class="flex-lg-row-fluid">
        <div class="d-flex flex-column">
            <div class="d-flex flex-stack mb-10">
                <div>
                    <h1 class="text-gray-900 fw-bolder fs-2hx">Conversations</h1>
                    <div class="fw-semibold fs-6 text-muted mt-2">Manage conversations and messages</div>
                </div>
            </div>
            
            <div class="card card-flush">
                <div class="card-header border-0 pt-5">
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Search:</label>
                            <input type="text" id="tableSearch" class="form-control form-control-solid w-250px" placeholder="Search conversations...">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold fs-6 mb-0">Type:</label>
                            <select id="filterType" class="form-select form-select-solid w-180px">
                                <option value="">All Types</option>
                                <option value="private">Private</option>
                                <option value="group">Group</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-row-gray-100 align-middle gs-0 gy-4" id="tableConversations">
                            <thead>
                                <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-80px">ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Members</th>
                                    <th>Last Message</th>
                                    <th class="min-w-150px">Last Activity</th>
                                    <th class="text-end min-w-150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conversation Detail Modal -->
<div class="modal fade" id="conversationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convModalTitle">Conversation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    initConversationsTable();
    
    $('#tableSearch').on('keyup', debounce(function() {
        conversationsTable.search(this.value).draw();
    }, 300));
    
    $('#filterType').on('change', function() {
        conversationsTable.column(2).search(this.value).draw();
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
                            '<div class="text-muted fw-semibold text-truncate" style="max-width: 250px;">' + escapeHtml(item.last_message_preview) + '</div>' :
                            '<span class="text-muted">No messages</span>';
                        var lastSender = item.last_sender_name ? 
                            '<div class="text-muted fs-7">by ' + escapeHtml(item.last_sender_name) + '</div>' : '';
                        
                        return [
                            item.id,
                            item.name ? '<span class="fw-semibold">' + escapeHtml(item.name) + '</span>' : '<span class="text-muted">(Private)</span>',
                            '<span class="badge ' + (item.type === 'group' ? 'badge-light-primary' : 'badge-light-success') + '">' + item.type + '</span>',
                            '<span class="badge badge-light-secondary">' + item.member_count + ' members</span>',
                            lastMsg + lastSender,
                            formatDateTime(item.last_message_at),
                            '<div class="d-flex gap-2 justify-content-end">' +
                                '<button class="btn btn-icon btn-light-primary btn-sm" onclick="viewConversation(' + item.id + ')" title="View">' +
                                    '<i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>' +
                                '</button>' +
                            '</div>'
                        ];
                    });
                }
                return [];
            },
            error: function(xhr) {
                if (xhr.status === 401) window.location.href = baseUrl + 'admin/login';
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
            processing: '<div class="spinner-border spinner-border-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'No conversations found'
        }
    });
}

function viewConversation(id) {
    $.ajax({
        url: baseUrl + 'admin/conversations/' + id,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(html) {
            $('#conversationDetailContent').html(html);
            new bootstrap.Modal(document.getElementById('conversationDetailModal')).show();
        }
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '<span class="text-muted">Never</span>';
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