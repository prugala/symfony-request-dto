<?php

namespace Prugala\RequestDto\Tests\ArgumentResolver;

use Prugala\RequestDto\ArgumentResolver\RequestDtoArgumentResolver;
use PHPUnit\Framework\TestCase;
use Prugala\RequestDto\Exception\RequestValidationException;
use Prugala\RequestDto\Tests\Resources\ExampleDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validation;

class RequestDtoArgumentResolverTest extends TestCase
{
    public function testResolveCorrectRequest(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();
        $resolver = new RequestDtoArgumentResolver(
            $validator
        );

        $request = new Request();
        $request->setMethod('POST');
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            json_encode([
                'name' => 'test',
                'position' => 2,
                'flag' => false
            ])
        );

        $argumentMetadata = new ArgumentMetadata('test', ExampleDto::class, true, false, '');

        $request = $resolver->resolve($request, $argumentMetadata);

        /** @var ExampleDto $dto */
        $dto = iterator_to_array($request)[0];

        $this->assertSame('test', $dto->name);
        $this->assertSame(2, $dto->position);
        $this->assertSame(false, $dto->flag);
    }

    public function testResolveCorrectGetRequest(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();
        $resolver = new RequestDtoArgumentResolver(
            new ObjectNormalizer(),
            $validator
        );

        $request = new Request();
        $request->setMethod('GET');
        $request->initialize(
            [
                'name' => 'test',
                'position' => 2,
                'flag' => 'false'
            ],
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
        );

        $argumentMetadata = new ArgumentMetadata('test', ExampleDto::class, true, false, '');

        $request = $resolver->resolve($request, $argumentMetadata);

        /** @var ExampleDto $dto */
        $dto = iterator_to_array($request)[0];

        $this->assertSame('test', $dto->name);
        $this->assertSame(2, $dto->position);
        $this->assertSame(false, $dto->flag);
    }

    public function testResolveRequestExpectException(): void
    {
        $this->expectException(RequestValidationException::class);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $resolver = new RequestDtoArgumentResolver(
            new ObjectNormalizer(),
            $validator
        );

        $request = new Request();
        $request->setMethod('POST');
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            json_encode([
                'name' => 'test',
                'position' => 1, // ExampleDto require position between 2-10
            ])
        );

        $argumentMetadata = new ArgumentMetadata('test', ExampleDto::class, true, false, '');

        $request = $resolver->resolve($request, $argumentMetadata);

        /** @var ExampleDto $dto */
        iterator_to_array($request)[0];
    }

    public function testResolveRequestWithViolation(): void
    {
        $this->expectException(RequestValidationException::class);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $resolver = new RequestDtoArgumentResolver(
            new ObjectNormalizer(),
            $validator
        );

        $request = new Request();
        $request->setMethod('POST');
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            json_encode([
                'name' => 'test',
                'position' => 1, // ExampleDto require position between 2-10
            ])
        );

        $argumentMetadata = new ArgumentMetadata('test', ExampleDto::class, true, false, '');

        try {
            $request = $resolver->resolve($request, $argumentMetadata);

            /** @var ExampleDto $dto */
            iterator_to_array($request)[0];
        } catch (RequestValidationException $exception) {
            $this->assertSame(
                'This value should be between 2 and 10.',
                $exception->getViolationList()->get(0)->getMessage()
            );

            throw $exception;
        }
    }
}
