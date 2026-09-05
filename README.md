# SeatLayer WordPress Seating Chart Plugin

![SeatLayer interactive seating charts](.wordpress-org/banner-1544x500.png)

[![Plugin checks](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml/badge.svg)](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/seatlayer-seating-charts.svg)](https://wordpress.org/plugins/seatlayer-seating-charts/)
[![License: MIT](https://img.shields.io/badge/license-MIT-111827.svg)](LICENSE)

The official SeatLayer plugin adds an interactive seating chart and seat picker
to WordPress so you can sell tickets with seat selection on your own site.
Buyers explore a real venue map, see live availability and prices, choose exact
seats or ask for the best seats together, and their selection is protected by a
temporary hold while they check out through your connected Stripe or Razorpay
account.

[SeatLayer plugin on WordPress.org](https://wordpress.org/plugins/seatlayer-seating-charts/) ·
[WordPress seating chart integration guide](https://docs.seatlayer.io/integrations/wordpress/) ·
[WordPress reserved-seating features](https://seatlayer.io/integrations/wordpress/) ·
[SeatLayer dashboard](https://app.seatlayer.io/) ·
[Buyer seat-map demo](https://app.seatlayer.io/demo/play/grand-theatre) ·
[SeatLayer AI Toolkit](https://github.com/seatlayer/seatlayer-ai-toolkit)

## The buyer experience

- Interactive venue maps with live seat availability
- Exact-seat selection with section, row, seat, and price details
- Best-available seating for buyers who want the closest group together
- Ticket tiers, prices, and active offers shown in the selection journey
- Temporary seat holds that protect the buyer while they check out
- Responsive controls designed for desktop and mobile screens
- Stripe or Razorpay payment through the organizer's connected account
- Email ticket confirmation after a completed order

Seat availability, holds, prices, offers, and order totals remain authoritative
on SeatLayer's servers. The WordPress page presents the experience without
duplicating ticketing state in the WordPress database.

## For organizers

- Add a chart with the **SeatLayer seating chart** block
- Use `[seatlayer_chart event="ev_..."]` in shortcode-based builders
- Select events from a dropdown after adding an optional SeatLayer secret key
- Keep using event keys without storing a secret key in WordPress
- Load the SeatLayer browser SDK only on pages that contain a seating chart
- Manage venue layouts, ticket types, pricing, offers, orders, and issued
  tickets from the SeatLayer dashboard

## Checkout choices

### Hosted checkout — default

The chart runs on the WordPress page. After choosing seats, the buyer continues
to SeatLayer's hosted buyer page to complete checkout. This requires the least
WordPress configuration and remains the automatic fallback.

### In-page checkout — optional

Enable **Let buyers pay without leaving this site** under
**Settings → SeatLayer**. Razorpay opens on the page; Stripe opens its secure
checkout and returns the buyer to the original WordPress page. The site's exact
origin must be registered as an embed domain in SeatLayer.

## Quick start

Request Managed Ticketing access for your SeatLayer organization first. Once
approved, Hosted Checkout is enabled for the account and you can create a
Managed event. See [Managed Ticketing pricing](https://seatlayer.io/pricing/)
for the service's usage charges.

1. Install and activate **SeatLayer Seating Charts** from WordPress.org.
2. Create a venue, chart, event, ticket tiers, and any offers in the
   [SeatLayer dashboard](https://app.seatlayer.io/).
3. Connect Stripe or Razorpay under **Payments**.
4. Add the **SeatLayer seating chart** block to a WordPress page.
5. Enter or select the event and publish the page.

The public chart needs only the event key. A SeatLayer secret key is optional
and is used server-side only to populate the block editor's event dropdown.

## Shortcode

```text
[seatlayer_chart event="ev_123" locale="en" currency="USD"]
```

`event` is required. `height` (320–2000, default 640) and `max_selection`
(1–50, default 10) are also accepted, and `locale` and `currency` are passed
through to the seat picker.

See [the WordPress.org readme](readme.txt) for the complete requirements,
external-service disclosures, and frequently asked questions.

## Frequently asked questions

### How do I sell tickets with seat selection on WordPress?

With Managed Ticketing access approved, install the SeatLayer plugin, create
your venue, chart, and event in the
[SeatLayer dashboard](https://app.seatlayer.io/), connect Stripe or Razorpay
under Payments, then add the **SeatLayer seating chart** block to a page and
choose the event. Buyers pick exact seats on your WordPress page, the selection
is held on SeatLayer's servers, and payment completes through your own gateway
account. No separate ticketing site or manual seat assignment is involved.

### Is there a WordPress event ticketing plugin with a seat map?

This plugin is the seat-map front end for SeatLayer's reserved-seating and
ticketing platform. WordPress renders the interactive chart, while venue
layouts, ticket tiers, pricing, offers, live availability, orders, and ticket
delivery are managed in SeatLayer. It is not a standalone seating designer and
does not store ticketing state in your WordPress database, which is what keeps
two buyers from being sold the same seat.

### Can I use my own payment provider?

Payment runs through the Stripe or Razorpay account you connect to SeatLayer,
so the money arrives in your own gateway account rather than being collected on
your behalf. SeatLayer computes the authoritative amount from its own hold
records and never accepts a price from the WordPress page. Other providers are
not supported by this plugin today; if you need a different gateway, build a
custom checkout against the
[SeatLayer holds and checkout flow](https://docs.seatlayer.io/buyer-sdk/holds-and-checkout/)
instead.

### Does this work with WooCommerce?

The plugin can run on a site that also uses WooCommerce. SeatLayer ticket
purchases use your connected Stripe or Razorpay account and do not create
WooCommerce cart line items or orders.

### Can two people buy the same seat?

No. A temporary hold reserves the buyer's selected inventory on SeatLayer's
servers during checkout. Only one hold can exist per seat. If the buyer does
not pay in time, the hold expires automatically and the seat returns to sale.
In the rare case where a payment succeeds after the hold expired, the buyer is
refunded automatically and the order is flagged in the SeatLayer dashboard.

### Do I need a SeatLayer secret key?

No. The public seating chart works with the event key alone, which is not a
secret. A secret key is optional, is stored only in your WordPress database, is
used server-side only to list your events in a dropdown in the block editor,
and is never sent to a browser. Leave it blank and paste event keys by hand if
you would rather not store it.

### Will the plugin slow down my site?

The seating chart's stylesheet, the plugin's mount script, and SeatLayer's
browser SDK load only when a page renders a chart. Pages without a chart load
no plugin assets. The SDK URL uses the `seatlayer-js@0` release channel, and
the plugin emits no inline JavaScript.

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
  as a separate browser capability alongside the 2D WordPress chart.
- [Point AI coding agents at the SeatLayer docs index](https://docs.seatlayer.io/llms.txt)
  (`llms.txt`) for an agent-readable map of the documentation.
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
experience loads SeatLayer's browser SDK from the `seatlayer-js@0` release
channel only when required.

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
