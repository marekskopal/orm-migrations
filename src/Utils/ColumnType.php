<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

readonly class ColumnType
{
    /** @param list<string>|null $enum */
    public function __construct(
        public string $type,
        public ?int $size = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public ?array $enum = null,
    ) {
    }

    public static function parseColumnType(string $typeString): self
    {
        $typeString = strtolower($typeString);

        if (str_starts_with($typeString, 'enum')) {
            $pattern = '/^enum\((?<enum>.+)\)$/';
            if (preg_match($pattern, $typeString, $matches) !== 1) {
                throw new \InvalidArgumentException('Invalid type string format');
            }

            return new self(
                type: 'enum',
                enum: array_map(fn($value) => trim($value, '\'"'), explode(',', $matches['enum'])),
            );
        }

        $pattern = '/^(?<type>\w+)(?:(?:(\((?<size>\d+)\))?$)|(?:\((?<precision>\d+),(?<scale>\d+)\)$))/';
        if (preg_match($pattern, $typeString, $matches) !== 1) {
            throw new \InvalidArgumentException('Invalid type string format');
        }

        return new self(
            type: $matches['type'],
            size: isset($matches['size']) ? (int) $matches['size'] : null,
            precision: isset($matches['precision']) ? (int) $matches['precision'] : null,
            scale: isset($matches['scale']) ? (int) $matches['scale'] : null,
        );
    }
}
