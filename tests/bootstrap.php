<?php

declare(strict_types=1);

namespace {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    if (class_exists('Kirby\\Cms\\App') && property_exists('Kirby\\Cms\\App', 'enableWhoops')) {
        Kirby\Cms\App::$enableWhoops = false;
    }
}

namespace Kirby\Cms {
    if (!class_exists(__NAMESPACE__ . '\\App')) {
        final class App
        {
            public static function instance(): self
            {
                return new self();
            }

            public function kirbytext(string $content): string
            {
                return $content;
            }
        }

        if (!defined('KIRBY_CALLOUTS_STUB')) {
            define('KIRBY_CALLOUTS_STUB', true);
        }
    }
}

namespace {
    require_once __DIR__ . '/../lib/callouts.php';
}
