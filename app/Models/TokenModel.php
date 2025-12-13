<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Token;

class TokenModel extends Model
{
    protected $table            = 'tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Token::class;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['token', 'user_id', 'type', 'expires', 'blacklisted'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}