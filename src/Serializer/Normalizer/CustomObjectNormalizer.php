<?php

declare(strict_types=1);

namespace Prugala\RequestDto\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomObjectNormalizer extends ObjectNormalizer
{
    protected function setAttributeValue(object $object, string $attribute, mixed $value, string $format = null, array $context = []): void
    {
        if ($value) {
            $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if (!is_null($boolValue)) {
                $value = $boolValue;
            }
        }

        parent::setAttributeValue($object, $attribute, $value, $format, $context);
    }
}
