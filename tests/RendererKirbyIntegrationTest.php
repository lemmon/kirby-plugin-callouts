<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Lemmon\Callouts\Renderer;
use PHPUnit\Framework\TestCase;

final class RendererKirbyIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(App::class)) {
            $this->markTestSkipped('Kirby is not installed.');
        }

        if (defined('KIRBY_CALLOUTS_STUB')) {
            $this->markTestSkipped('Kirby stub is active.');
        }

        try {
            $this->bootKirby();
        } catch (Throwable $e) {
            $this->markTestSkipped('Kirby app could not be bootstrapped: ' . $e->getMessage());
        }
    }

    public function testKirbyTextRendersMarkdown(): void
    {
        $input = "> [!NOTE]\n> **Bold**\n";
        $output = Renderer::transform($input);

        $this->assertStringContainsString('<strong>Bold</strong>', $output);
    }

    private function bootKirby(): void
    {
        new App([
            'roots' => [
                'index' => __DIR__,
                'site' => __DIR__,
                'content' => __DIR__,
                'media' => __DIR__,
                'assets' => __DIR__,
            ],
        ]);
    }
}
