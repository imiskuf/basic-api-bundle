<?php

namespace Imiskuf\BasicApiBundle\Controller;

use Imiskuf\BasicApiBundle\Exception\Http\ApiProblemException;
use Imiskuf\BasicApiBundle\Model\Http\ApiProblem;
use Imiskuf\BasicApiBundle\Model\Http\ApiResponse;
use Exception;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractController extends BaseAbstractController
{
    private const SERIALIZER_SERVICE_ID = 'jms_serializer';
    private const VALIDATOR_SERVICE_ID = 'validator';

    /**
     * @var
     */
    private $namingStrategy = null;

    /**
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            self::SERIALIZER_SERVICE_ID => SerializerInterface::class,
            self::VALIDATOR_SERVICE_ID => ValidatorInterface::class
        ]);
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->getService(self::SERIALIZER_SERVICE_ID);
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->getService(self::VALIDATOR_SERVICE_ID);
    }

    /**
     * @param string $serviceId
     * @return mixed
     */
    protected function getService(string $serviceId)
    {
        if ($this->container->has($serviceId)) {
            return $this->container->get($serviceId);
        }

        throw new LogicException("No service with ID '{$serviceId}' supported!");
    }

    protected function setSerializerNamingStrategy(PropertyNamingStrategyInterface $strategy): void
    {
        $this->namingStrategy = $strategy;
    }

    /**
     * @param Request $request
     * @param string $modelClass
     * @param string $bodyFormat
     * @throws ApiProblemException
     * @return mixed
     */
    protected function createModel(Request $request, string $modelClass, string $bodyFormat = 'json')
    {
        return $this->getSerializer()->deserialize(
            $this->requireRequestData($request),
            $modelClass,
            $bodyFormat
        );
    }

    /**
     * @param mixed $data
     * @param int $statusCode
     * @param array|null $groups
     * @return ApiResponse
     */
    protected function createApiResponse(
        $data,
        int $statusCode = ApiResponse::HTTP_OK,
        array $groups = null
    ): ApiResponse {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        if (null !== $groups) {
            $context->setGroups($groups);
        }

        $serializer = $this->container->get('jms_serializer');
        $serializedData = $serializer->serialize($data, 'json', $context);

        return new ApiResponse($serializedData, $statusCode);
    }

    /**
     * @param int $statusCode
     * @return ApiResponse
     */
    protected function createEmptyApiResponse(int $statusCode = ApiResponse::HTTP_NO_CONTENT): ApiResponse
    {
        return new ApiResponse('', $statusCode);
    }

    /**
     * @return ApiProblemException
     */
    protected function createInvalidBodyException(): ApiProblemException
    {
        return $this->createApiException('Request has empty or invalid JSON in body!');
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @param string|null $detail
     * @param int|null $errorCode
     * @return ApiProblemException
     */
    protected function createApiException(
        string $message = null,
        int $statusCode = ApiResponse::HTTP_BAD_REQUEST,
        string $detail = null,
        int $errorCode = null
    ): ApiProblemException
    {
        return new ApiProblemException(
            new ApiProblem($statusCode, $message, $detail, $errorCode)
        );
    }

    /**
     * @param Exception $e
     * @return ApiProblemException
     */
    protected function getInternalServerErrorException(Exception $e): ApiProblemException
    {
        return $this->createApiException(null, ApiResponse::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
    }

    protected function validate($model, array $groups = null, array $constraints = null): void
    {
        $this->throwExceptionIfNotValid(
            $this->getValidator()->validate($model, $constraints, $groups)
        );
    }

    /**
     * @param ConstraintViolationListInterface $constraintViolationList
     */
    protected function throwExceptionIfNotValid(ConstraintViolationListInterface $constraintViolationList): void
    {
        if ($constraintViolationList->count() === 0) {
            return;
        }

        $exception = $this->createApiException(
            'Request contains validation errors!',
            ApiResponse::HTTP_UNPROCESSABLE_ENTITY
        );
        $exception->getApiProblem()->set('validation_errors', $this->getValidationErrors($constraintViolationList));

        throw $exception;
    }

    /**
     * @param Request $request
     * @return array
     * @throws ApiProblemException
     */
    protected function getRequestData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createInvalidBodyException();
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return string
     * @throws ApiProblemException
     */
    protected function requireRequestData(Request $request): string
    {
        if (empty($content = $request->getContent()) || null === json_decode($content)) {
            throw $this->createInvalidBodyException();
        }

        return $content;
    }

    /**
     * @param ConstraintViolationListInterface|ConstraintViolationInterface[] $constraintViolationList
     * @return array
     */
    private function getValidationErrors(ConstraintViolationListInterface $constraintViolationList): array
    {
        $errors = [];
        $strategy = $this->getSerializerNamingStrategy();

        foreach ($constraintViolationList as $key => $error) {
            $errors[$key] = [
                'field' => $strategy->translateName(new PropertyMetadata('', $error->getPropertyPath())),
                'message' => $error->getMessage(),
                'value' => $error->getInvalidValue()
            ];
        }

        return $errors;
    }

    private function getSerializerNamingStrategy(): PropertyNamingStrategyInterface
    {
        if (null === $this->namingStrategy) {
            $this->namingStrategy = new IdenticalPropertyNamingStrategy();
        }

        return $this->namingStrategy;
    }
}
