<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationMembersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'conversation_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['owner', 'admin', 'member'],
                'default'    => 'member',
            ],
            'joined_at' => [
                'type' => 'DATETIME',
            ],
            'left_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_muted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'last_read_message_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        $this->forge->addPrimaryKey(['conversation_id', 'user_id']);
        $this->forge->addKey('user_id');
        $this->forge->addKey(['user_id', 'conversation_id'], false, false, 'idx_user_conversation');

        $this->forge->addForeignKey('conversation_id', 'conversations', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');

        $this->forge->createTable('conversation_members', true);
    }

    public function down()
    {
        $this->forge->dropTable('conversation_members', true);
    }
}
