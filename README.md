# Space2Book for WordPress

Official WordPress plugin for Space2Book, the platform for managing meeting rooms, workspaces and bookings. It lets venue owners integrate the public Space2Book booking form into their WordPress websites.

- **Booking button** – customizable button that opens the booking form in an accessible, responsive modal.
- **Inline embed** – the booking form directly within a page.
- **Direct link** – a link to the public booking page.
- **Gutenberg block** – "Space2Book booking" (`space2book/booking`).
- **Shortcode** – `[space2book]`.
- **Multiple instances** – any number of booking elements per page, each with its own venue and language.
- **Translations** – English, Dutch, German, French and Spanish.

The plugin contains no booking logic, availability calculation or API authentication: everything is handled by Space2Book. Public booking forms never require private API keys.

## Requirements

- WordPress 6.3 or later
- PHP 7.4 or later

## Installation

1. Copy this repository into `wp-content/plugins/space2book` (or upload a release ZIP via *Plugins → Add New → Upload Plugin*).
2. Activate **Space2Book** on the Plugins screen.
3. Go to **Settings → Space2Book** and enter your public venue reference.
4. Register your website's domain for your venue in Space2Book. Space2Book enforces registered-domain restrictions on the server; embedded forms only load on registered domains.
5. Add the **Space2Book booking** block or the `[space2book]` shortcode to a page.

## Usage

### Shortcode

```text
[space2book]
[space2book venue="my-venue" mode="button" language="nl" text="Reserveer nu"]
[space2book mode="inline" height="800"]
[space2book mode="link" text="View availability"]
```

| Attribute  | Values                                   | Default (from settings) |
|------------|------------------------------------------|-------------------------|
| `venue`    | Public venue reference                   | Venue reference         |
| `mode`     | `button`, `inline`, `link`               | Default display mode    |
| `language` | `nl`, `en`, `de`, `fr`, `es`, `auto`     | Default language        |
| `text`     | Button / link text                       | Button text or "Book now" (translated) |
| `height`   | Inline height in pixels (300–3000)       | Inline form height      |

`lang` is accepted as an alias of `language`. `auto` follows the site language and falls back to English.

### Block

Insert **Space2Book booking** from the *Widgets* category. Venue, display mode, language, button text and inline height can be set in the block sidebar; empty values use the plugin settings.

### PHP (themes)

```php
echo \Space2Book\Plugin::boot()->renderer()->render( array( 'mode' => 'link' ) );
```

## Display modes and accessibility

- **Button**: rendered as a normal link to the booking page, so it works without JavaScript. The frontend script enhances it into a button (`role="button"`, `aria-haspopup="dialog"`, Enter and Space) that opens a native `<dialog>` modal. Focus moves to the close button, is kept within the dialog, Escape and backdrop clicks close it, and focus returns to the button that opened it. The iframe is only loaded when the modal opens.
- **Inline**: a lazy-loaded iframe with a title and a fallback link to the booking page.
- **Link**: a plain link, optionally opening in a new tab (announced to screen readers). No JavaScript is loaded.

Assets are only enqueued on pages that contain a booking element.

## SDK integration adapter

The official Space2Book Embed SDK and secure iframe architecture are developed separately. Until they are published, the plugin uses a built-in iframe driver and a **placeholder URL template**. All integration points are in [`includes/class-sdk-adapter.php`](includes/class-sdk-adapter.php) and [`assets/js/space2book.js`](assets/js/space2book.js).

### PHP

| Integration point | Type | Purpose |
|---|---|---|
| `SPACE2BOOK_BOOKING_URL_TEMPLATE` | constant | URL template of the public booking page. Placeholders: `{venue}`, `{language}`. Default placeholder: `https://app.space2book.com/book/{venue}?lang={language}` |
| `space2book_booking_url_template` | filter | Same as above, at runtime. |
| `space2book_booking_url` | filter | Final booking URL (direct links and no-JS fallback). |
| `space2book_embed_url` | filter | URL loaded in the modal and inline iframe. |
| `SPACE2BOOK_SDK_URL` / `space2book_sdk_script_url` | constant / filter | HTTPS URL of the official SDK script. Empty by default; when set, it is loaded (deferred) before the plugin script. |
| `space2book_script_config` | filter | Data passed to the frontend as `window.space2bookConfig`. |
| `space2book_iframe_attributes` | filter | Attributes of the inline iframe. |
| `space2book_render_html` | filter | Final HTML of a booking element. |

Example `wp-config.php`:

```php
define( 'SPACE2BOOK_BOOKING_URL_TEMPLATE', 'https://booking.example.com/{language}/{venue}' );
define( 'SPACE2BOOK_SDK_URL', 'https://cdn.example.com/space2book-embed.js' );
```

### JavaScript

The frontend script exposes `window.Space2BookWP` and dispatches a `space2book:ready` event on `document`. An SDK can take over by registering a driver; return `true` when the driver handled the request, otherwise the built-in iframe driver is used:

```js
window.Space2BookWP.registerDriver( {
	openModal( context ) {
		// context: { venue, language, url, element }
		return false;
	},
	mountInline( element, context ) {
		return false;
	},
} );
```

`Space2BookWP.init( root )` re-scans dynamically added content; `open( element )` and `close()` control the modal.

Booking elements expose `data-space2book-venue`, `data-space2book-language` and `data-space2book-mode` (`modal` or `inline`) attributes.

## Development

```bash
composer install
composer lint      # WordPress Coding Standards (phpcs.xml.dist)
composer lint:fix  # Auto-fix coding standard issues
composer test      # PHPUnit unit tests
```

The block editor script is plain JavaScript using the `wp.*` globals, so no build step is needed.

### Translations

The text domain is `space2book`; translations live in [`languages/`](languages). After changing strings, regenerate them with [WP-CLI](https://wp-cli.org/):

```bash
wp i18n make-pot . languages/space2book.pot --exclude=vendor,tests
wp i18n update-po languages/space2book.pot languages
wp i18n make-mo languages
wp i18n make-json languages --no-purge
```

### Project structure

```text
space2book.php            Plugin bootstrap (header, constants, hooks)
uninstall.php             Removes plugin options on uninstall
includes/
  class-plugin.php        Wires all components together
  class-lifecycle.php     Activation / deactivation
  class-settings.php      Option defaults and sanitization
  class-admin.php         Settings → Space2Book page
  class-sdk-adapter.php   URLs, assets and SDK integration points
  class-renderer.php      Button, inline and link markup
  class-shortcode.php     [space2book] shortcode
  class-block.php         space2book/booking block registration and rendering
blocks/booking/           block.json and editor script
assets/                   Frontend JavaScript and CSS
languages/                POT, PO, MO and JSON translation files
tests/                    PHPUnit unit tests
```

## Versioning

This project uses [Semantic Versioning](https://semver.org/). See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later
