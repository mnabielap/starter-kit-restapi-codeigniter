<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\AuthService;
use App\Services\TokenService;
use App\Services\UserService;
use App\Services\EmailService;
use Exception;

class Auth extends BaseController
{
    protected $authService;
    protected $tokenService;
    protected $userService;
    protected $emailService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->tokenService = new TokenService();
        $this->userService = new UserService();
        $this->emailService = new EmailService();
    }

    public function register()
    {
        $rules = [
            'name'     => 'required',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]|validate_password_strength', // Uses Custom Rule
        ];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $user = $this->userService->createUser($body);
            $tokens = $this->tokenService->generateAuthTokens($user);

            return responseSuccess([
                'user' => $user->toArray(),
                'tokens' => $tokens
            ], 201);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $user = $this->authService->loginUserWithEmailAndPassword($body['email'], $body['password']);
            $tokens = $this->tokenService->generateAuthTokens($user);

            return responseSuccess([
                'user' => $user->toArray(),
                'tokens' => $tokens
            ]);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function logout()
    {
        $rules = ['refreshToken' => 'required'];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $this->authService->logout($body['refreshToken']);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function refreshTokens()
    {
        $rules = ['refreshToken' => 'required'];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $tokens = $this->authService->refreshAuth($body['refreshToken']);
            return responseSuccess($tokens);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function forgotPassword()
    {
        $rules = ['email' => 'required|valid_email'];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $body = $this->request->getJSON(true);
            $resetToken = $this->tokenService->generateResetPasswordToken($body['email']);
            $this->emailService->sendResetPasswordEmail($body['email'], $resetToken);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            // Standard security practice: don't reveal if email doesn't exist, but code here returns 404 if service throws it
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function resetPassword()
    {
        $rules = [
            'token'    => 'required',
            'password' => 'required|min_length[8]|validate_password_strength',
        ];

        // Combine Query Params (token) and Body (password) for validation
        $data = array_merge($this->request->getVar(), $this->request->getJSON(true) ?? []);

        $this->validator = \Config\Services::validation();
        if (!$this->validator->setRules($rules)->run($data)) {
             return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $token = $this->request->getVar('token');
            $password = $this->request->getJSON(true)['password'];
            
            $this->authService->resetPassword($token, $password);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function sendVerificationEmail()
    {
        try {
            // User is injected into request by JwtAuth filter
            $user = $this->request->user; 
            
            // We need the full user entity, the filter might pass a generic object or partial
            // In our filter implementation, it passes an object with ID or full entity
            $fullUser = $this->userService->getUserById($user->id);

            $verifyToken = $this->tokenService->generateVerifyEmailToken($fullUser);
            $this->emailService->sendVerificationEmail($fullUser->email, $verifyToken);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function verifyEmail()
    {
        $rules = ['token' => 'required'];

        if (!$this->validate($rules)) {
            return responseError(400, 'Validation Error', $this->validator->getErrors());
        }

        try {
            $token = $this->request->getVar('token');
            $this->authService->verifyEmail($token);
            return responseSuccess(null, 204);
        } catch (Exception $e) {
            return responseError($e->getCode() ?: 500, $e->getMessage());
        }
    }
}