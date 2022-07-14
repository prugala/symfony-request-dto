<?php
declare(strict_types=1);

namespace Prugala\RequestDto\ArgumentResolver;

use Prugala\RequestDto\Dto\RequestDtoInterface;
use Prugala\RequestDto\Exception\RequestValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface    $validator
    )
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), RequestDtoInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($request->getMethod() === 'GET') {
            $payload = $request->query->all();
        } else {
            $payload = $request->getContent();
            $payload = json_decode($toTransform, true);
        }

        $request = $this->denormalizer->denormalize($payload, $argument->getType(), null, [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
        ]);

        $violations = $this->validator->validate($request);

        if ($violations->count()) {
            throw new RequestValidationException($violations);
        }

        yield $request;
    }
}
