<?php

declare(strict_types=1);

namespace Prugala\RequestDto\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class StringBooleanDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if ($data === 'false') {
            return false;
        }

        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return true;
    }
}
