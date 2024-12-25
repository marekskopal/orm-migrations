<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Enum;

enum ReferenceOptionEnum: string
{
    case Restrict = 'RESTRICT';
    case Cascade = 'CASCADE';
    case SetNull = 'SET NULL';
    case NoAction = 'NO ACTION';
    case SetDefault = 'SET DEFAULT';
}
