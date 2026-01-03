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
        // Enforce role check if passed manually
        if (isset($userBody['role']) && !in_array($userBody['role'], ['user', 'admin'])) {
            throw new Exception('The role field must be one of: user,admin.', 400);
        }

        if ($this->userModel->isEmailTaken($userBody['email'])) {
            throw new Exception('Email already taken', 400);
        }

        $user = new User($userBody);
        
        // The Entity handles password hashing automatically via setPassword
        if (!$this->userModel->save($user)) {
            $errors = implode(', ', $this->userModel->errors());
            throw new Exception($errors, 400);
        }

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
     * Query users with pagination, searching, scopes, and sorting.
     * 
     * @param array $params (search, scope, role, sortBy, limit, page)
     */
    public function queryUsers(array $params): array
    {
        $builder = $this->userModel->builder();

        // --- 1. Filter by Role ---
        if (isset($params['role']) && !empty($params['role'])) {
            $builder->where('role', $params['role']);
        }

        // --- 2. Search & Scopes ---
        $search = $params['search'] ?? null;
        $scope  = $params['scope'] ?? 'all';

        if ($search) {
            // Apply grouping to ensure AND (Search OR Search) logic
            $builder->groupStart();
            
            if ($scope === 'all') {
                $builder->like('name', $search)
                        ->orLike('email', $search)
                        ->orWhere('id', $search);
            } elseif ($scope === 'name') {
                $builder->like('name', $search);
            } elseif ($scope === 'email') {
                $builder->like('email', $search);
            } elseif ($scope === 'id') {
                $builder->where('id', $search);
            }

            $builder->groupEnd();
        }

        // --- 3. Sorting ---
        // Expected format: "field:direction" (e.g., "created_at:desc")
        $sortParam = $params['sortBy'] ?? 'created_at:desc';
        $parts = explode(':', $sortParam);
        
        $sortField = $parts[0];
        $sortDirection = $parts[1] ?? 'asc';

        // Allow-list for sorting columns
        $allowedSorts = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
        
        // Map camelCase to snake_case
        $fieldMap = [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        if (array_key_exists($sortField, $fieldMap)) {
            $sortField = $fieldMap[$sortField];
        }

        if (in_array($sortField, $allowedSorts)) {
            // Standard sorting (admin < user)
            $builder->orderBy($sortField, $sortDirection);
        } else {
            // Default fallback
            $builder->orderBy('created_at', 'desc');
        }

        // --- 4. Pagination ---
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 10);
        $offset = ($page - 1) * $limit;

        // Clone builder to get total count
        $countBuilder = clone $builder;
        $totalResults = $countBuilder->countAllResults();

        // Apply pagination
        $builder->limit($limit, $offset);
        
        // Get Result Objects (Entities)
        $results = $builder->get()->getResult(User::class);
        
        $totalPages = $limit > 0 ? ceil($totalResults / $limit) : 1;

        return [
            'results'      => $results,
            'page'         => $page,
            'limit'        => $limit,
            'totalPages'   => $totalPages,
            'totalResults' => $totalResults,
        ];
    }
}