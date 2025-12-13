<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    public $jwtSecret;
    public $jwtAccessExpirationMinutes;
    public $jwtRefreshExpirationDays;
    public $jwtResetPasswordExpirationMinutes;
    public $jwtVerifyEmailExpirationMinutes;

    public $tokenTypes = [
        'ACCESS'          => 'access',
        'REFRESH'         => 'refresh',
        'RESET_PASSWORD'  => 'resetPassword',
        'VERIFY_EMAIL'    => 'verifyEmail',
    ];

    public $roles = ['user', 'admin'];

    public $roleRights = [
        'user'  => [],
        'admin' => ['getUsers', 'manageUsers'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'default_secret_key_change_me';
        $this->jwtAccessExpirationMinutes = (int) (getenv('JWT_ACCESS_EXPIRATION_MINUTES') ?: 30);
        $this->jwtRefreshExpirationDays = (int) (getenv('JWT_REFRESH_EXPIRATION_DAYS') ?: 30);
        $this->jwtResetPasswordExpirationMinutes = (int) (getenv('JWT_RESET_PASSWORD_EXPIRATION_MINUTES') ?: 10);
        $this->jwtVerifyEmailExpirationMinutes = (int) (getenv('JWT_VERIFY_EMAIL_EXPIRATION_MINUTES') ?: 10);
    }
}