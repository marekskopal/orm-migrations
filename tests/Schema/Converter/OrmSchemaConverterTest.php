<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Schema\Converter;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\Converter\OrmSchemaConverter;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\Converter\ChildFixture;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\Converter\ItemFixture;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\Converter\ParentFixture;
use MarekSkopal\ORM\Repository\Repository;
use MarekSkopal\ORM\Schema\ColumnSchema as OrmColumnSchema;
use MarekSkopal\ORM\Schema\EntitySchema;
use MarekSkopal\ORM\Schema\Enum\PropertyTypeEnum;
use MarekSkopal\ORM\Schema\Enum\RelationEnum;
use MarekSkopal\ORM\Schema\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrmSchemaConverter::class)]
#[UsesClass(ColumnSchema::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(ForeignKeySchema::class)]
#[UsesClass(IndexSchema::class)]
#[UsesClass(TableSchema::class)]
final class OrmSchemaConverterTest extends TestCase
{
    public function testForeignKeyColumnTakesTheTypeOfAUuidPrimaryKey(): void
    {
        $tables = new OrmSchemaConverter()->convert($this->buildSchema())->tables;

        $itemId = $tables['children']->columns['item_id'];
        self::assertSame(Type::Uuid, $itemId->type);
        self::assertNull($itemId->size);
        self::assertFalse($itemId->nullable);

        self::assertSame('item_id', $tables['children']->foreignKeys['item_id']->column);
        self::assertSame('items', $tables['children']->foreignKeys['item_id']->referenceTable);
    }

    public function testForeignKeyColumnToAnIntegerPrimaryKeyIsUnchanged(): void
    {
        $tables = new OrmSchemaConverter()->convert($this->buildSchema())->tables;

        $parentId = $tables['children']->columns['parent_id'];
        self::assertSame(Type::Int, $parentId->type);
        self::assertSame(11, $parentId->size);
        self::assertTrue($parentId->nullable);
    }

    public function testOneToOneColumnTakesTheTypeOfAUuidPrimaryKey(): void
    {
        $tables = new OrmSchemaConverter()->convert($this->buildSchema())->tables;

        self::assertSame(Type::Uuid, $tables['children']->columns['item_profile_id']->type);
        self::assertTrue($tables['children']->indexes['item_profile_id']->unique);
    }

    private function buildSchema(): Schema
    {
        $items = new EntitySchema(
            entityClass: ItemFixture::class,
            repositoryClass: Repository::class,
            table: 'items',
            tableAlias: 'i',
            columns: [
                'id' => new OrmColumnSchema('id', PropertyTypeEnum::Uuid, 'id', Type::Uuid, isPrimary: true),
            ],
        );
        $parents = new EntitySchema(
            entityClass: ParentFixture::class,
            repositoryClass: Repository::class,
            table: 'parents',
            tableAlias: 'p',
            columns: [
                'id' => new OrmColumnSchema('id', PropertyTypeEnum::Int, 'id', Type::Int, isPrimary: true, isAutoIncrement: true),
            ],
        );
        // Owning relation columns as the ORM's ColumnSchemaFactory declares them: Type::Int, size 11.
        $children = new EntitySchema(
            entityClass: ChildFixture::class,
            repositoryClass: Repository::class,
            table: 'children',
            tableAlias: 'c',
            columns: [
                'id' => new OrmColumnSchema('id', PropertyTypeEnum::Int, 'id', Type::Int, isPrimary: true, isAutoIncrement: true),
                'item' => new OrmColumnSchema(
                    'item',
                    PropertyTypeEnum::Relation,
                    'item_id',
                    Type::Int,
                    relationType: RelationEnum::ManyToOne,
                    relationEntityClass: ItemFixture::class,
                    size: 11,
                ),
                'parent' => new OrmColumnSchema(
                    'parent',
                    PropertyTypeEnum::Relation,
                    'parent_id',
                    Type::Int,
                    relationType: RelationEnum::ManyToOne,
                    relationEntityClass: ParentFixture::class,
                    isNullable: true,
                    size: 11,
                ),
                'itemProfile' => new OrmColumnSchema(
                    'itemProfile',
                    PropertyTypeEnum::Relation,
                    'item_profile_id',
                    Type::Int,
                    relationType: RelationEnum::OneToOne,
                    relationEntityClass: ItemFixture::class,
                    size: 11,
                ),
            ],
        );

        return new Schema([ItemFixture::class => $items, ParentFixture::class => $parents, ChildFixture::class => $children]);
    }
}
