<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryFactoryInterface;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;
use MarekSkopal\ORM\Migrations\Schema\Provider\SchemaProviderInterface;

final readonly class DatabaseProvider implements DatabaseProviderInterface
{
    public function __construct(
        private DatabaseInterface $database,
        private SchemaProviderInterface $schemaProvider,
        private TypeConverterInterface $typeConverter,
        private QueryFactoryInterface $queryFactory,
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

    public function getQueryFactory(): QueryFactoryInterface
    {
        return $this->queryFactory;
    }
}
