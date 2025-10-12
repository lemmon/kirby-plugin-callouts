# AGENTS

## Repository Purpose
- Kirby CMS plugin `lemmon/callouts` that upgrades Markdown-style (KirbyText) blockquotes using GitHub callout syntax (e.g. `> [!NOTE]`).
- Parser runs before Kirby’s KirbyText pipeline so transformed callouts get styled HTML while the rest stays untouched.
- Ships with reusable renderer class, Kirby hook wiring, CLI harness, sample KirbyText/Markdown fixture, and two CSS themes.

## Layout Cheat Sheet
- `index.php` registers the Kirby plugin, loads the renderer, and passes configurable options (`classPrefix`, `wrapper`).
- `lib/callouts.php` contains `Lemmon\Callouts\Renderer`. This is the core line-by-line parser and HTML generator.
- `assets/callouts-github.css` GitHub-inspired theme (vertical accent strip + header band).
- `assets/callouts-svelte.css` Svelte-inspired theme (icon badge beside content).
- `tests/sample.md` fixture demonstrating common and custom callouts plus untouched blockquotes using KirbyText/Markdown syntax.
- `test.php` CLI utility (`php test.php [path]`) for quick transforms outside Kirby.

## Parsing Flow
1. Normalize newlines, split into an array, and iterate once.
2. Collect contiguous blockquote lines and inspect the first line for `[!TYPE]`.
3. Strip leading `>` from the block, fold back together, and run it through Kirby’s `kirbytext()` helper when available (otherwise a lightweight HTML fallback).
4. Generate wrapper metadata with BEM classes (`callout callout--{slug}`).
5. Wrap in either `<div>` or `<blockquote>` (configurable).
6. Non-callout blockquotes are returned untouched.

## Configurable Options
- `lemmon.callouts.classPrefix` — defaults to `callout`; affects both classes and data attributes.
- `lemmon.callouts.renderHeader` — defaults to `true`; injects a `<header>` with icon/label spans inside each callout.
- `lemmon.callouts.icons` — associative array of inline SVG (keyed by modifier slug, plus `default`) used inside the header.
- `lemmon.callouts.wrapper` — `'div'` (default) or `'blockquote'`.
- Parser accepts custom callout types without a whitelist; slugify fallback ensures CSS selectors exist.

## Styling Notes
- Themes rely on CSS custom properties so users can tweak colors/spacing easily.
- Inline SVG icons live in the header, inherit `currentColor`, and default to Lucide glyphs; users can override via plugin options.
- Header markup uses BEM (`callout__header`, `callout__icon`, `callout__label` + modifier variants) to simplify styling.
- Additional callout modifiers inherit base variables; the GitHub theme includes a fallback for unknown types.

## Testing & QA
- Run `php test.php` for default output (uses `tests/sample.md`).
- Use `php test.php custom.md` to check arbitrary KirbyText/Markdown snippets.
- Without Kirby present, parser falls back to a simple paragraph-only renderer. Expect limited formatting.

## Development Tips
- Maintain ASCII files; keep comments purposeful.
- When extending renderer, prefer the current Parsedown-style loop—no heavy regex passes.
- Keep the BEM class structure (`callout`, `callout--foo`, `callout__header`, etc.) stable so existing CSS keeps working.
- If adding options, thread them through `index.php` → `Renderer::transform()` → `mergeConfig()`.
- Do not remove the fallback renderer; tests rely on it when Kirby classes are absent.

## Future Ideas
- Provide label translations (locale-specific strings for callout names).
- Support custom inline labels per callout (e.g. `> [!TIP] Custom title`).
- Offer a Tailwind-friendly styling option or preset.
- Consider publishing a Kirby blueprint snippet documenting callout usage for editors.
- Add automated snapshot tests comparing rendered HTML to fixtures.

Keep this document updated when changing parser semantics, configuration keys, or shipped assets—future agents will thank you.
