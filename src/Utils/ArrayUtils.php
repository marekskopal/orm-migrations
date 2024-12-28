<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

class ArrayUtils
{
    /**
     * @param array<mixed> $arrayA
     * @param array<mixed> $array2
     */
    public static function equals(array $arrayA, array $arrayB): bool
    {
        array_multisort($arrayA);
        array_multisort($arrayB);
        return serialize($arrayA) === serialize($arrayB);
    }
}
