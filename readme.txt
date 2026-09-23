=== SeatLayer Seating Charts ===
Contributors: navincse
Tags: seating chart, ticketing, event tickets, reserved seating, seat map
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.2.6
License: MIT
License URI: https://opensource.org/licenses/MIT

Free WordPress seating chart and ticketing plugin. Buyers pick exact seats and pay through your own gateway. Block or shortcode, stadium scale.

== Description ==

SeatLayer is a WordPress seating chart plugin for selling event tickets with
reserved seats. Add an interactive seat map to a page or post, let visitors
choose where they sit, and accept payment through your own connected payment
gateway.

Build theatre, auditorium, concert and dinner-show seating plans in SeatLayer,
then publish them on WordPress with a Gutenberg block or shortcode.

[Explore the WordPress seating chart features](https://seatlayer.io/integrations/wordpress/) ·
[Try the interactive seat selection demo](https://app.seatlayer.io/demo/play/grand-theatre)

= Scale evidence =

Tested on public venue files in September 2026: a 200,000-seat stadium is
ready to browse in 1.95 s, and one event handled 10,000 buyers at once with a
9 ms seat hold (p99) and zero server errors. Method and every run:
https://github.com/seatlayer/seatlayer-performance ·
Try the live stadium demo: https://app.seatlayer.io/demo/play/century-stadium-200k

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

= Accept payments through your own payment gateway =

**Hosted checkout:** buyers continue from seat selection to SeatLayer's hosted
event page and pay through your own connected payment gateway.

**Checkout from your WordPress page:** enable **Let buyers pay without leaving
this site** under **Settings → SeatLayer**. In-page gateways collect payment on
the page itself; a redirecting gateway opens its own secure checkout and returns
the buyer to the WordPress page. Add your site's address once in SeatLayer so
the buyer returns to your page. Hosted checkout remains the fallback.

Supported gateways: Stripe, PayPal, Razorpay, Tap, Mercado Pago, Xendit and
Flutterwave. Ticket money goes straight to your own account.

Manage venue layouts, events, ticket prices, offers, orders, refunds and ticket
delivery in the [SeatLayer dashboard](https://app.seatlayer.io/). Buyers get a
QR code and printable PDF ticket for every seat, and you can check tickets in at
the door from a phone browser.

= Pricing =

The plugin is free. Selling is self-serve with no approval step: you need a free
SeatLayer account, a published seating chart, an event and a connected payment
gateway. SeatLayer charges $0.50 per confirmed ticket, and your first 25 tickets
are free. There is no subscription and no percentage of the ticket price; your
payment gateway charges its own processing fee. See
[pricing](https://seatlayer.io/pricing/).

== Installation ==

1. Install and activate **SeatLayer Seating Charts**.
2. Sign up free at [SeatLayer](https://app.seatlayer.io/), create and publish
   a venue chart, then create your event.
3. Connect your payment gateway (Stripe, PayPal, Razorpay, Tap, Mercado Pago,
   Xendit or Flutterwave) under **Payments** in the SeatLayer dashboard.
4. Add the **SeatLayer seating chart** block or `[seatlayer_chart]` shortcode
   to a WordPress page and enter your event key.
5. Check the seating chart and checkout flow, then publish your event page.

A secret key under **Settings → SeatLayer** is optional. It enables the
editor's event dropdown; the public chart works with the event key alone.

== Frequently Asked Questions ==

= Is the plugin free? =

Yes. The plugin is free and MIT licensed. SeatLayer charges $0.50 per confirmed
ticket, your first 25 tickets are free, and there is no subscription.

= Is this a WordPress seating chart plugin or a full ticketing plugin? =

Both, for seated events. WordPress renders the interactive seating chart and
the seat picker, and the buyer completes checkout through your own connected
payment gateway, so ticket money arrives in your own gateway account. Venue
layouts, inventory, holds, orders and ticket delivery stay in SeatLayer, which
bills its hosted-ticket usage separately from your gateway's fees.

= Does it work with Elementor and other page builders? =

Yes. Use the block in the block editor, or paste the `[seatlayer_chart]`
shortcode into any builder or classic editor that accepts shortcodes, including
Elementor.

= Which payment gateways can buyers use? =

Stripe, PayPal, Razorpay, Tap, Mercado Pago, Xendit and Flutterwave, connected
to your own account. Need another gateway? Request it from SeatLayer.

= Do buyers get e-tickets with QR codes? =

Yes. Each buyer gets a confirmation email with a QR code and printable PDF
ticket for every seat, and you can scan tickets at the door from a phone
browser.

= Can I sell tickets for a stadium or arena from WordPress? =

Yes. Stadium and arena charts use the same buyer renderer as the published
large-venue benchmark, embedded on your own WordPress page. See
[arena and stadium seating charts](https://seatlayer.io/arenas-stadiums/), and
evaluate your own chart, WordPress theme and target devices before opening
sales.

= Can I create the seating plan inside WordPress? =

Create and edit venues in the [seating chart designer](https://seatlayer.io/venue-designer/),
then embed the published chart on WordPress. The plugin provides the buyer
picker and display settings.

= Can I use table seating or general admission? =

Yes, within a published chart alongside reserved seats. A pure-GA event
without a chart uses a different ticketing flow.

= Can I use it for a large theatre, arena or stadium? =

The plugin uses SeatLayer's shared buyer renderer. Read the
[large-chart renderer measurements](https://docs.seatlayer.io/platform/renderer-performance/)
for what that renderer does on big venue charts. Those measure the shared
renderer; also evaluate your own chart, WordPress theme and target devices
before opening sales.

= Does this integrate with WooCommerce checkout? =

It can run on a site that also uses WooCommerce. SeatLayer ticket purchases go
through your own connected payment gateway and do not create WooCommerce cart
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
The plugin provides the hosted ticketing flow described above.

== External services ==

This plugin connects to SeatLayer's hosted seating and ticketing service. It
requires a SeatLayer account. These services are contacted during use:

**SeatLayer CDN (`cdn.seatlayer.io`)**

Loads the seating-chart renderer only on pages containing a chart, from
`https://cdn.seatlayer.io/seatlayer-js@0.92.5/seatlayer.js`.
The browser sends its normal IP address, user agent and referrer; the plugin
adds no visitor data to that script request.

**SeatLayer API (`api.seatlayer.io`)**

On chart load, fetches the layout and live availability. During selection and
checkout, creates seat holds and payment sessions. Requests include the event
key and selected inventory; payment also needs the buyer's email and optional
name. Payment starts with the hold ID, and SeatLayer computes the amount from
its records. Optional in-page checkout sends the page URL so a card buyer returns to
it; SeatLayer only uses it if it matches a site address you added.

If an administrator saves a secret key, the WordPress server uses it to list
events in the block editor. That key is not sent to visitors.

**SeatLayer hosted buyer page (`app.seatlayer.io`)**

For hosted checkout, redirects the buyer to
`https://app.seatlayer.io/e/EVENT?hold=HOLD_ID` after seat selection.
The URL carries the event key and hold ID, not an amount or buyer details.

**The organizer's payment gateway (Stripe, PayPal, Razorpay, Tap, Mercado Pago, Xendit or Flutterwave)**

After seat selection, processes payment through the organizer's connected
account. SeatLayer sends the amount, currency and buyer email needed for
payment. Card details go directly to the payment provider and never pass
through this plugin or your WordPress database.

[Stripe Privacy Policy](https://stripe.com/privacy) ·
[PayPal Privacy Statement](https://www.paypal.com/myaccount/privacy/privacyhub) ·
[Razorpay Privacy Policy](https://razorpay.com/privacy) ·
[Tap Privacy Policy](https://www.tap.company/en-kw/privacy) ·
[Mercado Pago Privacy Policy](https://www.mercadopago.com/privacy) ·
[Xendit Privacy Policy](https://www.xendit.co/en/privacy-policy/) ·
[Flutterwave Privacy Policy](https://flutterwave.com/us/privacy-policy) ·
[SeatLayer Terms of Service](https://seatlayer.io/terms) ·
[SeatLayer Privacy Policy](https://seatlayer.io/privacy)

== Screenshots ==

1. Interactive seating chart on a WordPress page, with venue sections, category prices and live availability.
2. Seat map zoomed to individual numbered seats, with a chosen seat in the selection panel.
3. 3D seat view of the venue, so buyers can judge the view before they buy.
4. Checkout hold countdown protecting the selected seats while the buyer pays.
5. Mobile seat selection: touch seat map, numbered seats and the selection summary on a phone.
6. Stadium-scale seat map: a large arena chart in the same buyer renderer.
7. The SeatLayer seating chart block in the WordPress block editor, with its block settings.
8. The seating chart shortcode in the block editor, for classic and page-builder layouts.
9. Plugin settings page in wp-admin, where the optional server-side secret key and checkout return settings live

== Changelog ==

= 0.2.6 =
* Listing update: self-serve setup with no approval step, all seven supported payment gateways, current pricing, and new answers on price, page builders, gateways and QR tickets.
* Documentation and release metadata only; plugin behavior is unchanged.

= 0.2.5 =
* Refreshed listing screenshots for the block editor and settings screen.

= 0.2.4 =
* Add listing screenshots of the seat map, 3D seat view, mobile seat selection, hold countdown, block editor and shortcode.
* Update the pinned buyer SDK CDN release to the verified 0.92.5.
* Reword payment, scale and FAQ copy, and add seating chart and stadium answers.

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
* Return card buyers to the registered WordPress page after payment.
* Remove plugin settings on uninstall and translate chart error messages.
* Document external services and data sent.

= 0.1.0 =
* First release: seating chart block, shortcode and hosted payment handoff.
