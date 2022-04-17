<?php
declare(strict_types=1);

namespace Prugala\RequestDto\Tests\Resources;

use Prugala\RequestDto\Dto\RequestDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleDto implements RequestDtoInterface
{
    public string $name;

    #[Assert\Range(min: 2, max: 10)]
    public int $position;
}
