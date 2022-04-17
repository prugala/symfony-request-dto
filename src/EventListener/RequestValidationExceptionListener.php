<?php
declare(strict_types=1);

namespace Prugala\RequestDto\EventListener;

use Prugala\RequestDto\Exception\RequestValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof RequestValidationException) {
            return;
        }

        $response = new JsonResponse([
            'errors' => $this->formatErrors($exception->getViolationList())
        ]);

        $event->setResponse($response);
    }

    public function formatErrors(ConstraintViolationListInterface $violationList): array
    {
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($violationList as $violation) {
            $data = [
                'message' => $violation->getMessage(),
                'code' => $violation->getCode(),
                'context' => [
                    'field' => $violation->getPropertyPath()
                ]
            ];

            $errors[] = $data;
        }

        return $errors;
    }
}
