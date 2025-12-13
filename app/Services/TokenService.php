<?php

namespace App\Services;

use App\Models\TokenModel;
use App\Entities\Token;
use App\Entities\User;
use Config\Auth;
use Firebase\JWT\JWT;
use CodeIgniter\I18n\Time;
use Exception;

class TokenService
{
    protected $tokenModel;
    protected $config;

    public function __construct()
    {
        $this->tokenModel = new TokenModel();
        $this->config = new Auth();
    }

    /**
     * Generate token string (JWT)
     */
    public function generateToken(int $userId, Time $expires, string $type): string
    {
        $payload = [
            'sub'  => $userId,
            'iat'  => Time::now()->getTimestamp(),
            'exp'  => $expires->getTimestamp(),
            'type' => $type,
        ];

        return JWT::encode($payload, $this->config->jwtSecret, 'HS256');
    }

    /**
     * Save a token to the database
     */
    public function saveToken(string $token, int $userId, Time $expires, string $type, bool $blacklisted = false): Token
    {
        $tokenEntity = new Token();
        $tokenEntity->token = $token;
        $tokenEntity->user_id = $userId;
        $tokenEntity->expires = $expires; // CI4 handles Time object to DB format
        $tokenEntity->type = $type;
        $tokenEntity->blacklisted = $blacklisted;

        $this->tokenModel->save($tokenEntity);
        return $tokenEntity;
    }

    /**
     * Verify token and return token entity (or throw error)
     */
    public function verifyToken(string $token, string $type): Token
    {
        try {
            // 1. Verify Signature
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($this->config->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            throw new Exception('Token not found'); // Generalized error for security
        }

        // 2. Verify Database existence (for Refresh/Reset/Verify types)
        $tokenDoc = $this->tokenModel->where([
            'token'       => $token,
            'type'        => $type,
            'user_id'     => $decoded->sub,
            'blacklisted' => false
        ])->first();

        if (!$tokenDoc) {
            throw new Exception('Token not found');
        }

        return $tokenDoc;
    }

    /**
     * Generate Auth Tokens (Access & Refresh)
     */
    public function generateAuthTokens(User $user): array
    {
        $accessTokenExpires = Time::now()->addMinutes($this->config->jwtAccessExpirationMinutes);
        $accessToken = $this->generateToken($user->id, $accessTokenExpires, $this->config->tokenTypes['ACCESS']);

        $refreshTokenExpires = Time::now()->addDays($this->config->jwtRefreshExpirationDays);
        $refreshToken = $this->generateToken($user->id, $refreshTokenExpires, $this->config->tokenTypes['REFRESH']);
        
        // Save refresh token to DB
        $this->saveToken($refreshToken, $user->id, $refreshTokenExpires, $this->config->tokenTypes['REFRESH']);

        return [
            'access' => [
                'token'   => $accessToken,
                'expires' => $accessTokenExpires->toDateTimeString(),
            ],
            'refresh' => [
                'token'   => $refreshToken,
                'expires' => $refreshTokenExpires->toDateTimeString(),
            ],
        ];
    }

    public function generateResetPasswordToken(string $email): string
    {
        $userService = new UserService();
        $user = $userService->getUserByEmail($email);
        
        if (!$user) {
            throw new Exception('No users found with this email', 404);
        }

        $expires = Time::now()->addMinutes($this->config->jwtResetPasswordExpirationMinutes);
        $resetToken = $this->generateToken($user->id, $expires, $this->config->tokenTypes['RESET_PASSWORD']);
        
        $this->saveToken($resetToken, $user->id, $expires, $this->config->tokenTypes['RESET_PASSWORD']);

        return $resetToken;
    }

    public function generateVerifyEmailToken(User $user): string
    {
        $expires = Time::now()->addMinutes($this->config->jwtVerifyEmailExpirationMinutes);
        $verifyToken = $this->generateToken($user->id, $expires, $this->config->tokenTypes['VERIFY_EMAIL']);

        $this->saveToken($verifyToken, $user->id, $expires, $this->config->tokenTypes['VERIFY_EMAIL']);

        return $verifyToken;
    }
}