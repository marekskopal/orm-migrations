<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter\Type;

use MarekSkopal\ORM\Enum\Type;

final class MySqlTypeConverter implements TypeConverterInterface
{
    public function convert(string $type): Type
    {
        return match (strtolower($type)) {
            'smallint' => Type::SmallInt,
            'int' => Type::Int,
            'bigint' => Type::BigInt,
            'decimal' => Type::Decimal,
            'float' => Type::Float,
            'double precision' => Type::Double,
            'varchar' => Type::String,
            'tinytext' => Type::TinyText,
            'text' => Type::Text,
            'mediumtext' => Type::MediumText,
            'longtext' => Type::LongText,
            'tinyint' => Type::Boolean,
            'uuid' => Type::Uuid,
            'varbinary' => Type::Binary,
            'tinyblob' => Type::TinyBlob,
            'blob' => Type::Blob,
            'mediumblob' => Type::MediumBlob,
            'longblob' => Type::LongBlob,
            'date' => Type::Date,
            'datetime' => Type::DateTime,
            'time' => Type::Time,
            'timestamp' => Type::Timestamp,
            'enum' => Type::Enum,
            'json' => Type::Json,
            default => throw new \InvalidArgumentException(sprintf('Type %s is not supported.', $type)),
        };
    }

    public function convertToDatabase(Type $type): string
    {
        return match ($type) {
            Type::SmallInt => 'smallint',
            Type::Int => 'int',
            Type::BigInt => 'bigint',
            Type::Decimal => 'decimal',
            Type::Float => 'float',
            Type::Double => 'double precision',
            Type::String => 'varchar',
            Type::TinyText => 'tinytext',
            Type::Text => 'text',
            Type::MediumText => 'mediumtext',
            Type::LongText => 'longtext',
            Type::Boolean => 'tinyint',
            Type::Uuid => 'uuid',
            Type::Binary => 'varbinary',
            Type::TinyBlob => 'tinyblob',
            Type::Blob => 'blob',
            Type::MediumBlob => 'mediumblob',
            Type::LongBlob => 'longblob',
            Type::Date => 'date',
            Type::DateTime => 'datetime',
            Type::Time => 'time',
            Type::Timestamp => 'timestamp',
            Type::Enum => 'enum',
            Type::Json => 'json',
        };
    }
}
