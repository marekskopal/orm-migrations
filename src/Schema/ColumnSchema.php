<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

use BackedEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Utils\ArrayUtils;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;

readonly class ColumnSchema
{
    /** @param list<string>|null $enum */
    public function __construct(
        public string $name,
        public Type $type,
        public bool $nullable,
        public bool $autoincrement,
        public bool $primary,
        public ?int $size,
        public ?int $precision,
        public ?int $scale,
        public ?array $enum,
        public string|int|float|bool|BackedEnum|null $default,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name
        && $this->type === $other->type
        && $this->nullable === $other->nullable
        && $this->autoincrement === $other->autoincrement
        && $this->primary === $other->primary
        // If size is null in ORM schema, it does not matter if has size in database schema
        && ($other->size === null || $this->size === $other->size)
        && $this->precision === $other->precision
        && $this->scale === $other->scale
        && ArrayUtils::equals($this->enum ?? [], $other->enum ?? [])
        && StringUtils::toCompare($this->default) === StringUtils::toCompare($other->default);
    }
}
