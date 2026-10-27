=== Space2Book ===
Contributors: space2book
Tags: booking, reservations, meeting rooms, workspace, booking form
Requires at least: 6.3
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add the public Space2Book booking form to your website as a booking button, an inline form or a direct link.

== Description ==

Space2Book is a platform for managing meeting rooms, workspaces and bookings. This plugin lets venue owners integrate their public Space2Book booking form into a WordPress website.

* **Booking button** – a customizable button that opens the booking form in an accessible, responsive modal dialog.
* **Inline form** – show the booking form directly on a page.
* **Direct link** – link visitors to your public booking page.
* **Block** – the "Space2Book booking" block for the block editor.
* **Shortcode** – `[space2book]` for classic content, widgets and page builders.
* **Multiple instances** – place as many booking buttons on a page as you need, each for its own venue and language.
* **Languages** – the plugin is available in English, Dutch, German, French and Spanish, and the booking form language can follow your site language.

All bookings, availability and payments are handled by Space2Book. The plugin does not need, and never asks for, API keys.

= Shortcode =

`[space2book venue="my-venue" mode="button" language="nl" text="Reserveer nu"]`

* `venue` – public venue reference. Defaults to the venue from the settings.
* `mode` – `button` (modal), `inline` or `link`.
* `language` – `nl`, `en`, `de`, `fr`, `es` or `auto` (site language).
* `text` – button or link text.
* `height` – height of the inline form in pixels.

= Registered domains =

Space2Book only allows its booking form to be embedded on domains registered for your venue. Register your website domain in your Space2Book account before using the button or inline modes.

= External service =

This plugin embeds and links to the public booking form hosted by Space2Book. When a visitor views a page with an inline form, or opens the booking button, their browser loads the booking form from Space2Book. No data is sent to Space2Book by the plugin itself. See the Space2Book terms of service and privacy policy on the Space2Book website.

== Installation ==

1. Upload the `space2book` folder to `/wp-content/plugins/`, or install the plugin through the Plugins screen.
2. Activate the plugin through the Plugins screen.
3. Go to Settings → Space2Book and enter the public venue reference shown in your Space2Book account.
4. Add the "Space2Book booking" block or the `[space2book]` shortcode to a page.

== Frequently Asked Questions ==

= Do I need an API key? =

No. The plugin only uses the public booking form.

= The embedded form does not load. =

Make sure your website domain is registered for your venue in Space2Book. The direct link mode works on any domain.

= Can I show booking buttons for different venues on one page? =

Yes. Set a different venue reference on each block or shortcode.

== Changelog ==

= 0.1.0 =
* Initial release: booking button with modal, inline form, direct link, block, shortcode, settings page and SDK integration adapter.
