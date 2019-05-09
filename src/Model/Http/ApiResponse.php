<?php

namespace BasicApi\Model\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    /**
     * @param mixed $data The response data
     * @param int $status The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($data = null, int $status = 200, array $headers = array())
    {
        parent::__construct($data, $status, $headers, true);
    }
}

