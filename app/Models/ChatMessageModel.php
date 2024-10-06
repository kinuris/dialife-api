<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table            = 'tbl_chat_message';
    protected $primaryKey       = 'chat_message_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'chat_message_id',
        'fk_chat_connection_id',
        'sender_type',
        'sender_id',
        'content',
        'created_at',
    ];
}
