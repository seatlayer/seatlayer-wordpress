=== SeatLayer Seating Charts ===
Contributors: navincse
Tags: seating chart, seat map, seat selection, event tickets, reserved seating
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.2.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Sell reserved event tickets with interactive seat maps, 3D seat views and live availability. Add charts to WordPress by block or shortcode.

== Description ==

SeatLayer is a WordPress seating chart plugin for selling event tickets with
reserved seats. Add an interactive seat map to a page or post, let visitors
choose where they sit, and accept payment through your connected Stripe or
Razorpay account.

Build theatre, auditorium, concert and dinner-show seating plans in SeatLayer,
then publish them on WordPress with a Gutenberg block or shortcode.

[Explore the WordPress seating chart features](https://seatlayer.io/integrations/wordpress/) ·
[Try the interactive seat selection demo](https://app.seatlayer.io/demo/play/grand-theatre)

= Interactive seat maps and reserved seating =

* **Numbered seats and venue sections.** Show row, seat, section, price and
  availability for exact-seat selection.
* **Tables, booths and general-admission areas.** Combine reserved rows with
  other seating and capacity areas in one published venue chart.
* **Accessible seating and multiple floors.** Show your configured seat labels
  and floor layouts.
* **Best Available for groups.** Find a suitable group together using
  [best-available seat selection](https://docs.seatlayer.io/buyer-sdk/best-available/).

= 3D seat views and mobile seat selection =

Buyers can switch between the map and **interactive 3D** on supported browsers
and devices, with a full 2D fallback. Explore SeatLayer's
[3D seating charts](https://seatlayer.io/3d-seat-map/).

The responsive picker adapts to the space on your page, with touch selection,
pinch-to-zoom and a mobile selection summary. Chart branding and category
colours come from your SeatLayer venue configuration.

= Live availability, ticket types and checkout holds =

* **Live seat availability** updates as inventory changes.
* **Ticket types and category pricing** show the options configured for the
  event, such as Adult, Child or Senior tickets.
* **Active event offers** appear in the buyer journey when configured in
  SeatLayer.
* **Temporary seat holds** protect the selected inventory during checkout,
  with a countdown and automatic release when a hold expires.
* **Confirmed bookings and ticket emails** follow successful payment through
  the organizer's connected account.

SeatLayer manages inventory, payment totals and bookings centrally, without
duplicating ticket inventory in your WordPress database.

= Gutenberg block and seating chart shortcode =

Add the **SeatLayer seating chart** block in the WordPress editor, or use:

`[seatlayer_chart event="your-event" height="720" max_selection="6"]`

Set the event, height, maximum seat selection, `locale` and `currency`.
The block supports wide/full alignment where your theme provides it. Currency
is a display fallback; the event's configured prices remain authoritative.

Enter an event key or use an optional server-side secret key for an event
dropdown. Scripts and styles load only on pages containing a chart.

See the [WordPress seat map setup guide](https://docs.seatlayer.io/integrations/wordpress/)
for block settings and shortcode options.

= Accept payments through Stripe or Razorpay =

**Hosted checkout:** buyers continue from seat selection to SeatLayer's hosted
event page and pay through your connected account.

**Checkout from your WordPress page:** enable **Let buyers pay without leaving
this site** under **Settings → SeatLayer**. Razorpay opens on the page; Stripe
opens its secure checkout and returns the buyer to the WordPress page.
This needs in-page checkout access and a registered embed domain. Hosted
checkout remains the fallback.

Manage venue layouts, events, ticket prices, offers, orders and ticket delivery
in the [SeatLayer dashboard](https://app.seatlayer.io/).

= Account requirements and service pricing =

You need an approved SeatLayer Managed Ticketing account, a published
reserved-seat chart, a Managed event and a connected payment account.
Request Managed access before opening sales.

The WordPress plugin is free to install. SeatLayer's hosted service has usage
charges, and your payment provider has its own fees. See
[Managed Ticketing pricing](https://seatlayer.io/pricing/) for current rates.

== Installation ==

1. Install and activate **SeatLayer Seating Charts**.
2. Request Managed Ticketing access in SeatLayer. Once approved, create and
   publish a venue chart, then create your Managed event.
3. Connect Stripe or Razorpay under **Payments** in the SeatLayer dashboard.
4. Add the **SeatLayer seating chart** block or `[seatlayer_chart]` shortcode
   to a WordPress page and enter your event key.
5. Check the seating chart and checkout flow, then publish your event page.

A secret key under **Settings → SeatLayer** is optional. It enables the
editor's event dropdown; the public chart works with the event key alone.

== Frequently Asked Questions ==

= Can I create the seating plan inside WordPress? =

Create and edit venues in the [seating chart designer](https://seatlayer.io/venue-designer/),
then embed the published chart on WordPress. The plugin provides the buyer
picker and display settings.

= Can I use table seating or general admission? =

Yes, within a published chart alongside reserved seats. A pure-GA event
without a chart uses a different ticketing flow.

= Can I use it for a large theatre, arena or stadium? =

The plugin uses SeatLayer's shared buyer renderer. Try the public
[53,018-seat stadium demo](https://app.seatlayer.io/demo/play/large-stadium)
and read the [large-chart renderer measurements](https://docs.seatlayer.io/platform/renderer-performance/).
These measure the shared renderer; also evaluate your own chart, WordPress
theme and target devices before opening sales.

= Does this integrate with WooCommerce checkout? =

It can run on a site that also uses WooCommerce. SeatLayer ticket purchases use
your connected Stripe or Razorpay account and do not create WooCommerce cart
line items or orders.

= Can two buyers purchase the same seat? =

Only one active hold can own a seat; expired holds release inventory for sale.
If payment completes after a hold expires and booking fails, SeatLayer refunds
the buyer automatically and flags the order.

= Where is my optional secret key stored? =

In your WordPress database, used server-side and never sent to visitors.
Database administrators can read it. Leave it blank and enter event keys
directly if you do not want to store it.

= Can I build a custom seat booking integration? =

Developers who need their own buyer controls, cart or payment flow can use the
[JavaScript seat map SDK](https://docs.seatlayer.io/buyer-sdk/install/) and
[SeatLayer seat booking API](https://seatlayer.io/developers/).
The plugin provides the Managed Ticketing flow described above.

== External services ==

This plugin connects to SeatLayer's hosted seating and ticketing service. It
requires a SeatLayer account. These services are contacted during use:

**SeatLayer CDN (`cdn.seatlayer.io`)**

Loads the seating-chart renderer only on pages containing a chart, from
`https://cdn.seatlayer.io/seatlayer-js@0.80.3/seatlayer.js`.
The browser sends its normal IP address, user agent and referrer; the plugin
adds no visitor data to that script request.

**SeatLayer API (`api.seatlayer.io`)**

On chart load, fetches the layout and live availability. During selection and
checkout, creates seat holds and payment sessions. Requests include the event
key and selected inventory; payment also needs the buyer's email and optional
name. Payment starts with the hold ID, and SeatLayer computes the amount from
its records. Optional in-page checkout sends the page URL for a return to a
registered embed domain.

If an administrator saves a secret key, the WordPress server uses it to list
events in the block editor. That key is not sent to visitors.

**SeatLayer hosted buyer page (`app.seatlayer.io`)**

For hosted checkout, redirects the buyer to
`https://app.seatlayer.io/e/EVENT?hold=HOLD_ID` after seat selection.
The URL carries the event key and hold ID, not an amount or buyer details.

**Stripe or Razorpay**

After seat selection, processes payment through the organizer's connected
account. SeatLayer sends the amount, currency and buyer email needed for
payment. Card details go directly to the payment provider and never pass
through this plugin or your WordPress database.

[Stripe Privacy Policy](https://stripe.com/privacy) ·
[Razorpay Privacy Policy](https://razorpay.com/privacy) ·
[SeatLayer Terms of Service](https://seatlayer.io/terms) ·
[SeatLayer Privacy Policy](https://seatlayer.io/privacy)

== Changelog ==

= 0.2.3 =
* Detail seating chart features, 3D seat views, WordPress controls and setup answers.
* Add relevant demo, feature and developer documentation links.
* Pin the buyer SDK CDN to the verified 0.80.3 release.

= 0.2.2 =
* Refresh the plugin listing with clearer seating chart and seat map setup guidance.
* Document the Managed Ticketing account requirement and link the WordPress integration guide.
* Clarify checkout holds, WooCommerce coexistence, and browser SDK loading.
* Documentation and release metadata only; plugin behavior is unchanged.

= 0.2.1 =
* Documentation only. Refreshes the plugin listing text and the project
  README. No plugin behaviour changes.

= 0.2.0 =
* Add optional in-page checkout with hosted-checkout fallback.
* Return Stripe buyers to the registered WordPress page after payment.
* Remove plugin settings on uninstall and translate chart error messages.
* Document external services and data sent.

= 0.1.0 =
* First release: seating chart block, shortcode and hosted payment handoff.
