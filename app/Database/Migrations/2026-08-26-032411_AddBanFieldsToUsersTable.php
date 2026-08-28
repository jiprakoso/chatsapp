<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBanFieldsToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_banned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'photo',
            ],
            'banned_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'is_banned',
            ],
            'banned_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'banned_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['is_banned', 'banned_at', 'banned_reason']);
    }
}
