<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\Admin\AdminBaseController;
use App\Models\ConversationMemberModel;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\RoleModel;
use App\Models\UserDeviceModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class StatsController extends AdminBaseController
{
    public function index()
    {
        // Catatan: tiap hitungan memakai instance model baru supaya
        // kondisi WHERE tidak menumpuk antar query (countAllResults(false)
        // tidak me-reset builder pada instance yang sama).
        $data = [
            'total_users'         => (new UserModel())->countAllResults(),
            'total_conversations' => (new ConversationModel())->countAllResults(),
            'total_messages'      => (new MessageModel())->countAllResults(),
            'total_devices'       => (new UserDeviceModel())->where('is_active', 1)->countAllResults(),
            'banned_users'        => (new UserModel())->where('is_banned', 1)->countAllResults(),
            'active_users_24h'    => (new UserModel())->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults(),
        ];
        
        return $this->success($data);
    }
    
    public function activity()
    {
        $userModel = new UserModel();
        $convModel = new ConversationModel();
        $msgModel = new MessageModel();
        
        $activities = [];
        
        // Recent users
        $recentUsers = $userModel->orderBy('created_at', 'DESC')->limit(10)->findAll();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'time' => $user['created_at'],
                'event' => 'User Registered',
                'type' => 'user',
                'user' => $user['name'] . ' (@' . $user['username'] . ')',
                'details' => 'Email: ' . $user['email']
            ];
        }
        
        // Recent conversations
        $recentConvs = $convModel->orderBy('created_at', 'DESC')->limit(10)->findAll();
        foreach ($recentConvs as $conv) {
            $activities[] = [
                'time' => $conv['created_at'],
                'event' => 'Conversation Created',
                'type' => 'conversation',
                'user' => $conv['name'] ?? 'Private',
                'details' => 'Type: ' . $conv['type'] . ', ID: #' . $conv['id']
            ];
        }
        
        // Recent messages
        $recentMessages = $msgModel->select('messages.*, users.name as sender_name')
            ->join('users', 'users.id = messages.sender_id')
            ->where('messages.deleted_at IS NULL')
            ->orderBy('messages.created_at', 'DESC')
            ->limit(10)
            ->findAll();
        foreach ($recentMessages as $msg) {
            $activities[] = [
                'time' => $msg['created_at'],
                'event' => 'Message Sent',
                'type' => 'message',
                'user' => $msg['sender_name'],
                'details' => 'Conv: #' . $msg['conversation_id'] . ' | ' . substr($msg['content'] ?? '', 0, 50)
            ];
        }
        
        // Sort by time descending
        usort($activities, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
        $activities = array_slice($activities, 0, 20);
        
        // Format time for display
        foreach ($activities as &$activity) {
            $activity['time'] = date('M d, H:i', strtotime($activity['time']));
        }
        
        return $this->success($activities);
    }
}