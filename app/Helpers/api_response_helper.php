<?php

use CodeIgniter\HTTP\ResponseInterface;

if (!function_exists('responseSuccess')) {
    /**
     * Send a formatted success JSON response.
     * 
     * @param mixed $data The payload to return
     * @param int $statusCode HTTP Status code (default 200)
     * @param string $message Optional message
     */
    function responseSuccess($data = null, int $statusCode = 200, string $message = 'Success'): ResponseInterface
    {
        $response = service('response');
        
        $body = [
            'code'    => $statusCode,
            'message' => $message,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        return $response->setJSON($body)->setStatusCode($statusCode);
    }
}

if (!function_exists('responseError')) {
    /**
     * Send a formatted error JSON response.
     * 
     * @param int $statusCode HTTP Status code
     * @param string $message Error message
     * @param mixed $errors Detailed validation errors or stack trace
     */
    function responseError(int $statusCode, string $message, $errors = null): ResponseInterface
    {
        $response = service('response');

        $body = [
            'code'    => $statusCode,
            'message' => $message,
        ];

        if ($errors !== null) {
            // In production, you might want to hide stack traces for 500 errors
            if (ENVIRONMENT === 'production' && $statusCode === 500) {
                $body['errors'] = 'Internal Server Error';
            } else {
                $body['errors'] = $errors;
            }
        }

        return $response->setJSON($body)->setStatusCode($statusCode);
    }
}