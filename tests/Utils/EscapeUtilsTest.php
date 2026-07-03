<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Utils;

use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EscapeUtils::class)]
final class EscapeUtilsTest extends TestCase
{
    public function testEscapeWrapsWithQuoteChar(): void
    {
        self::assertSame('`users`', EscapeUtils::escape('users'));
        self::assertSame('"users"', EscapeUtils::escape('users', '"'));
    }

    public function testEscapeDoublesEmbeddedBacktick(): void
    {
        self::assertSame('`a``b`', EscapeUtils::escape('a`b'));
    }

    public function testEscapeDoublesEmbeddedDoubleQuote(): void
    {
        self::assertSame('"a""b"', EscapeUtils::escape('a"b', '"'));
    }

    public function testEscapeNeutralizesIdentifierInjection(): void
    {
        $malicious = 'id` , ADD COLUMN evil INT); DROP TABLE users; -- ';

        $escaped = EscapeUtils::escape($malicious);

        // The embedded backtick is doubled, so it can no longer terminate the identifier.
        self::assertSame('`id`` , ADD COLUMN evil INT); DROP TABLE users; -- `', $escaped);
    }
}
