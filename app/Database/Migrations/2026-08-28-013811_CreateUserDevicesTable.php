<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDevicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'platform' => [
                'type'       => 'ENUM',
                'constraint' => ['android', 'ios', 'web'],
            ],
            'fcm_token' => [
                'type' => 'TEXT',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'last_seen_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');

        $this->forge->createTable('user_devices', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_devices', true);
    }
}
