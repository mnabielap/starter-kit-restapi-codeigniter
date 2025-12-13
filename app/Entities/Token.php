<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Token extends Entity
{
    protected $datamap = [
        'userId' => 'user_id',
    ];

    protected $dates   = ['expires', 'created_at', 'updated_at'];
    protected $casts   = [
        'blacklisted' => 'boolean',
        'user_id'     => 'integer',
    ];
}