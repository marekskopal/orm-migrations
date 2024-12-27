<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;
use MarekSkopal\ORM\Migrations\Schema\Provider\SchemaProviderInterface;

interface DatabaseProviderInterface
{
    public function getDatabase(): DatabaseInterface;

    public function getSchemaProvider(): SchemaProviderInterface;

    public function getTypeConverter(): TypeConverterInterface;
}
