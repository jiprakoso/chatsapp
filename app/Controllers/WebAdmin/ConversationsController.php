<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class ConversationsController extends BaseController
{
    public function index()
    {
        return view('webadmin/conversations/index');
    }
    
    public function view($id = null)
    {
        $convModel = new \App\Models\ConversationModel();
        $conversation = $convModel->find($id);
        
        if (!$conversation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Conversation not found');
        }
        
        // Get members
        $memberModel = new \App\Models\ConversationMemberModel();
        $members = $memberModel->where('conversation_id', $id)->findAll();
        
        // Get user info for each member
        $userModel = new \App\Models\UserModel();
        foreach ($members as &$member) {
            $user = $userModel->find($member['user_id']);
            $member['user'] = $user;
        }
        
        // Get recent messages
        $msgModel = new \App\Models\MessageModel();
        $messages = $msgModel->where('conversation_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->findAll();
        
        // Get sender info for messages
        foreach ($messages as &$msg) {
            $sender = $userModel->find($msg['sender_id']);
            $msg['sender'] = $sender;
        }
        
        return view('webadmin/conversations/view', [
            'conversation' => $conversation,
            'members' => $members,
            'messages' => array_reverse($messages)
        ]);
    }
}