<?php

use CodeIgniter\HTTP\ResponseInterface;

if (!function_exists('responseSuccess')) {
    /**
     * Send a formatted success JSON response.
     * 
     * @param mixed $data The payload to return
     * @param int $statusCode HTTP Status code (default 200)
     * @param string $message Optional message (Not used in raw JSON output)
     */
    function responseSuccess($data = null, int $statusCode = 200, string $message = 'Success'): ResponseInterface
    {
        $response = service('response');
        
        // If data is provided, return it as the JSON root
        if ($data !== null) {
            return $response->setJSON($data)->setStatusCode($statusCode);
        }

        // For 204 No Content or empty responses
        return $response->setStatusCode($statusCode);
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
            'message' => $message,
        ];

        if ($errors !== null) {
            if (ENVIRONMENT === 'production' && $statusCode === 500) {
                $body['errors'] = 'Internal Server Error';
            } else {
                $body['errors'] = $errors;
            }
        }

        return $response->setJSON($body)->setStatusCode($statusCode);
    }
}