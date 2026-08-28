<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'sender_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['text', 'image', 'video', 'audio', 'file', 'location', 'contact'],
                'default'    => 'text',
            ],
            'reply_message_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'edited_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['conversation_id', 'id'], false, false, 'idx_conversation_id_id');
        $this->forge->addKey(['conversation_id', 'created_at'], false, false, 'idx_conversation_created');
        $this->forge->addKey('sender_id');
        $this->forge->addKey('reply_message_id');

        $this->forge->addForeignKey('conversation_id', 'conversations', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('sender_id', 'users', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('reply_message_id', 'messages', 'id', '', 'SET NULL');

        $this->forge->createTable('messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('messages', true);
    }
}
