<?php

namespace Imiskuf\BasicApiBundle\EventSubscriber;

use Imiskuf\BasicApiBundle\Exception\Http\ApiProblemException;
use Imiskuf\BasicApiBundle\Factory\Http\ApiProblemResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiProblemExceptionSubscriber implements EventSubscriberInterface
{
    //profiler has 0, so we use -1 to execute profiler correctly before we modify response
    private const PRIORITY = -1;

    /**
     * @var ApiProblemResponseFactory
     */
    private $responseFactory;

    /**
     * @param ApiProblemResponseFactory $responseFactory
     */
    public function __construct(ApiProblemResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', self::PRIORITY],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (!$exception instanceof ApiProblemException) {
            return;
        }

        $event->setResponse(
            $this->responseFactory->createResponse(
                $exception->getApiProblem()
            )
        );
    }
}
