<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessageReadsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'message_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey(['message_id', 'user_id']);
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey('message_id', 'messages', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');

        $this->forge->createTable('message_reads', true);
    }

    public function down()
    {
        $this->forge->dropTable('message_reads', true);
    }
}
