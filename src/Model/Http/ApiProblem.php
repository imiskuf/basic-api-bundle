<?php

namespace Imiskuf\BasicApiBundle\Model\Http;

use Imiskuf\BasicApiBundle\Model\ArrayableInterface;
use Symfony\Component\HttpFoundation\Response;

class ApiProblem implements ArrayableInterface
{
    private const FIELD_STATUS_CODE = 'status';
    private const FIELD_MESSAGE     = 'message';

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $extraData = [];

    /**
     * @param int $statusCode
     * @param string|null $message
     * @param string|null $detail
     * @param int|null $errorCode
     */
    public function __construct(int $statusCode, string $message = null, string $detail = null, int $errorCode = null)
    {
        if (null === $message) {
            $message = Response::$statusTexts[$statusCode] ?? 'Unknown status code';
        }

        $this->statusCode = $statusCode;
        $this->message = $message;

        if (null !== $detail) {
            $this->set('detail', $detail);
        }

        if (null !== $errorCode) {
            $this->set('error', $errorCode);
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            [
                self::FIELD_STATUS_CODE => $this->statusCode,
                self::FIELD_MESSAGE => $this->message
            ],
            $this->extraData
        );
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        $this->extraData[$name] = $value;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
