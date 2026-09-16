<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;
use App\Models\ConversationMemberModel;
use App\Models\ConversationModel;

class ChatController extends BaseController
{
    public function index()
    {
        return view('webadmin/chat/index', [
            'activeConversationId' => null,
            'adminUserId'          => (int) session()->get('admin_user_id'),
            'isViewOnly'           => false,
        ]);
    }

    public function room($id = null)
    {
        $conversationId = (int) $id;
        $conversation   = (new ConversationModel())->find($conversationId);

        if ($conversation === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Conversation not found');
        }

        $adminUserId = (int) session()->get('admin_user_id');
        $memberModel = new ConversationMemberModel();
        $isMember    = $memberModel->isActiveMember($conversationId, $adminUserId);
        $rank        = \App\Services\RoleHierarchy::highestRankForUser($adminUserId);
        $isSuperAdmin = $rank >= 100;
        $isAdmin      = $rank >= 80 && $rank < 100;

        // Aturan baru (sesuai request):
        // - user & moderator (rank <80): hanya conversation yang sudah jadi member yang muncul.
        //   Klo bukan member -> tidak muncul (list sudah filtered) dan akses room 403 baik private maupun group.
        // - admin (rank 80): only view jika bukan member tidak bisa join room.
        // - super_admin (rank 100): bisa join jika group, jika private only view.
        $isViewOnly = false;

        if (! $isMember) {
            if ($conversation['type'] === 'private') {
                if ($rank < 80) {
                    // user/moderator
                    $this->response->setStatusCode(403);
                    return view('webadmin/chat/forbidden', ['conversationId' => $conversationId]);
                }
                // admin & super_admin private -> only view (tanpa join)
                $isViewOnly = true;
            } else { // group
                if ($rank < 80) {
                    $this->response->setStatusCode(403);
                    return view('webadmin/chat/forbidden', ['conversationId' => $conversationId]);
                }
                if ($isAdmin) {
                    // admin group non-member -> only view
                    $isViewOnly = true;
                } elseif ($isSuperAdmin) {
                    // super_admin group -> auto-join
                    $memberModel->addMember($conversationId, $adminUserId, 'admin');
                } else {
                    // fallback (custom role) -> view only
                    $isViewOnly = true;
                }
            }
        }

        return view('webadmin/chat/index', [
            'activeConversationId' => $conversationId,
            'adminUserId'          => $adminUserId,
            'isViewOnly'           => $isViewOnly,
        ]);
    }
}
