<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Entities\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        // Check if admin already exists
        if ($userModel->where('email', 'admin@example.com')->first()) {
            return;
        }

        $user = new User([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => 'password123', // Will be hashed by Entity
            'role'     => 'admin',
            'is_email_verified' => true
        ]);

        $userModel->save($user);
    }
}