<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResultSubmission extends Migration
{
    public function up()
    {
        $this->forge->addField('result_submission_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_result_request_id INT NOT NULL');
        $this->forge->addForeignKey('fk_result_request_id', 'tbl_result_request', 'result_request_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('type ENUM ("file", "image") NOT NULL');
        $this->forge->addField('data BLOB NOT NULL');

        $this->forge->addField('created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->forge->createTable('tbl_result_submission');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_result_submission');
    }
}
