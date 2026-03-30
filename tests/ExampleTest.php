<?php

declare(strict_types=1);

namespace yii\template\tests;

use PHPUnit\Framework\TestCase;
use yii\template\Example;

final class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $example = new Example();

        self::assertSame(
            false,
            $example->getExample(),
            "Method should return 'false' by default.",
        );
        self::assertSame(
            true,
            $example->getExample(true),
            "Method should return 'true' when legacy parameter is 'true'.",
        );
    }
}
