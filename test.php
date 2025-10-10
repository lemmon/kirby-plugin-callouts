#!/usr/bin/env php
<?php

declare(strict_types=1);

use Lemmon\Callouts\Renderer;

require_once __DIR__ . '/lib/callouts.php';

/**
 * Simple CLI runner that prints transformed callout markup.
 */
final class CalloutTestRunner
{
    /**
     * Runs the test harness.
     *
     * @param array<int, string> $arguments
     */
    public static function run(array $arguments): int
    {
        $sourcePath = $arguments[1] ?? __DIR__ . '/tests/sample.md';

        if (!is_file($sourcePath)) {
            fwrite(STDERR, sprintf("File not found: %s\n", $sourcePath));
            return 1;
        }

        $contents = file_get_contents($sourcePath);
        if ($contents === false) {
            fwrite(STDERR, sprintf("Unable to read: %s\n", $sourcePath));
            return 1;
        }

        echo Renderer::transform($contents) . PHP_EOL;

        return 0;
    }
}

exit(CalloutTestRunner::run($argv));
