# Lemmon Callouts for Kirby

Bring GitHub-style callouts (a.k.a. admonitions) to Kirby using the familiar `[!TYPE]` blockquote syntax.  
Your editors keep writing Markdown; your site gets polished, theme-ready callouts.

## Motivation
- Kirby ships with great Markdown support, but callouts usually mean hand-written HTML or block blueprints.
- Documentation platforms such as GitHub, Svelte, and Docusaurus already rely on `[!TYPE]` callouts—let Kirby join the club.
- Works out of the box with optional themes and still respects custom callout types your team invents.

## Features
- Turn `> [!TYPE]` blocks into callout wrappers automatically.
- Supports NOTE, TIP, IMPORTANT, WARNING, CAUTION, and any custom type (e.g. `[!CHALLENGE]` → `callout-challenge`).
- Configurable wrapper (`div` or `blockquote`) and CSS class prefix.
- Optional HTML header injection for icon/label markup (on by default).
- Ships with two CSS themes (GitHub + Svelte inspired) that you can drop into your site immediately.
- Includes CLI test harness and sample fixture for quick previews outside Kirby.
- Graceful fallback when Kirby’s Markdown classes are not available (handy for CLI testing).

## Installation
1. Copy this repository into your Kirby project:  
   `site/plugins/lemmon-callouts/` → copy the plugin root here (or pull as a submodule).
2. Include the desired CSS theme in your site template or snippet:
```php
<?= css($kirby->plugin('lemmon/callouts')->asset('callouts-github.css')->url()) ?>
```
3. Keep writing Markdown as usual:
   ```markdown
   > [!TIP]
   > Add a handy tip right inside your content.
   ```

## Configuration
Set options in `site/config/config.php` if you need to customize behaviour:

```php
return [
    'lemmon.callouts.classPrefix' => 'callout',
    'lemmon.callouts.renderHeader' => true,
    'lemmon.callouts.wrapper' => 'div', // 'div' or 'blockquote'
];
```

- `classPrefix`: changing this updates both wrapper classes (`{prefix} {prefix}-{type}`) and CSS selectors.
- `renderHeader`: toggle the injected `<header>` that contains the icon span and label (themes expect this to stay on).
- `wrapper`: switch between `<div>` (default) and `<blockquote>` depending on your semantic preference.
- Any `[!TYPE]` yields classes like `callout callout-type` and data attributes `data-callout="type"` / `data-callout-label="TYPE"`.

## Styling
Two drop-in themes live under `assets/`:

| Theme | Path | Notes |
| ----- | ---- | ----- |
| GitHub | `assets/callouts-github.css` | Bold left border, label above text, colors/icons matching GitHub. |
| Svelte | `assets/callouts-svelte.css` | Floating icon badge with glassmorphism background. |

Both styles rely on CSS custom properties (colors, icon URLs, spacing). Override them in your own stylesheet to match brand guidelines:

```css
.callout--tip {
    --callout-accent: #0f766e;
    --callout-surface: #ecfdf5;
}
```

Custom callout types inherit the neutral defaults—just define selectors like `.callout-challenge` if you need bespoke colors.
The injected markup looks like:

```html
<div class="callout callout--tip" data-callout="tip" data-callout-label="TIP">
    <header class="callout__header callout__header--tip" data-callout="tip">
        <span class="callout__icon callout__icon--tip" aria-hidden="true"></span>
        <span class="callout__label callout__label--tip">TIP</span>
    </header>
    <!-- Markdown content -->
</div>
```

Use those modifier classes (`callout--tip`, `callout--note`, etc.) to fine-tune icons or typography. Disabling `renderHeader` removes the `<header>` block in case you prefer pure CSS badges.

## CLI Preview
Use the included script to test transformations without booting Kirby:

```bash
php test.php               # uses tests/sample.md
php test.php docs/intro.md # run against your own markdown file
```

If Kirby’s Markdown classes are unavailable, the plugin falls back to a simple paragraph renderer, so expect basic HTML but correct wrappers.

## License
MIT License. See `LICENSE` (add one if your project does not already include it) for details.

---

Questions, issues, or ideas? File them in the repository or reach out—this plugin is designed to be extended.
