<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Utils;

use MarekSkopal\ORM\Migrations\Utils\ArrayUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayUtils::class)]
final class ArrayUtilsTest extends TestCase
{
    #[TestWith([[1, 2, 3], [1, 2, 3], true])]
    #[TestWith([[1, 2, 3], [1, 2, 4], false])]
    #[TestWith([[1, 2, 3], [1, 2], false])]
    #[TestWith([[1, 2, 3], [1, 2, 3, 4], false])]
    #[TestWith([[1, 2, 3], [1, 3, 2], true])]
    public function testEquals($arrayA, $arrayB, $expects): void
    {
        self::assertSame($expects, ArrayUtils::equals($arrayA, $arrayB));
    }
}
