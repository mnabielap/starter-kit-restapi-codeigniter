<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class User extends Entity
{
    protected $datamap = [
        // Maps class properties to database columns if names differ
        'isEmailVerified' => 'is_email_verified',
    ];

    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'is_email_verified' => 'boolean',
    ];

    /**
     * Automatically hash the password when setting it.
     */
    public function setPassword(string $password)
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }

    /**
     * Verify if the provided password matches the hash.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password']);
    }

    /**
     * Remove sensitive data for JSON serialization.
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        $data = parent::toArray($onlyChanged, $cast, $recursive);
        
        // Remove sensitive fields
        unset($data['password']);
        
        // Standardize ID
        if (isset($data['id'])) {
            $data['id'] = (string) $data['id'];
        }

        foreach (['created_at', 'updated_at', 'deleted_at'] as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof Time) {
                $data[$dateField] = $data[$dateField]->format('c'); // 2023-01-01T12:00:00+00:00
            }
        }

        return $data;
    }
}