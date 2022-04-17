<?php
declare(strict_types=1);

namespace Prugala\RequestDto\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationException extends BadRequestHttpException
{
    public function __construct(private ConstraintViolationListInterface $violationList)
    {
        parent::__construct('Request validation failed');
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
