<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RateLimiter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('api_response');
        $throttler = Services::throttler();

        // Allow 20 requests per 15 minutes (900 seconds) per IP address
        // Matches the Regular configuration: windowMs: 15 * 60 * 1000, max: 20
        if ($throttler->check(md5($request->getIPAddress()), 20, 900) === false) {
            return responseError(429, 'Too many requests, please try again later.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}