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
        $lowerTypeString = strtolower($typeString);

        if (str_starts_with($lowerTypeString, 'enum')) {
            $pattern = '/^enum\((?<enum>.+)\)$/i';
            if (preg_match($pattern, $typeString, $matches) !== 1) {
                throw new \InvalidArgumentException('Invalid type string format');
            }

            // Match each quoted member as a whole so commas inside a value do not
            // split it, and unescape doubled quotes (MySQL's escaping for enum members).
            preg_match_all('/\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"/', $matches['enum'], $memberMatches);

            $enum = array_map(
                static fn(string $member): string => $member[0] === "'"
                    ? str_replace("''", "'", substr($member, 1, -1))
                    : str_replace('""', '"', substr($member, 1, -1)),
                $memberMatches[0],
            );

            return new self(type: 'enum', enum: $enum);
        }

        $pattern = '/^(?<type>\w+)(?:(?:(\((?<size>\d+)\))?$)|(?:\((?<precision>\d+),(?<scale>\d+)\)$))/';
        if (preg_match($pattern, $lowerTypeString, $matches) !== 1) {
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
