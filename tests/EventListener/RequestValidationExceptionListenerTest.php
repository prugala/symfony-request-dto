<?php

namespace Prugala\RequestDto\Tests\EventListener;

use Prugala\RequestDto\EventListener\RequestValidationExceptionListener;
use PHPUnit\Framework\TestCase;
use Prugala\RequestDto\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class RequestValidationExceptionListenerTest extends TestCase
{
    private EventDispatcher $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testShouldGetAJsonResponseWithViolation(): void
    {
        $listener = new RequestValidationExceptionListener();
        $this->dispatcher->addListener('onKernelException', [$listener, 'onKernelException']);

        $constraintViolationList = new ConstraintViolationList();
        $constraintViolationList->add(
            new ConstraintViolation(
                'test',
                'test',
                [],
                'test',
                'test.test',
                'invalid value',
                1,
                123
            )
        );

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            1,
            new RequestValidationException($constraintViolationList),
        );
        $this->dispatcher->dispatch($event, 'onKernelException');
        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(
            $event->getResponse()->getContent(),
            json_encode([
                'errors' => [
                    [
                        'message' => 'test',
                        'code' => "123",
                        'context' => [
                            'field' => 'test.test',
                        ],
                    ],
                ],
            ])
        );
    }
}
