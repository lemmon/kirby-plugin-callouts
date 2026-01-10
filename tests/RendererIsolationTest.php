<?php

declare(strict_types=1);

use Lemmon\Callouts\Renderer;
use PHPUnit\Framework\TestCase;

final class RendererIsolationTest extends TestCase
{
    public function testIgnoresFencedCodeBlock(): void
    {
        $input = "```\n> [!NOTE]\n> inside code\n```\n";

        $this->assertSame($input, Renderer::transform($input));
    }

    public function testTransformsCalloutOutsideBlocks(): void
    {
        $input = "> [!NOTE]\n> outside\n";
        $output = Renderer::transform($input);

        $this->assertStringContainsString('class="callout callout--note"', $output);
        $this->assertStringContainsString('callout__header', $output);
        $this->assertStringContainsString('outside', $output);
        $this->assertStringNotContainsString('[!NOTE]', $output);
    }

    public function testUsesInlineLabelWithoutChangingModifier(): void
    {
        $input = "> [!TIP] My Fancy Label\n> Body line\n";
        $output = Renderer::transform($input);

        $this->assertStringContainsString('class="callout callout--tip"', $output);
        $this->assertStringContainsString('My Fancy Label', $output);
        $this->assertSame(2, substr_count($output, 'My Fancy Label'));
        $this->assertStringContainsString('Body line', $output);
    }
}
