<?php

namespace Prugala\RequestDto\Serializer\Normalizer;

use Prugala\RequestDto\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomNormalizer extends ObjectNormalizer
{
    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null, ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, callable $objectClassResolver = null, array $defaultContext = [])
    {
        parent::__construct($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter(), $propertyAccessor, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);
    }

    public function setAttributeValue(object $object, string $attribute, mixed $value, string $format = null, array $context = [])
    {
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (!is_null($value) && !is_null($boolValue)) {
            $value = $boolValue;
        }

        parent::setAttributeValue($object, $attribute, $value, $format, $context);
    }

    public function supportsNormalization(mixed $data, string $format = null)
    {
        return false;
    }

   public function supportsDenormalization(mixed $data, string $type, string $format = null){
        if (false === parent::supportsDenormalization($data, $type, $format)) {
            return false;
        }

       return is_a($type, Request::class, true) || is_a($type, RequestDtoInterface::class, true);
   }
}
