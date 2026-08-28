<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessageAttachmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'message_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'file_size' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'width' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'height' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'duration' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('message_id');

        $this->forge->addForeignKey('message_id', 'messages', 'id', '', 'CASCADE');

        $this->forge->createTable('message_attachments', true);
    }

    public function down()
    {
        $this->forge->dropTable('message_attachments', true);
    }
}
