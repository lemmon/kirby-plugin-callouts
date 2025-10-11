<?php

declare(strict_types=1);

namespace Lemmon\Callouts;

use Kirby\Cms\App;
use Throwable;

/**
 * Callout renderer that transforms GitHub-style callouts into HTML callout markup.
 */
final class Renderer
{
    /**
     * Default CSS class prefix for rendered callouts.
     */
    public const DEFAULT_CLASS_PREFIX = 'callout';

    /**
     * Whether to render an HTML header inside each callout by default.
     */
    public const DEFAULT_RENDER_HEADER = true;

    /**
     * Default wrapper tag used for rendered callouts.
     */
    public const DEFAULT_WRAPPER = 'div';

    /**
     * Regex that matches a GitHub-style callout heading.
     */
    private const CALLOUT_HEADING_PATTERN = '/^\s{0,3}>\s*\[!([^\]]+)\]\s*(.*)$/i';

    /**
     * Regex that strips the blockquote prefix from a line.
     */
    private const BLOCKQUOTE_PREFIX_PATTERN = '/^\s{0,3}>\s?/';

    /**
     * Transforms GitHub-style callouts found inside blockquotes into HTML callout markup.
     *
     * @param string $text   Raw markdown content.
     * @param array<string, mixed> $config Runtime configuration overrides.
     */
    public static function transform(string $text, array $config = []): string
    {
        $config = self::mergeConfig($config);

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $normalized);
        $lineCount = count($lines);

        $result = [];
        $index = 0;

        while ($index < $lineCount) {
            if (self::isBlockquoteLine($lines[$index])) {
                [$block, $index] = self::collectBlockquote($lines, $index);
                $result[] = self::renderBlock($block, $config);
                continue;
            }

            $result[] = $lines[$index];
            $index++;
        }

        return implode("\n", $result);
    }

    /**
     * Collects contiguous blockquote lines from the current cursor position.
     *
     * @param array<int, string> $lines
     * @param int $start
     *
     * @return array{0: array<int, string>, 1: int}
     */
    private static function collectBlockquote(array $lines, int $start): array
    {
        $block = [];
        $lineCount = count($lines);

        while ($start < $lineCount && self::isBlockquoteLine($lines[$start])) {
            $block[] = $lines[$start];
            $start++;
        }

        return [$block, $start];
    }

    /**
     * Renders a single blockquote block, returning either transformed callout HTML or the original block.
     *
     * @param array<int, string> $block
     * @param array{classPrefix: string, wrapper: string, renderHeader: bool} $config
     */
    private static function renderBlock(array $block, array $config): string
    {
        if ($block === []) {
            return '';
        }

        $headingLine = ltrim($block[0]);
        if (preg_match(self::CALLOUT_HEADING_PATTERN, $headingLine, $matches) !== 1) {
            return implode("\n", $block);
        }

        $rawType = trim($matches[1]);
        $type = strtoupper($rawType);
        $titleRemainder = trim($matches[2]);

        $contentLines = [];
        if ($titleRemainder !== '') {
            $contentLines[] = $titleRemainder;
        }

        $lineTotal = count($block);
        for ($i = 1; $i < $lineTotal; $i++) {
            $contentLines[] = self::stripBlockquotePrefix($block[$i]);
        }

        $content = trim(implode("\n", $contentLines));
        $html = self::renderContent($content);

        $meta = self::buildCalloutMeta($config['classPrefix'], $type, $rawType);

        $segments = [];
        if ($config['renderHeader']) {
            $segments[] = self::renderHeader($meta);
        }

        if ($html !== '') {
            $segments[] = self::indent($html);
        }

        $content = implode("\n", array_filter($segments, static fn(string $segment): bool => $segment !== ''));

        return self::wrapContent($content, $meta, $config['wrapper']);
    }

    /**
     * Determines if the provided line belongs to a Markdown blockquote.
     */
    private static function isBlockquoteLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        return preg_match('/^\s{0,3}>/', $line) === 1;
    }

    /**
     * Strips the blockquote prefix (`>`) from a given line.
     */
    private static function stripBlockquotePrefix(string $line): string
    {
        return preg_replace(self::BLOCKQUOTE_PREFIX_PATTERN, '', $line) ?? $line;
    }

    /**
     * Renders callout content to HTML, using Kirby's Markdown when available.
     */
    private static function renderContent(string $content): string
    {
        if ($content === '') {
            return '';
        }

        $kirbyHtml = self::renderWithKirbyText($content);
        if ($kirbyHtml !== null) {
            return trim($kirbyHtml);
        }

        return self::fallbackMarkdown($content);
    }

    /**
     * Very small Markdown fallback for CLI usage when Kirby is absent.
     */
    private static function fallbackMarkdown(string $content): string
    {
        $paragraphs = preg_split('/\R{2,}/', $content) ?: [];
        $rendered = [];

        foreach ($paragraphs as $paragraph) {
            $trimmed = trim($paragraph);
            if ($trimmed === '') {
                continue;
            }

            $rendered[] = sprintf(
                '<p>%s</p>',
                htmlspecialchars($trimmed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }

        return implode("\n", $rendered);
    }

    /**
     * Indents each line in a block of HTML for readability.
     */
    private static function indent(string $html, int $level = 1): string
    {
        $indent = str_repeat('    ', $level);

        $lines = preg_split('/\R/', $html) ?: [];
        $indented = array_map(
            static fn(string $line): string => $indent . rtrim($line),
            $lines
        );

        return implode("\n", $indented);
    }

    /**
     * Merges runtime configuration with defaults.
     *
     * @param array<string, mixed> $config
     *
     * @return array{classPrefix: string, wrapper: string, renderHeader: bool}
     */
    private static function mergeConfig(array $config): array
    {
        $classPrefix = $config['classPrefix'] ?? self::DEFAULT_CLASS_PREFIX;

        if (!is_string($classPrefix)) {
            $classPrefix = self::DEFAULT_CLASS_PREFIX;
        }

        $classPrefix = trim($classPrefix);

        if ($classPrefix === '') {
            $classPrefix = self::DEFAULT_CLASS_PREFIX;
        }

        $renderHeader = array_key_exists('renderHeader', $config) ? (bool) $config['renderHeader'] : self::DEFAULT_RENDER_HEADER;
        $wrapper = $config['wrapper'] ?? self::DEFAULT_WRAPPER;
        if (!in_array($wrapper, ['div', 'blockquote'], true)) {
            $wrapper = self::DEFAULT_WRAPPER;
        }

        return [
            'classPrefix' => $classPrefix,
            'renderHeader' => $renderHeader,
            'wrapper' => $wrapper,
        ];
    }

    /**
     * Builds CSS class and metadata used for rendering the callout wrapper.
     *
     * @return array{classes: string, modifier: string, label: string, prefix: string}
     */
    private static function buildCalloutMeta(string $classPrefix, string $type, string $rawType): array
    {
        $modifier = self::typeModifier($type);

        $baseClass = $classPrefix;
        $modifierClass = sprintf('%s--%s', $classPrefix, $modifier);

        return [
            'classes' => trim(sprintf('%s %s', $baseClass, $modifierClass)),
            'modifier' => $modifier,
            'label' => self::typeLabel($rawType),
            'prefix' => $classPrefix,
        ];
    }

    /**
     * Resolves the CSS modifier for the provided callout type.
     */
    private static function typeModifier(string $type): string
    {
        $canonical = strtoupper($type);

        $slug = strtolower($canonical);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'callout';
    }

    /**
     * Wraps the rendered content using the configured tag.
     */
    private static function wrapContent(string $content, array $meta, string $wrapper): string
    {
        $classes = $meta['classes'];
        $modifier = $meta['modifier'];
        $label = htmlspecialchars($meta['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($content === '') {
            return sprintf(
                '<%1$s class="%2$s" data-callout="%3$s" data-callout-label="%4$s"></%1$s>',
                $wrapper,
                $classes,
                $modifier,
                $label
            );
        }

        return sprintf(
            "<%1\$s class=\"%2\$s\" data-callout=\"%3\$s\" data-callout-label=\"%4\$s\">\n%5\$s\n</%1\$s>",
            $wrapper,
            $classes,
            $modifier,
            $label,
            $content
        );
    }

    /**
     * Normalizes the callout label used for visual headers.
     */
    private static function typeLabel(string $rawType): string
    {
        $normalized = trim($rawType);
        if ($normalized === '') {
            return 'CALLOUT';
        }

        $normalized = preg_replace('/[\s_]+/', ' ', $normalized) ?? $normalized;

        return strtoupper($normalized);
    }

    /**
     * Renders an optional header element containing icon and label placeholders.
     */
    private static function renderHeader(array $meta): string
    {
        $label = htmlspecialchars($meta['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $modifier = htmlspecialchars($meta['modifier'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $prefix = htmlspecialchars($meta['prefix'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $headerClass = trim(sprintf('%s__header %s__header--%s', $prefix, $prefix, $modifier));
        $iconClass = trim(sprintf('%s__icon %s__icon--%s', $prefix, $prefix, $modifier));
        $labelClass = trim(sprintf('%s__label %s__label--%s', $prefix, $prefix, $modifier));

        return sprintf(
            '    <header class="%1$s" data-callout="%2$s"><span class="%3$s" aria-hidden="true"></span><span class="%4$s">%5$s</span></header>',
            $headerClass,
            $modifier,
            $iconClass,
            $labelClass,
            $label
        );
    }

    /**
     * Attempts to render content using Kirby's kirbytext helper/App instance.
     */
    private static function renderWithKirbyText(string $content): ?string
    {
        if (function_exists('kirbytext')) {
            try {
                return kirbytext($content);
            } catch (Throwable $exception) {
                // Fall through to other strategies.
            }
        }

        if (class_exists(App::class)) {
            try {
                return App::instance()->kirbytext($content);
            } catch (Throwable $exception) {
                // Fall through to fallback renderer.
            }
        }

        return null;
    }
}
