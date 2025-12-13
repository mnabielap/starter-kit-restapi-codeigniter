<?php

namespace App\Services;

use App\Models\UserModel;
use App\Entities\User;
use Exception;

class UserService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function createUser(array $userBody): User
    {
        if ($this->userModel->isEmailTaken($userBody['email'])) {
            throw new Exception('Email already taken', 400);
        }

        $user = new User($userBody);
        
        // The Entity handles password hashing automatically via setPassword
        if (!$this->userModel->save($user)) {
            // Collect validation errors from model
            $errors = implode(', ', $this->userModel->errors());
            throw new Exception($errors, 400);
        }

        // Return the fresh user object with ID
        return $this->userModel->find($this->userModel->getInsertID());
    }

    public function getUserById($id): ?User
    {
        return $this->userModel->find($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->userModel->where('email', $email)->first();
    }

    public function updateUserById($userId, array $updateBody): User
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if (isset($updateBody['email']) && $this->userModel->isEmailTaken($updateBody['email'], $userId)) {
            throw new Exception('Email already taken', 400);
        }

        $user->fill($updateBody);
        
        if ($user->hasChanged()) {
            if (!$this->userModel->save($user)) {
                 $errors = implode(', ', $this->userModel->errors());
                 throw new Exception($errors, 400);
            }
        }

        return $user;
    }

    public function deleteUserById($userId): void
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new Exception('User not found', 404);
        }
        $this->userModel->delete($userId);
    }

    /**
     * Query users with pagination
     * @param array $params (sortBy, limit, page, name, role)
     */
    public function queryUsers(array $params): array
    {
        $builder = $this->userModel->builder();

        // Filtering
        if (isset($params['name']) && $params['name']) {
            $builder->like('name', $params['name']);
        }
        if (isset($params['role']) && $params['role']) {
            $builder->where('role', $params['role']);
        }

        // Sorting
        $sort = $params['sortBy'] ?? 'created_at:desc';
        // Format: field:desc or field (default asc)
        $sortParts = explode(':', $sort);
        $sortField = $sortParts[0];
        $sortDirection = $sortParts[1] ?? 'asc';

        $sortMapping = [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'id'        => 'id',
            'name'      => 'name',
            'email'     => 'email',
            'role'      => 'role',
        ];

        if (array_key_exists($sortField, $sortMapping)) {
            $sortField = $sortMapping[$sortField];
        }
        
        $builder->orderBy($sortField, $sortDirection);

        // Pagination
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 10);
        
        // Clone builder for counting
        $countBuilder = clone $builder;
        $totalResults = $countBuilder->countAllResults();
        
        // Execute query
        $results = $this->userModel->paginate($limit, 'default', $page);
        
        $totalPages = ceil($totalResults / $limit);

        return [
            'results'      => $results,
            'page'         => $page,
            'limit'        => $limit,
            'totalPages'   => $totalPages,
            'totalResults' => $totalResults,
        ];
    }
}