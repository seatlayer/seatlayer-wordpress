=== SeatLayer Seating Charts ===
Contributors: navincse
Tags: seating chart, seat selection, event tickets, sell tickets, reserved seating
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.2.1
License: MIT
License URI: https://opensource.org/licenses/MIT

Sell tickets with seat selection on WordPress. Visitors pick exact seats on an interactive chart and pay through your own Stripe or Razorpay.

== Description ==

Turn a WordPress page into a complete reserved-seating experience and sell
tickets with seat selection on your own site. Buyers can explore the venue, see
live availability and prices, choose exact seats, and continue to checkout
without calling, emailing, or guessing where they will sit. Payment goes to the
Stripe or Razorpay account you connect, not to a third-party ticketing site.

= A better seating experience for buyers =

* **Interactive venue maps** for rows, tables, booths, general-admission areas,
  accessible seating, and multiple floors.
* **Live availability** that updates while buyers are viewing the event.
* **Clear seat details and prices** before a buyer makes a selection.
* **Ticket tiers and active event offers** shown through the SeatLayer buyer
  experience when configured for the event.
* **Temporary seat holds** during checkout, with automatic release when a buyer
  does not finish.
* **Mobile-ready controls** with touch selection, pinch-to-zoom, responsive
  summaries, and a layout designed for smaller screens.
* **Best-available assistance** when buyers want SeatLayer to find seats
  together.
* **Email confirmation** with the seats purchased after successful payment.

= Built for WordPress organizers =

* Add the **SeatLayer seating chart** block in the Gutenberg editor.
* Use `[seatlayer_chart event="your-event"]` in classic content, builders, or
  shortcode-compatible areas.
* Choose an event from the editor when an optional SeatLayer secret key is
  configured, or paste an event key without storing a secret.
* Load the SeatLayer buyer SDK only on pages that contain a chart.
* Keep payments in the organizer's connected Stripe or Razorpay account.
* Manage venue design, event pricing, offers, orders, and ticket delivery from
  the [SeatLayer dashboard](https://app.seatlayer.io/).

= Choose the checkout experience =

**Hosted checkout — the safe default**

After choosing seats, the buyer continues to the SeatLayer-hosted event page to
pay. SeatLayer completes the Order and ticket-delivery flow through the payment
account connected by the organizer.

**Checkout from your WordPress page — optional**

Enable **Let buyers pay without leaving this site** under **Settings →
SeatLayer**. Razorpay opens on the page. Stripe opens its secure checkout and
returns the buyer to the WordPress page afterward. This option requires in-page
checkout access and the exact WordPress origin to be registered as a SeatLayer
embed domain; otherwise the plugin safely keeps the hosted-checkout flow.

= How it works =

1. [Create a SeatLayer account](https://app.seatlayer.io/) and design or select
   your venue. Request Managed Ticketing access before opening sales.
2. Create an event, set prices and offers, and connect Stripe or Razorpay.
3. Add the SeatLayer block to a WordPress page and choose the event.
4. Publish the page. Buyers can select seats and purchase through the configured
   SeatLayer checkout.

= Requirements =

This plugin connects WordPress to an approved SeatLayer Managed Ticketing
account. SeatLayer staff must enable Hosted Checkout for your organization
before you can create a Managed event. Venue design, events, pricing, offers,
and checkout are configured in SeatLayer. See
[Managed Ticketing pricing](https://seatlayer.io/pricing/) for service charges.

[WordPress seating chart setup guide](https://docs.seatlayer.io/integrations/wordpress/) ·
[WordPress reserved-seating features](https://seatlayer.io/integrations/wordpress/) ·
[Choose the right SeatLayer integration](https://docs.seatlayer.io/start/choose-an-integration/)

== Installation ==

1. Install and activate the plugin.
2. With Managed Ticketing access approved, create your venue, chart, and event
   in the [SeatLayer dashboard](https://app.seatlayer.io/).
3. Connect Stripe or Razorpay in the SeatLayer dashboard under Payments.
4. Add the **SeatLayer seating chart** block to a page and choose your event.

Optionally, add a SeatLayer secret key under **Settings → SeatLayer** so the block
editor can list your events in a dropdown instead of you pasting event keys. The
chart itself works without a key.

== Frequently Asked Questions ==

= How do I sell tickets with seat selection on WordPress? =

With Managed Ticketing access approved, install this plugin and create your
venue, chart, and event in the SeatLayer
dashboard, connect Stripe or Razorpay under Payments, then add the SeatLayer
seating chart block to a page and choose the event. Buyers pick exact seats on
your WordPress page, the selection is held while they check out, and payment
completes through your own gateway account. No separate ticketing site and no
manual seat assignment are involved.

= Is this an event ticketing plugin with a seat map? =

It is the seat-map front end for SeatLayer's reserved-seating and ticketing
platform. WordPress renders the interactive chart, while venue layouts, ticket
tiers, pricing, offers, live availability, orders, and ticket delivery are
managed in SeatLayer. Ticketing state is never duplicated in your WordPress
database, which is what keeps two buyers from being sold the same seat.

= Do I need a secret key? =

No. The seating chart works with just the event key, which is not a secret. A key
only makes the editor nicer, by listing your events in a dropdown.

= Where is my secret key stored? =

In your WordPress database, like every other plugin setting. It can create and
modify events, so anyone with database or administrator access to this site can
read it. If that is not acceptable for your setup, leave it blank and type event
keys by hand — nothing else needs it.

= Does this work with WooCommerce? =

The plugin can run on a site that also uses WooCommerce. SeatLayer ticket
purchases use your connected Stripe or Razorpay account and do not create
WooCommerce cart line items or orders.

= Can two people buy the same seat? =

No. A temporary hold reserves the buyer's selected inventory on SeatLayer's
servers during checkout. Only one hold can exist per seat. If they don't pay
in time, the hold expires and the seat returns to sale.

= What happens if a payment succeeds but the seat is taken? =

The buyer is refunded automatically. This is rare — it needs the hold to expire
during payment — and the order is flagged in your SeatLayer dashboard so you can
check it.

= Does it slow my site down? =

The chart's code only loads on pages that actually contain a chart.

= Can buyers pay without leaving my site? =

It is optional and off by default. Enable **Let buyers pay without leaving this
site** under **Settings → SeatLayer**. Razorpay opens on the page; Stripe opens
its secure checkout and returns the buyer afterward. Your SeatLayer account must
have in-page checkout enabled, and the exact WordPress origin must be registered
as an embed domain. Otherwise buyers safely continue through hosted checkout.

== External services ==

This plugin is a client for SeatLayer, a hosted seating and ticketing service. It
does not work standalone, and using it means your site and your visitors talk to
SeatLayer. This section explains each external service, when it is contacted, and
what data is sent.

**SeatLayer CDN (`cdn.seatlayer.io`)** — loads the seating-chart renderer.

* When: only on pages that actually contain a chart. The browser loads
  `https://cdn.seatlayer.io/seatlayer-js@0/seatlayer.js`.
* Sent: nothing the plugin adds. As with any script request, the browser sends
  its own IP address, user agent, and referring page.

**SeatLayer API (`api.seatlayer.io`)** — provides layouts, live
availability, seat holds, and payment sessions.

* When (visitor): as soon as a chart renders, to fetch the seating layout and
  live availability; then when a visitor selects seats, to hold them; then, if
  they buy, to start a payment.
* Sent (visitor): the event key, the seats selected, and — at the payment step
  only — the buyer's email address and, if given, their name. A payment is
  started with the hold identifier alone: the plugin never sends an amount, and
  the SeatLayer server recomputes the total from its own records.
* Also sent for optional in-page checkout: the chart page's public URL, used only
  when it matches a pre-declared embed domain so Stripe can return the buyer.
* When (administrator): only if you save a secret key, and only inside the block
  editor, to list your events in a dropdown. Your server makes this call, not the
  browser, and the key is never sent to a browser.
* Sent (administrator): your secret key. No visitor data.

**[SeatLayer hosted buyer page](https://app.seatlayer.io/)** — completes the
default hosted-checkout journey.

* When: only if a buyer chooses to check out. Their browser is redirected to
  `https://app.seatlayer.io/e/EVENT?hold=HOLD_ID`.
* Sent: the event key and the hold identifier, in the URL. No amount, and nothing
  about the buyer.

**Stripe or Razorpay** — processes payment through the organizer's connected
gateway account.

* When: after a buyer selects seats and presses pay. Razorpay opens its payment
  window on the page; Stripe redirects to its secure checkout.
* Sent by SeatLayer to the gateway: the authoritative amount, currency, and
  buyer email needed to process payment.
* Privacy: [Stripe Privacy Policy](https://stripe.com/privacy) ·
  [Razorpay Privacy Policy](https://razorpay.com/privacy)

Card details are handled by Stripe or Razorpay on the account **you** connected.
They never pass through this plugin, your WordPress site, or your database.

[SeatLayer Terms of Service](https://seatlayer.io/terms) ·
[SeatLayer Privacy Policy](https://seatlayer.io/privacy)

== Changelog ==

= 0.2.1 =
* Documentation only. Refreshes the plugin listing text and the project
  README. No plugin behaviour changes.

= 0.2.0 =
* New: an optional Checkout setting that lets buyers pay on your page instead of
  being sent to SeatLayer. Off by default, and it falls back to the redirect on
  its own if your account does not have in-page checkout.
* With that setting on, a buyer paying by card now returns to the page they
  bought from instead of finishing on SeatLayer. Declare your site under embed
  domains in your SeatLayer dashboard to switch it on; without it the buyer still
  pays and still gets their tickets, just on SeatLayer's page.
* Deleting the plugin now removes its settings, including any saved secret key.
* The two messages a visitor can see if a chart fails to load are translatable.
* Documented every SeatLayer service the plugin talks to, and what is sent.

= 0.1.0 =
* First release: SeatLayer block and `[seatlayer_chart]` shortcode. Buyers are
  handed to SeatLayer's buyer page to pay through your own Stripe or Razorpay
  account.
