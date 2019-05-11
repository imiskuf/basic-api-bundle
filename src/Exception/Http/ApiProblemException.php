<?php

namespace Imiskuf\BasicApiBundle\Exception\Http;

use Imiskuf\BasicApiBundle\Model\Http\ApiProblem;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    /**
     * @var ApiProblem
     */
    private $apiProblem;

    public function __construct(ApiProblem $apiProblem, Exception $previous = NULL, array $headers = [], ?int $code = 0)
    {
        $this->apiProblem = $apiProblem;

        parent::__construct($apiProblem->getStatusCode(), $apiProblem->getMessage(), $previous, $headers, $code);
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem(): ApiProblem
    {
        return $this->apiProblem;
    }
}
