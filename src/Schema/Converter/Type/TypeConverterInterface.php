<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter\Type;

use MarekSkopal\ORM\Enum\Type;

interface TypeConverterInterface
{
    public function convert(string $type): Type;

    public function convertToDatabase(Type $type): string;

    public function sanitizeSize(Type $type, ?int $size): ?int;
}
