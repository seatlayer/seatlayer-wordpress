# Contributing

## What this plugin is

A thin embed for an existing SeatLayer account. It renders a chart, and hands the
buyer to payment. It is deliberately **not** a place where order state, prices, or
credentials live — those belong to the SeatLayer server, which is the only side
that can be trusted with them. A patch that moves any of them into WordPress is
the one kind of change that will be turned down on principle rather than on
detail.

## No build step

There is no `package.json`, no bundler, and no `vendor/`. The editor script is
plain ES5-era JavaScript against the `wp.*` globals; the frontend script is a
mount shim. This is a choice, not an omission — see the comment at the top of
`includes/class-seatlayer-block.php`. It means what is in the repository is what
runs, and a reviewer never has to trust a build artifact.

Likewise there is no Composer dependency on the SeatLayer PHP SDK. The reasoning
is written out at length at the top of `seatlayer.php`; read it before adding one.

## Layout

```
seatlayer.php                       plugin header, constants, boot
uninstall.php                       removes options (incl. the secret key) on delete
includes/class-seatlayer-settings.php   options + settings screen
includes/class-seatlayer-api.php        the ONE authenticated call (list events)
includes/class-seatlayer-rest.php       admin-only REST proxy for the editor
includes/class-seatlayer-render.php     shortcode + shared markup
includes/class-seatlayer-block.php      block registration (server-rendered)
assets/js/frontend.js                   mounts the picker
assets/js/block.js                      editor UI
```

The block and the shortcode both go through `SeatLayer_Render::markup()`. Keep it
that way; the two drifting apart is the bug that structure exists to prevent.

## House rules

- **Escape every output, sanitize every input.** No exceptions, including for
  strings you wrote yourself — a rule with an exception is a rule nobody can
  check.
- **Every user-facing string is translatable**, with the `seatlayer` text domain,
  and never with a variable passed to a translation function. Buyer-facing
  JavaScript strings are translated in PHP and travel in the `data-seatlayer`
  config; do not add a `wp-i18n` dependency to a public-page script to avoid it.
- **No inline JavaScript.** Configuration rides on a `data-` attribute so the
  plugin works under a strict Content-Security-Policy.
- **Assets load only on pages that contain a chart.** `register` in
  `register_assets()`, `enqueue` in `markup()`.
- **Prefix everything**: `seatlayer_` functions and options, `SEATLAYER_`
  constants, `SeatLayer_` classes.
- One logical change per commit.

## Checks before you open a PR

There is no CI and no PHP test suite here. Run at least:

```sh
# Syntax. Any PHP 7.4+ interpreter.
for f in seatlayer.php uninstall.php includes/*.php; do php -l "$f"; done
node --check assets/js/frontend.js
node --check assets/js/block.js
```

If you have Docker or a local WordPress, run the official Plugin Check plugin
against the tree as well — it is the same tool the wp.org reviewers start with:

```sh
wp plugin install plugin-check --activate
wp plugin check seatlayer
```

Anything touching payment behaviour has to be tried against a real event with a
**test-mode** gateway. Code review cannot tell you whether a buyer ends up on a
working page.
