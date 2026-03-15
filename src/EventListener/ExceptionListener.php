<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener(event: ExceptionEvent::class)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if ($statusCode === 404) {
            $response = new JsonResponse([
                'status' => 'fail',
                'data' => [
                    'message' => 'The requested resource was not found.'
                ]
            ], 404);

            $event->setResponse($response);
            return;
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            $response = new JsonResponse([
                'status' => 'fail',
                'data' => ['message' => $exception->getMessage()]
            ], $statusCode);
        } else {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'code' => $exception->getCode() ?: $statusCode
            ], $statusCode);
        }

        $event->setResponse($response);
    }
}
