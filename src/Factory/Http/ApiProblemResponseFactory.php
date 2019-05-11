<?php

namespace Imiskuf\BasicApiBundle\Factory\Http;

use Imiskuf\BasicApiBundle\Model\Http\ApiProblem;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiProblemResponseFactory
{
    public const API_PROBLEM_MIME_TYPE = 'application/problem+json';

    /**
     * @param ApiProblem $apiProblem
     * @return JsonResponse
     */
    public function createResponse(ApiProblem $apiProblem): JsonResponse
    {
        $response = new JsonResponse($apiProblem->toArray(), $apiProblem->getStatusCode());
        $response->headers->set('Content-Type', self::API_PROBLEM_MIME_TYPE);

        return $response;
    }
}
