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

        // Private conversation: hanya anggota yang boleh masuk room.
        // Admin yang bukan anggota mendapat 403 (tidak auto-join).
        if ($conversation['type'] === 'private' && ! $isMember) {
            $this->response->setStatusCode(403);

            return view('webadmin/chat/forbidden', [
                'conversationId' => $conversationId,
            ]);
        }

        // Group: admin otomatis join sebagai member supaya bisa membaca &
        // membalas memakai aturan yang sama dengan user biasa (tercatat).
        if (! $isMember) {
            $memberModel->addMember($conversationId, $adminUserId, 'admin');
        }

        return view('webadmin/chat/index', [
            'activeConversationId' => $conversationId,
            'adminUserId'          => $adminUserId,
        ]);
    }
}
