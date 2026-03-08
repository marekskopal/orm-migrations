<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter\Type;

use MarekSkopal\ORM\Enum\Type;

final class PgsqlTypeConverter implements TypeConverterInterface
{
    public function convert(string $type): Type
    {
        return match (strtolower($type)) {
            'smallint' => Type::SmallInt,
            'integer', 'int' => Type::Int,
            'bigint' => Type::BigInt,
            'numeric', 'decimal' => Type::Decimal,
            'real' => Type::Float,
            'double precision' => Type::Double,
            'character varying', 'varchar' => Type::String,
            'text' => Type::Text,
            'boolean' => Type::Boolean,
            'uuid' => Type::Uuid,
            'bytea' => Type::Binary,
            'date' => Type::Date,
            'timestamp without time zone', 'timestamp' => Type::DateTime,
            'time without time zone', 'time' => Type::Time,
            'timestamp with time zone', 'timestamptz' => Type::Timestamp,
            'user-defined' => Type::Enum,
            'json', 'jsonb' => Type::Json,
            default => throw new \InvalidArgumentException(sprintf('Type %s is not supported.', $type)),
        };
    }

    public function convertToDatabase(Type $type): string
    {
        return match ($type) {
            Type::SmallInt => 'smallint',
            Type::Int => 'integer',
            Type::BigInt => 'bigint',
            Type::Decimal => 'numeric',
            Type::Float => 'real',
            Type::Double => 'double precision',
            Type::String => 'varchar',
            Type::TinyText, Type::Text, Type::MediumText, Type::LongText => 'text',
            Type::Boolean => 'boolean',
            Type::Uuid => 'uuid',
            Type::Binary, Type::TinyBlob, Type::Blob, Type::MediumBlob, Type::LongBlob => 'bytea',
            Type::Date => 'date',
            Type::DateTime => 'timestamp',
            Type::Time => 'time',
            Type::Timestamp => 'timestamptz',
            Type::Enum => 'varchar',
            Type::Json => 'jsonb',
        };
    }

    public function sanitizeSize(Type $type, ?int $size): ?int
    {
        return match ($type) {
            Type::SmallInt, Type::Int, Type::BigInt, Type::Boolean => null,
            Type::String => $size > 255 ? 255 : $size ?? 255,
            default => $size,
        };
    }
}
