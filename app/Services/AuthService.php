<?php

namespace App\Services;

use App\Models\TokenModel;
use Config\Auth;
use Exception;

class AuthService
{
    protected $userService;
    protected $tokenService;
    protected $tokenModel;
    protected $config;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->tokenService = new TokenService();
        $this->tokenModel = new TokenModel();
        $this->config = new Auth();
    }

    public function loginUserWithEmailAndPassword(string $email, string $password)
    {
        $user = $this->userService->getUserByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            throw new Exception('Incorrect email or password', 401);
        }
        
        return $user;
    }

    public function logout(string $refreshToken): void
    {
        $refreshTokenDoc = $this->tokenModel->where([
            'token' => $refreshToken,
            'type'  => $this->config->tokenTypes['REFRESH'],
            'blacklisted' => false
        ])->first();

        if (!$refreshTokenDoc) {
            throw new Exception('Not found', 404);
        }

        $this->tokenModel->delete($refreshTokenDoc->id);
    }

    public function refreshAuth(string $refreshToken): array
    {
        try {
            $refreshTokenDoc = $this->tokenService->verifyToken($refreshToken, $this->config->tokenTypes['REFRESH']);
            $user = $this->userService->getUserById($refreshTokenDoc->user_id);
            
            if (!$user) {
                throw new Exception();
            }

            // Remove old refresh token (Rotation strategy)
            $this->tokenModel->delete($refreshTokenDoc->id);
            
            return $this->tokenService->generateAuthTokens($user);
            
        } catch (Exception $e) {
            throw new Exception('Please authenticate', 401);
        }
    }

    public function resetPassword(string $resetPasswordToken, string $newPassword): void
    {
        try {
            $resetTokenDoc = $this->tokenService->verifyToken($resetPasswordToken, $this->config->tokenTypes['RESET_PASSWORD']);
            $user = $this->userService->getUserById($resetTokenDoc->user_id);
            
            if (!$user) {
                throw new Exception();
            }

            $this->userService->updateUserById($user->id, ['password' => $newPassword]);
            
            // Invalidate all reset tokens for this user
            $this->tokenModel->where([
                'user_id' => $user->id, 
                'type' => $this->config->tokenTypes['RESET_PASSWORD']
            ])->delete();

        } catch (Exception $e) {
            throw new Exception('Password reset failed', 401);
        }
    }

    public function verifyEmail(string $verifyEmailToken): void
    {
        try {
            $verifyTokenDoc = $this->tokenService->verifyToken($verifyEmailToken, $this->config->tokenTypes['VERIFY_EMAIL']);
            $user = $this->userService->getUserById($verifyTokenDoc->user_id);

            if (!$user) {
                throw new Exception();
            }

            $this->userService->updateUserById($user->id, ['is_email_verified' => true]);
            
            // Cleanup
            $this->tokenModel->where([
                'user_id' => $user->id, 
                'type' => $this->config->tokenTypes['VERIFY_EMAIL']
            ])->delete();

        } catch (Exception $e) {
            throw new Exception('Email verification failed', 401);
        }
    }
}