<?php

namespace Tests;

abstract class TestCase
{
    protected function assertSame(mixed $expected, mixed $actual): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    protected function assertTrue(bool $condition): void
    {
        if (!$condition) {
            throw new \RuntimeException('Expected condition to be true.');
        }
    }
}
