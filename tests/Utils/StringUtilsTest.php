<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Utils;

use MarekSkopal\ORM\Migrations\Tests\Fixtures\TestEnum;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringUtils::class)]
final class StringUtilsTest extends TestCase
{
    public function testToCodeRendersScalarsAndArrays(): void
    {
        self::assertSame("'New York'", StringUtils::toCode('New York'));
        self::assertSame('255', StringUtils::toCode(255));
        self::assertSame('true', StringUtils::toCode(true));
        self::assertSame('false', StringUtils::toCode(false));
        self::assertSame('null', StringUtils::toCode(null));
        self::assertSame("['a', 'b', 'c']", StringUtils::toCode(['a', 'b', 'c']));
    }

    public function testToCodeRendersBackedEnumAsValue(): void
    {
        self::assertSame("'a'", StringUtils::toCode(TestEnum::A));
    }

    public function testToCodeEscapesQuotesAndBackslashesToPreventCodeInjection(): void
    {
        $code = StringUtils::toCode("foo').update(); system('rm -rf /'); //");

        // The single quote is escaped, so the string literal cannot be broken out of.
        self::assertSame("'foo\\').update(); system(\\'rm -rf /\\'); //'", $code);

        // The rendered code must evaluate back to the original string, not execute anything.
        self::assertSame("foo').update(); system('rm -rf /'); //", eval('return ' . $code . ';'));
    }

    public function testToParameterNormalizesBindableValues(): void
    {
        self::assertSame(1, StringUtils::toParameter(true));
        self::assertSame(0, StringUtils::toParameter(false));
        self::assertSame('a', StringUtils::toParameter(TestEnum::A));
        self::assertSame('John', StringUtils::toParameter('John'));
        self::assertSame(42, StringUtils::toParameter(42));
        self::assertNull(StringUtils::toParameter(null));
    }
}
