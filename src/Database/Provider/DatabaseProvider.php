<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;
use MarekSkopal\ORM\Migrations\Schema\Provider\SchemaProviderInterface;

final readonly class DatabaseProvider implements DatabaseProviderInterface
{
    public function __construct(
        private DatabaseInterface $database,
        private SchemaProviderInterface $schemaProvider,
        private TypeConverterInterface $typeConverter,
    ) {
    }

    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    public function getSchemaProvider(): SchemaProviderInterface
    {
        return $this->schemaProvider;
    }

    public function getTypeConverter(): TypeConverterInterface
    {
        return $this->typeConverter;
    }
}
