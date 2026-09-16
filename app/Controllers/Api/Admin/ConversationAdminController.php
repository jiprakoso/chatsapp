<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\Admin\AdminBaseController;
use App\Models\ConversationMemberModel;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class ConversationAdminController extends AdminBaseController
{
    public function index()
    {
        $convModel = new ConversationModel();
        $memberModel = new ConversationMemberModel();
        $msgModel = new MessageModel();
        $userModel = new UserModel();
        
        // Build query with filters
        $builder = $convModel->select('
            conversations.*,
            (SELECT COUNT(*) FROM conversation_members WHERE conversation_id = conversations.id) as member_count,
            (SELECT u.name FROM users u 
             JOIN messages m ON m.sender_id = u.id 
             WHERE m.conversation_id = conversations.id 
             ORDER BY m.created_at DESC LIMIT 1) as last_sender_name
        ');
        
        // Search filter
        $search = $this->request->getGet('search') ?? '';
        if ($search) {
            $builder->groupStart()
                ->like('conversations.name', $search)
                ->orLike('conversations.id', $search)
                ->groupEnd();
        }
        
        // Type filter
        $type = $this->request->getGet('type') ?? '';
        if ($type && in_array($type, ['private', 'group'])) {
            $builder->where('conversations.type', $type);
        }
        
        // Pagination
        $page = (int)($this->request->getGet('page') ?? 1);
        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        $offset = ($page - 1) * $perPage;
        
        $total = $builder->countAllResults(false);
        $conversations = $builder->orderBy('conversations.last_message_at', 'DESC')
            ->limit($perPage, $offset)
            ->findAll();
        
        // Untuk private: admin ke atas yang bukan member -> "UserA - UserB",
        // yang member -> lawan bicara. Group tetap pakai name.
        $currentUserId = $this->currentUserId();
        $data = [];
        foreach ($conversations as $conv) {
            $lastMsg = null;
            if ($conv['last_message_id']) {
                $lastMsg = $msgModel->find($conv['last_message_id']);
            }

            $displayName = $conv['name'];
            if ($conv['type'] === 'private') {
                $memberIds = $memberModel->activeMemberUserIds((int) $conv['id']);
                if ($memberIds !== []) {
                    $members = $userModel->select('id, name')->whereIn('id', $memberIds)->findAll();
                    $map = array_column($members, 'name', 'id');
                    $normIds = array_map('intval', $memberIds);
                    $isMember = in_array((int) $currentUserId, $normIds, true);
                    if ($isMember) {
                        $otherIds = array_values(array_filter($memberIds, static fn($id) => (int) $id !== $currentUserId));
                        $displayName = $otherIds !== [] ? ($map[$otherIds[0]] ?? '(Private)') : ($map[$currentUserId] ?? '(Private)');
                    } else {
                        // admin ke atas bukan member -> "UserA - UserB"
                        $names = array_values($map);
                        $displayName = $names !== [] ? implode(' - ', $names) : '(Private)';
                    }
                } else {
                    $displayName = '(Private)';
                }
            }

            $data[] = [
                'id' => (int)$conv['id'],
                'name' => $conv['name'],
                'display_name' => $displayName,
                'type' => $conv['type'],
                'member_count' => (int)$conv['member_count'],
                'last_message_preview' => $lastMsg ? ($lastMsg['content'] ?? '') : null,
                'last_message_at' => $conv['last_message_at'],
                'last_message_id' => $conv['last_message_id'] ? (int) $conv['last_message_id'] : null,
                'last_sender_id' => $conv['last_sender_id'] ? (int) $conv['last_sender_id'] : null,
                'last_sender_name' => $conv['last_sender_name'],
            ];
        }
        
        return $this->success([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ]
        ]);
    }
    
    public function show($id = null)
    {
        $convModel = new ConversationModel();
        $conversation = $convModel->find($id);
        
        if (!$conversation) {
            return $this->error('Conversation not found', 404);
        }
        
        // Get members with user info
        $memberModel = new ConversationMemberModel();
        $members = $memberModel->where('conversation_id', $id)->findAll();
        
        $userModel = new UserModel();
        foreach ($members as &$member) {
            $user = $userModel->find($member['user_id']);
            $member['user'] = $user ? [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
            ] : null;
        }
        
        // Get recent messages
        $msgModel = new MessageModel();
        $messages = $msgModel->select('messages.*, users.name as sender_name, users.username as sender_username')
            ->join('users', 'users.id = messages.sender_id')
            ->where('messages.conversation_id', $id)
            ->orderBy('messages.id', 'DESC')
            ->limit(50)
            ->findAll();
        
        return $this->success([
            'conversation' => $conversation,
            'members' => $members,
            'messages' => array_reverse($messages)
        ]);
    }
}