# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-10-27

### Added

- Plugin bootstrap with activation, deactivation and uninstall handling.
- Settings → Space2Book admin page: public venue reference, default language,
  default display mode, inline height, direct-link behaviour and button appearance.
- Three display modes: booking button that opens an accessible modal dialog,
  inline embed and direct link to the public booking page.
- `[space2book]` shortcode with `venue`, `mode`, `language` (alias `lang`), `text`
  and `height` attributes.
- Dynamic "Space2Book booking" block (`space2book/booking`) without a build step.
- Support for multiple booking elements on the same page.
- SDK integration adapter (`Space2Book\Sdk_Adapter` and `window.Space2BookWP.registerDriver()`)
  with configurable URL template placeholder, filters and constants.
- Translations for Dutch, German, French and Spanish (English is the source language).
- PHPUnit unit tests, WordPress Coding Standards configuration and a GitHub Actions workflow.

[Unreleased]: https://github.com/Space2Book/space2book_wordpress/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/Space2Book/space2book_wordpress/releases/tag/v0.1.0
