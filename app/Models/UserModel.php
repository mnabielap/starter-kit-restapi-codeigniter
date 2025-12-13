<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\User;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = User::class;
    protected $useSoftDeletes   = false; // Set true if you want soft deletes
    protected $allowedFields    = ['name', 'email', 'password', 'role', 'is_email_verified'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'name'     => 'required|min_length[3]',
        'password' => 'required|min_length[8]',
        'role'     => 'in_list[user,admin]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Email already taken',
        ],
    ];

    /**
     * Check if email is taken (excluding a specific user ID)
     */
    public function isEmailTaken(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->where('email', $email);
        
        if ($excludeUserId) {
            $query->where('id !=', $excludeUserId);
        }

        return !is_null($query->first());
    }
}