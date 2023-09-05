<?php
declare(strict_types=1);

namespace Prugala\RequestDto\ArgumentResolver;

use Prugala\RequestDto\Dto\RequestDtoInterface;
use Prugala\RequestDto\Exception\RequestValidationException;
use Prugala\RequestDto\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
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
        $headers = $request->headers->all();
        $headers = array_combine(
            array_map(fn($name) => str_replace('-', '_', $name), array_keys($headers)),
            array_map(fn($value) => is_array($value) ? reset($value) : $value, $headers)
        );

        $content = $request->getContent();
        $contentDecoded = json_decode($content ?? '', true) ?? [];
        $payload = array_merge($request->request->all(), $request->query->all(), $request->files->all(), $headers, $contentDecoded);
        $serializer = new Serializer([new CustomNormalizer(null, new CamelCaseToSnakeCaseNameConverter())], [new JsonEncoder(), new XmlEncoder()]);

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
