<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/native/bootstrap.php';
require __DIR__ . '/TestCase.php';
require __DIR__ . '/Unit/AlumnoTest.php';
require __DIR__ . '/Unit/QrCodeTest.php';
require __DIR__ . '/Feature/HealthCheckTest.php';

$testClasses = [
    Tests\Unit\AlumnoTest::class,
    Tests\Unit\QrCodeTest::class,
    Tests\Feature\HealthCheckTest::class,
];
$passed = 0;

foreach ($testClasses as $testClass) {
    $test = new $testClass();
    foreach (get_class_methods($test) as $method) {
        if (!str_starts_with($method, 'test_')) {
            continue;
        }
        try {
            $test->$method();
            fwrite(STDOUT, "PASS {$testClass}::{$method}\n");
            $passed++;
        } catch (Throwable $exception) {
            fwrite(STDERR, "FAIL {$testClass}::{$method}: {$exception->getMessage()}\n");
            exit(1);
        }
    }
}

fwrite(STDOUT, "Tests passed: {$passed}\n");
