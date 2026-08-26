<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'permission_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
        ]);

        $this->forge->addPrimaryKey(['role_id', 'permission_id']);
        $this->forge->addKey('permission_id');

        $this->forge->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', '', 'CASCADE');

        $this->forge->createTable('role_permissions', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
    }
}
