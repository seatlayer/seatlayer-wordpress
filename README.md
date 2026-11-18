# SeatLayer WordPress Seating Chart Plugin

![SeatLayer interactive seating charts](.wordpress-org/banner-1544x500.png)

[![Plugin checks](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml/badge.svg)](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/seatlayer-seating-charts.svg)](https://wordpress.org/plugins/seatlayer-seating-charts/)
[![License: MIT](https://img.shields.io/badge/license-MIT-111827.svg)](LICENSE)

The official WordPress seating chart plugin for SeatLayer. It adds interactive
seating charts and seat maps to WordPress for event ticket sales with reserved
seats. Buyers choose exact seats or find a group with Best Available, explore 3D for venues up to 30,000 seats on phones and 60,000 on desktop on supported devices, and pay through SeatLayer's
checkout, with ticket money paid to the organizer's own account. SeatLayer is
seating chart and reserved-seat ticketing software built for venues up to
stadium scale.

Create theatre, auditorium, concert and dinner-show layouts in SeatLayer, then
publish them on WordPress with a Gutenberg block or shortcode.

[SeatLayer plugin on WordPress.org](https://wordpress.org/plugins/seatlayer-seating-charts/) ·
[WordPress seating chart integration guide](https://docs.seatlayer.io/integrations/wordpress/) ·
[WordPress reserved-seating features](https://seatlayer.io/integrations/wordpress/) ·
[SeatLayer dashboard](https://app.seatlayer.io/) ·
[Buyer seat-map demo](https://app.seatlayer.io/demo/play/grand-theatre) ·
[All live demos](https://seatlayer.io/demo/)

**Start here:** [Quickstart](https://docs.seatlayer.io/start/quickstart/) · [Holds and checkout](https://docs.seatlayer.io/buyer-sdk/holds-and-checkout/) · [WordPress guide](https://docs.seatlayer.io/integrations/wordpress/) · [SDK catalog](https://docs.seatlayer.io/sdk-catalog.json) · [Pricing](https://seatlayer.io/pricing/): $0.50 per ticket, your first 25 free.

## Scale evidence

Benchmarked on public 100,000-, 150,000- and 200,000-seat venue fixtures on 15 September 2026: 200,000 seats chart-ready in 1.95 s, desktop, local production build. Tested at 10,000 simulated concurrent buyers on one 12,000-seat event (18 September 2026): engine-side hold latency p99 9 ms, zero server errors. Method, fixtures and run files: https://github.com/seatlayer/seatlayer-performance. Live 200,000-seat stadium demo: https://app.seatlayer.io/demo/play/century-stadium-200k.

## The buyer experience

- Interactive venue maps with live seat availability
- Exact-seat selection with section, row, seat, and price details
- Reserved rows, tables, booths and mixed general-admission areas
- Accessible-seat labels and floor navigation from the published chart
- Best-available seating for buyers who want a suitable group together
- Interactive 3D with the view from each seat, for venues up to 30,000 seats on phones and 60,000 on desktop,
  on supported browsers and devices, with the complete 2D picker as
  fallback
- Ticket tiers, prices, and active offers shown in the selection journey
- Temporary seat holds that protect the buyer while they check out
- Responsive controls designed for desktop and mobile screens
- Chart branding and category colours configured in SeatLayer
- Payment into the organizer's own account, through one of SeatLayer's 14 payment gateways
- Email ticket confirmation after a completed order

Seat availability, holds, prices, offers, and order totals remain authoritative
on SeatLayer's servers. The WordPress page presents the experience without
duplicating ticketing state in the WordPress database.

See [interactive 3D seat views](https://seatlayer.io/3d-seat-map/) and
[best-available seat selection](https://docs.seatlayer.io/buyer-sdk/best-available/)
for the buyer workflows. The same seat map has a public
[200,000-seat stadium demo](https://app.seatlayer.io/demo/play/century-stadium-200k)
with [published measurements](https://docs.seatlayer.io/platform/renderer-performance/);
evaluate your own chart, WordPress theme and target devices before opening sales.

## For organizers

- Add a chart with the **SeatLayer seating chart** block, including wide/full
  alignment where the WordPress theme supports it
- Use `[seatlayer_chart event="ev_..."]` in shortcode-based builders
- Select events from a dropdown after adding an optional SeatLayer secret key
- Keep using event keys without storing a secret key in WordPress
- Load the SeatLayer browser SDK only on pages that contain a seating chart
- Manage venue layouts, ticket types, pricing, offers, orders, and issued
  tickets from the SeatLayer dashboard

## Checkout choices

### Hosted checkout (default)

The chart runs on the WordPress page. After choosing seats, the buyer continues
to SeatLayer's hosted buyer page to complete checkout. This requires the least
WordPress configuration and remains the automatic fallback.

### In-page checkout (optional)

Enable **Let buyers pay without leaving this site** under
**Settings → SeatLayer** once in-page checkout is on for your SeatLayer account;
until then the setting changes nothing and buyers use hosted checkout. Some
gateways collect payment on the page itself; a gateway that opens its own secure
checkout returns the buyer to the same WordPress page. Add your site's address
once in SeatLayer so the buyer returns to your page.

## Quick start

Setup is self-serve with no approval step. The plugin is free; SeatLayer charges
$0.50 per confirmed ticket, with your first 25 tickets free and no subscription.
See [pricing](https://seatlayer.io/pricing/).

1. Install and activate **SeatLayer Seating Charts** from WordPress.org.
2. Create a venue, chart, event, ticket tiers, and any offers in the
   [SeatLayer dashboard](https://app.seatlayer.io/).
3. Set up payments under **Payments**. SeatLayer supports 14 payment gateways,
   including Stripe, PayPal and Square, and ticket money is paid to your own
   account ([all 14](https://seatlayer.io/payments/)).
4. Add the **SeatLayer seating chart** block to a WordPress page.
5. Enter or select the event and publish the page.

The public chart needs only the event key. A SeatLayer secret key is optional
and is used server-side only to populate the block editor's event dropdown.

## Shortcode

```text
[seatlayer_chart event="ev_123" locale="en" currency="USD"]
```

`event` is required. `height` (320 to 2000, default 640) and `max_selection`
(1 to 50, default 10) are also accepted, and `locale` and `currency` are passed
through to the seat picker. Currency is a display fallback; event prices remain
authoritative.

See [the WordPress.org readme](readme.txt) for the complete requirements,
external-service disclosures, and frequently asked questions.

## Frequently asked questions

### How do I sell tickets with seat selection on WordPress?

Install the free SeatLayer plugin, set up your venue and event in your SeatLayer
account, and add the SeatLayer seating chart block or the `[seatlayer_chart]`
shortcode to any page. Buyers pick exact seats or a group with Best Available,
can look at the venue in 3D for venues up to 30,000 seats on phones and 60,000 on desktop, and keep their seats
on hold while they pay. Checkout supports 14 payment gateways, including Stripe,
PayPal and Square, and ticket money is paid to your own account. SeatLayer
charges $0.50 per ticket, with your first 25 free.

### Is there a WordPress event ticketing plugin with a seat map?

This plugin is the seat-map front end for SeatLayer's reserved-seating and
ticketing platform. WordPress renders the interactive chart, while venue
layouts, ticket tiers, pricing, offers, live availability, orders, and ticket
delivery are managed in SeatLayer. Venue design stays in the SeatLayer
dashboard, and authoritative server-side holds prevent overlapping seat sales.

### Can I use my own payment provider?

Yes. SeatLayer supports 14 payment gateways, including Stripe, PayPal and
Square, and ticket money is paid to your own account rather than collected on
your behalf. SeatLayer works out the amount from its own hold records and never
accepts a price from the WordPress page. Need a gateway that isn't listed?
Request it from SeatLayer.

### Does this work with WooCommerce?

The plugin can run on a site that also uses WooCommerce. SeatLayer ticket
purchases run through the organizer's own payment account and do not create
WooCommerce cart line items or orders.

### Can two people buy the same seat?

No. A temporary hold gives each selected seat to one buyer while they check
out, and only one hold can exist per seat. If the buyer does not pay in time,
the hold expires and the seat returns to sale. If a payment arrives after its
hold expired and the seats can no longer be booked, the buyer is refunded
automatically and the order is flagged in your SeatLayer dashboard.

### Do I need a SeatLayer secret key?

No. The public seating chart works with the event key alone, which is not a
secret. A secret key is optional, is stored only in your WordPress database, is
used server-side only to list your events in a dropdown in the block editor,
and is never sent to a browser. Leave it blank and paste event keys by hand if
you would rather not store it.

### Will the plugin slow down my site?

The seating chart's stylesheet, the plugin's mount script, and SeatLayer's
browser SDK load only when a page renders a chart. Pages without a chart load
no plugin assets. This release pins the SDK to `seatlayer-js@0.92.5`, and the
plugin emits no inline JavaScript.

## Continue your WordPress integration

- [Read the WordPress seating chart integration guide](https://docs.seatlayer.io/integrations/wordpress/)
  for setup, block usage, and checkout configuration on a live site.
- [Choose the right SeatLayer integration](https://docs.seatlayer.io/start/choose-an-integration/)
  before deciding between the WordPress plugin, a hosted event page, and a
  custom checkout.
- [Connect seat holds to secure server-side checkout](https://docs.seatlayer.io/buyer-sdk/holds-and-checkout/)
  when you need a gateway or cart the plugin does not cover.
- [Run the complete checkout example](https://docs.seatlayer.io/examples/complete-checkout/)
  to see a buyer hold id connected to payment and idempotent booking.
- [Compare SeatLayer's mobile seat map SDKs](https://docs.seatlayer.io/buyer-sdk/mobile/)
  when the same events also need a React Native, Flutter, iOS, or Android app.
- [Explore the 3D seating chart for web buyers](https://seatlayer.io/3d-seat-map/)
  available through the picker on supported browsers and devices.
- Use the [developer integration toolkit](https://github.com/seatlayer/seatlayer-ai-toolkit)
  and [agent-readable documentation index](https://docs.seatlayer.io/llms.txt)
  when building a custom SeatLayer integration.
- [Report a plugin issue](https://github.com/seatlayer/seatlayer-wordpress/issues)
  or ask a question about the WordPress integration.

## SeatLayer SDK ecosystem

| Surface | Package or source |
| --- | --- |
| WordPress | [`seatlayer-seating-charts`](https://wordpress.org/plugins/seatlayer-seating-charts/) (this plugin) |
| JavaScript | [`@seatlayer/js`](https://www.npmjs.com/package/@seatlayer/js) |
| React | [`@seatlayer/react`](https://www.npmjs.com/package/@seatlayer/react) |
| Vue | [`@seatlayer/vue`](https://www.npmjs.com/package/@seatlayer/vue) |
| Angular | [`@seatlayer/angular`](https://www.npmjs.com/package/@seatlayer/angular) |
| React Native | [`@seatlayer/react-native`](https://www.npmjs.com/package/@seatlayer/react-native) |
| iOS | [`seatlayer-ios`](https://github.com/seatlayer/seatlayer-ios) |
| Flutter | [`seatlayer`](https://pub.dev/packages/seatlayer) |
| Android | [`seatlayer-android`](https://github.com/seatlayer/seatlayer-android) |
| Server SDKs | [Node.js, Python, PHP, Ruby, .NET, Java, and Go](https://docs.seatlayer.io/server-sdk/install/) |

## Development

The plugin intentionally has no runtime Composer or npm dependency. WordPress's
HTTP layer handles the small server-side API surface, while the public seating
experience loads the exact `seatlayer-js@0.92.5` browser SDK release only when
required. Update `SEATLAYER_SDK_VERSION` and the documented CDN URL together
after verifying the new release.

Run the focused local checks:

```sh
for file in seatlayer.php uninstall.php includes/*.php; do php -l "$file"; done
node --check assets/js/frontend.js
node --check assets/js/block.js
php tests/settings-logic.php
node tests/frontend-options.js
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidance and
[RELEASE.md](RELEASE.md) for the release procedure.

## Releases

GitHub is the source of truth. Publishing a non-prerelease GitHub Release runs
the focused checks, validates that the release tag matches the plugin and stable
versions, deploys that tag to the required WordPress.org SVN repository, and
attaches the installable ZIP to the GitHub Release.

WordPress.org still uses SVN behind the scenes; maintainers do not need to run
the normal SVN synchronization manually.

## License

[MIT](LICENSE) © SeatLayer
