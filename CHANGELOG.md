# Changelog

## Unreleased

### Added

-   PHPUnit test suite with isolated renderer tests and optional Kirby integration test.
-   Support inline labels after `[!TYPE]` for custom callout headers.

### Changed

-   Require PHP 8.2 (aligns with Kirby baseline).

### Fixed

-   Callout parsing skips fenced code blocks.
-   Callout rendering avoids re-entering `kirbytext` hooks.

## v1.0.0 - 2025-10-13

-   Initial release.
