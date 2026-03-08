<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryFactoryInterface;
use MarekSkopal\ORM\Migrations\Schema\Provider\SchemaProviderInterface;

interface DatabaseProviderInterface
{
    public function getDatabase(): DatabaseInterface;

    public function getSchemaProvider(): SchemaProviderInterface;

    public function getQueryFactory(): QueryFactoryInterface;
}
