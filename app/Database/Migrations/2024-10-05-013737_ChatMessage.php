<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChatMessage extends Migration
{
    public function up()
    {
        $this->forge->addField('chat_message_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_chat_connection_id INT NOT NULL');
        $this->forge->addForeignKey('fk_chat_connection_id', 'tbl_chat_connection', 'chat_connection_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('sender_type ENUM("doctor", "patient") NOT NULL');
        $this->forge->addField('sender_id INT NOT NULL');

        $this->forge->addField('content TEXT NOT NULL');
        $this->forge->addField('created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->forge->createTable('tbl_chat_message');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_chat_message');
    }
}
