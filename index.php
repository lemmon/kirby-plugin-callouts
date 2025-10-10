<?php

declare(strict_types=1);

use Lemmon\Callouts\Renderer;

require_once __DIR__ . '/lib/callouts.php';

Kirby::plugin('lemmon/callouts', [
    'options' => [
        'classPrefix' => Renderer::DEFAULT_CLASS_PREFIX,
        'renderHeader' => Renderer::DEFAULT_RENDER_HEADER,
        'wrapper' => Renderer::DEFAULT_WRAPPER,
    ],
    'hooks' => [
        'kirbytext:before' => static function (string $text): string {
            return Renderer::transform($text, [
                'classPrefix' => option('lemmon.callouts.classPrefix', Renderer::DEFAULT_CLASS_PREFIX),
                'renderHeader' => option('lemmon.callouts.renderHeader', Renderer::DEFAULT_RENDER_HEADER),
                'wrapper' => option('lemmon.callouts.wrapper', Renderer::DEFAULT_WRAPPER),
            ]);
        },
    ],
]);
