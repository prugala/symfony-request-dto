<?php
declare(strict_types=1);

namespace Prugala\RequestDto\ArgumentResolver;

use Prugala\RequestDto\Dto\RequestDtoInterface;
use Prugala\RequestDto\Exception\RequestValidationException;
use Prugala\RequestDto\Serializer\Normalizer\StringBooleanDenormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator
    )
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), RequestDtoInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $toTransform = $request->getContent();
        $payload = json_decode($toTransform, true);
        $payload = array_merge($request->query->all(), $payload ?? []);

        $serializer = new Serializer([new ObjectNormalizer(null, null, null, new ReflectionExtractor()), new ArrayDenormalizer(), new StringBooleanDenormalizer()], [new JsonEncoder(), new XmlEncoder()]);
        $request = $serializer->denormalize($payload, $argument->getType(), null, [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
        ]);

        $violations = $this->validator->validate($request);

        if ($violations->count()) {
            throw new RequestValidationException($violations);
        }

        yield $request;
    }
}
