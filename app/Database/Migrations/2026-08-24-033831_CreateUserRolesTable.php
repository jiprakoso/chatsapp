<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRolesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'role_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'assigned_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey(['user_id', 'role_id']);
        $this->forge->addKey('role_id');

        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');

        $this->forge->createTable('user_roles', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_roles', true);
    }
}
