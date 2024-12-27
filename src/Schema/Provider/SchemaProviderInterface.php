<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Provider;

use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;

interface SchemaProviderInterface
{
    public function getDatabaseSchema(): DatabaseSchema;
}
