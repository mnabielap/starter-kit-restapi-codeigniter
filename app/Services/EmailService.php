<?php

namespace App\Services;

class EmailService
{
    protected $email;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->fromEmail = getenv('SMTP_FROM_EMAIL');
        $this->fromName = getenv('SMTP_FROM_NAME') ?: 'App Support';
    }

    public function sendEmail(string $to, string $subject, string $text): void
    {
        // Don't send real emails in test environment
        if (getenv('CI_ENVIRONMENT') === 'testing') {
            return;
        }

        $this->email->setFrom($this->fromEmail, $this->fromName);
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMessage($text);

        if (!$this->email->send()) {
            // Log error in production, but don't break the app flow
            log_message('error', $this->email->printDebugger(['headers']));
        }
    }

    public function sendResetPasswordEmail(string $to, string $token): void
    {
        $subject = 'Reset password';
        $resetPasswordUrl = getenv('app.baseURL') . "/reset-password?token={$token}";
        $text = "Dear user,\n\nTo reset your password, click on this link: {$resetPasswordUrl}\n\nIf you did not request any password resets, then ignore this email.";
        
        $this->sendEmail($to, $subject, $text);
    }

    public function sendVerificationEmail(string $to, string $token): void
    {
        $subject = 'Email Verification';
        $verificationEmailUrl = getenv('app.baseURL') . "/verify-email?token={$token}";
        $text = "Dear user,\n\nTo verify your email, click on this link: {$verificationEmailUrl}\n\nIf you did not create an account, then ignore this email.";
        
        $this->sendEmail($to, $subject, $text);
    }
}