# SeatLayer for WordPress

![SeatLayer interactive seating charts](.wordpress-org/banner-1544x500.png)

[![Plugin checks](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml/badge.svg)](https://github.com/seatlayer/seatlayer-wordpress/actions/workflows/ci.yml)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/seatlayer-seating-charts.svg)](https://wordpress.org/plugins/seatlayer-seating-charts/)
[![License: MIT](https://img.shields.io/badge/license-MIT-111827.svg)](LICENSE)

The official SeatLayer plugin brings interactive reserved seating to WordPress.
Buyers can explore a real venue map, see current availability and prices, choose
exact seats or ask for the best seats together, and continue to secure checkout.

[Install from WordPress.org](https://wordpress.org/plugins/seatlayer-seating-charts/) ·
[Read the integration guide](https://seatlayer.io/integrations/wordpress) ·
[Open the SeatLayer dashboard](https://app.seatlayer.io/)

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

See `readme.txt` for the complete WordPress.org description, requirements,
external-service disclosures, and frequently asked questions.

## Development

The plugin intentionally has no runtime Composer or npm dependency. WordPress's
HTTP layer handles the small server-side API surface, while the public seating
experience loads SeatLayer's version-pinned browser SDK only when required.

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

## Support and links

- [WordPress plugin](https://wordpress.org/plugins/seatlayer-seating-charts/)
- [SeatLayer documentation](https://docs.seatlayer.io/start/choose-an-integration/)
- [WordPress integration](https://seatlayer.io/integrations/wordpress)
- [SeatLayer platform](https://seatlayer.io/)
- [Issue tracker](https://github.com/seatlayer/seatlayer-wordpress/issues)

## License

[MIT](LICENSE) © SeatLayer
