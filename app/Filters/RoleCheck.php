<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Auth;
use App\Models\UserModel;

class RoleCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('api_response'); 
        // If arguments are empty, no specific permission is required
        if (empty($arguments)) {
            return;
        }

        $requiredRight = $arguments[0];
        
        // Ensure user is authenticated (handled by JwtAuth, but double check)
        if (!isset($request->user) || !isset($request->user->id)) {
            return responseError(401, 'Please authenticate');
        }

        // Fetch full user to get role
        $userModel = new UserModel();
        $user = $userModel->find($request->user->id);

        if (!$user) {
            return responseError(401, 'User not found');
        }

        // Attach full user object to request for controller use
        $request->user = $user;

        $config = new Auth();
        $userRights = $config->roleRights[$user->role] ?? [];

        // Check if user has the required right
        if (!in_array($requiredRight, $userRights)) {
            // Allow if user is managing their own data (optional logic, based on common patterns)
            // For strict role checking:
            return responseError(403, 'Forbidden');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}