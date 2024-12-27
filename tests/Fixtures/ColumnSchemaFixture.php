<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Fixtures;

use BackedEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;

final class ColumnSchemaFixture
{
    /** @param list<string>|null $enum */
    public static function create(
        ?string $name = null,
        ?Type $type = null,
        ?bool $nullable = null,
        ?bool $autoincrement = null,
        ?bool $primary = null,
        ?int $size = null,
        ?int $precision = null,
        ?int $scale = null,
        ?array $enum = null,
        string|int|float|bool|BackedEnum|null $default = null,
    ): ColumnSchema {
        return new ColumnSchema(
            name: $name ?? 'id',
            type: $type ?? Type::Int,
            nullable: $nullable ?? false,
            autoincrement: $autoincrement ?? false,
            primary: $primary ?? false,
            size: $size,
            precision: $precision,
            scale: $scale,
            enum: $enum,
            default: $default,
        );
    }
}
