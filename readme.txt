=== SeatLayer Seating Charts ===
Contributors: seatlayer
Tags: seating chart, reserved seating, event tickets, ticketing, seat selection
Requires at least: 6.3
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Interactive reserved-seating charts on WordPress. Visitors pick their seats and pay through your own Stripe or Razorpay account.

== Description ==

Add a real seating chart to any page or post. Visitors see live availability, pick
the seats they want, and pay — with the money going straight into your own payment
account.

**How it works**

1. Design your venue and create an event in your SeatLayer dashboard.
2. Connect your Stripe or Razorpay account there, under Payments.
3. Drop the SeatLayer block on a page, or use `[seatlayer_chart event="your-event"]`.

**What you get**

* Live availability — seats update as other people buy, without a page refresh.
* Real venue layouts: rows, tables, booths, general-admission areas, multiple floors.
* Works on phones. Pinch to zoom, tap to select.
* Seats are held while a buyer checks out, then released automatically if they
  don't finish — so two people cannot buy the same seat.
* Buyers get an emailed confirmation listing their seats.

**About payments**

SeatLayer is not a payment processor and never holds your money. You connect your
own Stripe or Razorpay account, and buyers pay you directly. Refunds happen in your
own payment dashboard; SeatLayer notices them and puts the seats back on sale.

**Requirements**

A SeatLayer account. The plugin embeds charts from an existing account — it is not
a standalone seating designer.

== Installation ==

1. Install and activate the plugin.
2. Create your venue, chart, and event at https://app.seatlayer.io.
3. Connect Stripe or Razorpay in the SeatLayer dashboard under Payments.
4. Add the **SeatLayer seating chart** block to a page and choose your event.

Optionally, add a SeatLayer secret key under **Settings → SeatLayer** so the block
editor can list your events in a dropdown instead of you pasting event keys. The
chart itself works without a key.

== Frequently Asked Questions ==

= Do I need a secret key? =

No. The seating chart works with just the event key, which is not a secret. A key
only makes the editor nicer, by listing your events in a dropdown.

= Where is my secret key stored? =

In your WordPress database, like every other plugin setting. It can create and
modify events, so anyone with database or administrator access to this site can
read it. If that is not acceptable for your setup, leave it blank and type event
keys by hand — nothing else needs it.

= Does this work with WooCommerce? =

Not yet. Today buyers pay through SeatLayer's checkout using your own Stripe or
Razorpay account. A WooCommerce integration — seats as cart line items, paid
through your existing Woo gateway — is planned separately.

= Can two people buy the same seat? =

No. A seat is held on SeatLayer's servers the moment a buyer selects it, and only
one hold can exist per seat. If they don't pay in time, the hold expires and the
seat returns to sale.

= What happens if a payment succeeds but the seat is taken? =

The buyer is refunded automatically. This is rare — it needs the hold to expire
during payment — and the order is flagged in your SeatLayer dashboard so you can
check it.

= Does it slow my site down? =

The chart's code only loads on pages that actually contain a chart.

= Can buyers pay without leaving my site? =

Partly, and it is off by default. Under **Settings → SeatLayer** you can switch
Checkout to "Let buyers pay without leaving this site". Two caveats worth reading
before you do:

* Your SeatLayer account needs in-page checkout enabled — it is granted per
  account. Without it the setting quietly does nothing and buyers take the normal
  redirect, so turning it on early cannot break anything.
* Razorpay collects payment entirely on your page — the buyer never leaves.
  Stripe cards still open Stripe's own page, but the buyer then comes back to the
  page they bought from. Either way the seats are sold and the tickets are
  emailed.

For that return trip, add your site to your SeatLayer account's embed domains,
using the exact address the settings screen shows you. SeatLayer only returns
buyers to addresses you have declared in advance, which is what stops a return
address from sending a paying customer somewhere you did not sanction.

If you skip it you lose the return trip and nothing else: an undeclared address
is ignored, not refused. The buyer still pays, the seats are still sold, and the
tickets are still emailed — they simply finish on SeatLayer's page.

== External services ==

This plugin is a client for SeatLayer, a hosted seating and ticketing service. It
does not work standalone, and using it means your site and your visitors talk to
SeatLayer. Exactly what is sent, and when:

**cdn.seatlayer.io** — the seating chart renderer.

* When: only on pages that actually contain a chart. The browser loads
  `https://cdn.seatlayer.io/seatlayer-js@0/seatlayer.js`.
* Sent: nothing the plugin adds. As with any script request, the browser sends
  its own IP address, user agent, and referring page.

**api.seatlayer.io** — seat availability, seat holds, and payment sessions.

* When (visitor): as soon as a chart renders, to fetch the seating layout and
  live availability; then when a visitor selects seats, to hold them; then, if
  they buy, to start a payment.
* Sent (visitor): the event key, the seats selected, and — at the payment step
  only — the buyer's email address and, if given, their name. A payment is
  started with the hold identifier alone: the plugin never sends an amount, and
  the SeatLayer server recomputes the total from its own records.
* Also sent, only when Checkout is set to "Let buyers pay without leaving this
  site": the web address of the page the chart is on, so a buyer returning from
  Stripe lands back on it. It is the page's own public URL and nothing else — no
  visitor data. SeatLayer uses it only if it matches an embed domain you declared
  in advance, and otherwise ignores it.
* When (administrator): only if you save a secret key, and only inside the block
  editor, to list your events in a dropdown. Your server makes this call, not the
  browser, and the key is never sent to a browser.
* Sent (administrator): your secret key. No visitor data.

**app.seatlayer.io** — the hosted buyer page, used in the default handoff mode.

* When: only if a buyer chooses to check out. Their browser is redirected to
  `https://app.seatlayer.io/e/EVENT?hold=HOLD_ID`.
* Sent: the event key and the hold identifier, in the URL. No amount, and nothing
  about the buyer.

**checkout.stripe.com / checkout.razorpay.com** — the payment step, and only if
you switch Checkout to "Let buyers pay without leaving this site".

* When: only after a buyer has selected seats and pressed pay. Razorpay's script
  is loaded into your page to open its payment window; Stripe redirects the
  buyer to its own page.
* Sent: what the gateway needs to charge — the amount and currency, which
  SeatLayer computed, plus the buyer's email. Your site does not send this; the
  payment window and the redirect are set up by SeatLayer and your own gateway
  account.
* Stripe: https://stripe.com/privacy — Razorpay: https://razorpay.com/privacy

Card details are handled by Stripe or Razorpay on the account **you** connected.
They never pass through this plugin, your WordPress site, or your database.

Service terms: https://seatlayer.io/terms
Privacy policy: https://seatlayer.io/privacy

== Changelog ==

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
