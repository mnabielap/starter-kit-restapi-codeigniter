<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\UserService;
use Exception;

class Users extends BaseController
{
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function create()
    {
        $rules = [
            'name'     => 'required',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]|validate_password_strength',
            'role'     => 'required|in_list[user,admin]',
        ];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $user = $this->userService->createUser($body);
            return responseSuccess($user->toArray(), 201);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function index()
    {
        $params = $this->request->getGet();
        
        try {
            $result = $this->userService->queryUsers($params);
            
            // Format results with entities
            $result['results'] = array_map(function($user) {
                return $user->toArray();
            }, $result['results']);

            return responseSuccess($result);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function show($userId = null)
    {
        try {
            $user = $this->userService->getUserById($userId);
            
            if (!$user) {
                return responseError(404, 'User not found');
            }

            // Authorization Check: User can see themselves, or Admin can see anyone
            // Note: 'getUsers' role allows seeing all. Standard users have empty permissions.
            $currentUser = $this->request->user;
            
            // If current user is NOT the requested user, AND they don't have permission to manage users
            // The Route filter handles "role:getUsers", but that's for the whole route. 
            // We refine logic here: Logged in users can fetch only their own info, or admins fetch others.
            if ($userId != $currentUser->id && $currentUser->role !== 'admin') {
                return responseError(403, 'Forbidden');
            }

            return responseSuccess($user->toArray());
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function update($userId = null)
    {
        $body = $this->request->getJSON(true);

        $allRules = [
            'email'    => 'valid_email|is_unique[users.email,id,'.$userId.']',
            'password' => 'min_length[8]|validate_password_strength',
            'name'     => 'min_length[1]',
            'role'     => 'in_list[user,admin]',
        ];

        $rules = array_intersect_key($allRules, $body);

        if (!empty($rules)) {
            $this->validator = \Config\Services::validation();
            
            if (!$this->validator->setRules($rules)->run($body)) {
                 return responseError(400, 'Validation Error', $this->validator->getErrors());
            }
        }

        try {
            $currentUser = $this->request->user;
            if ($userId != $currentUser->id && $currentUser->role !== 'admin') {
                 return responseError(403, 'Forbidden');
            }

            $user = $this->userService->updateUserById($userId, $body);
            return responseSuccess($user->toArray());
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function delete($userId = null)
    {
        try {
             // Authorization: Users delete themselves, Admins delete anyone
             $currentUser = $this->request->user;
             if ($userId != $currentUser->id && $currentUser->role !== 'admin') {
                  return responseError(403, 'Forbidden');
             }

            $this->userService->deleteUserById($userId);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }
}