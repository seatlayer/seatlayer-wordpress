# Releasing

Two destinations, in this order: **GitHub** (source of truth, where issues live)
and **wordpress.org** (where users actually install from). Nothing below has been
run — this file is the recipe, not a log.

> **Status, 2026-08-12.** `0.2.0` was submitted to wordpress.org on 11 Aug 2026 and
> came back **pended** on a single finding: `Plugin URI` pointed at
> `https://seatlayer.io/integrations/wordpress`, which 404'd. That page now exists,
> so the header is valid unchanged and the reviewed zip is byte-identical.
>
> Two questions below are settled and must not be re-opened. **The slug is
> `seatlayer-seating-charts`** — wp.org assigned it, it is permanent, and the
> "short slug `seatlayer`" reversal block before 0.1 is dead. **The contributor is
> `navincse`**, which resolves; `seatlayer` never existed as a wordpress.org
> account, so 0.1 is history rather than work. Every other URL the plugin declares
> was re-checked on 2026-08-12 and resolves.

Read Part 0 first; everything after it is meant to be run start to finish without
stopping to decide anything.

**The whole sweep, in order:**

| | |
|---|---|
| **0** | Two account facts to confirm, and one optional asset call. Nothing to code. |
| **1** | Verify locally: syntax → Plugin Check → click through it by hand. |
| **2** | GitHub: create the repo, push, tag `v0.2.0`. |
| **3** | wp.org: build the zip → submit → *(wait for approval)* → SVN trunk + tag → listing assets. |

The only unbounded wait is plugin review in 3.2. Everything before it is one
sitting.

---

## Part 0 — confirm these BEFORE you submit anything

Two account facts and one optional call, all cheap now and awkward later.

The slug/text-domain question that used to head this list is **settled and
applied in code** — kept here only so the decision is reversible, not because
anything is pending.

### 0.0 The slug and text domain — SETTLED, no action needed

wordpress.org derives the plugin slug from the **Plugin Name** header, and the
slug it assigns is permanent. The text domain has to equal that slug or the
language packs built on translate.wordpress.org never load, and Plugin Check
reports the mismatch as an error.

These used to disagree. They now agree:

| | value |
|---|---|
| Plugin Name | `SeatLayer Seating Charts` |
| Expected wp.org slug | `seatlayer-seating-charts` |
| Text Domain | `seatlayer-seating-charts` |

The **name** was kept and the **domain** was lengthened to match it, rather than
the other way round: the name is already public-facing in `readme.txt` and on
seatlayer.io, and a text domain is invisible to everyone except the translation
tooling. Churning the branding to save nine characters in a URL is the wrong
trade.

So: **do nothing here.** Use `seatlayer-seating-charts` as the slug everywhere
below — it is already the `--prefix` in Part 3.1 and the SVN path in Part 3.3.

<details>
<summary><strong>Only if you would rather have the short slug <code>seatlayer</code></strong> — the full reversal</summary>

Worth doing only if `seatlayer` is actually available (check that
https://wordpress.org/plugins/seatlayer/ returns 404) and only **before** the
first submission — after wp.org assigns a slug it is permanent.

This renames the plugin so the name-derived slug becomes `seatlayer`, and puts
the text domain back to `seatlayer`.

```sh
cd /Users/paiteq/projects/seatlayer-sdks/wordpress

# 1. The public name, in both places it appears.
sed -i '' 's/^ \* Plugin Name:       SeatLayer Seating Charts$/ * Plugin Name:       SeatLayer/' seatlayer.php
sed -i '' 's/^=== SeatLayer Seating Charts ===$/=== SeatLayer ===/' readme.txt

# 2. The text domain: 62 call sites + the header.
grep -rl "'seatlayer-seating-charts'" seatlayer.php includes assets/js \
  | xargs sed -i '' "s/'seatlayer-seating-charts'/'seatlayer'/g"
sed -i '' 's/^ \* Text Domain:       seatlayer-seating-charts$/ * Text Domain:       seatlayer/' seatlayer.php

# 3. The slug in this file's own commands (Parts 1.2, 3.1, 3.3).
sed -i '' 's/seatlayer-seating-charts/seatlayer/g' RELEASE.md
```

Then verify — expect **62** hits, and confirm the two non-gettext domain
registrations came along:

```sh
grep -rc "'seatlayer'" seatlayer.php includes assets/js
grep -n "load_plugin_textdomain" seatlayer.php
grep -n "wp_set_script_translations" includes/class-seatlayer-block.php
```

**One thing the blanket `sed` in step 2 is safe about, and you must keep safe if
you hand-edit instead:** `includes/class-seatlayer-settings.php` line ~126 holds
a bare `'seatlayer'` that is **not** a text domain — it is the `add_options_page()`
**menu slug**, which is what puts the settings screen at
`options-general.php?page=seatlayer`. Changing it would move the settings page
and break any bookmark or documentation link to it. The step-2 command only
matches `'seatlayer-seating-charts'`, so it cannot touch that line; a reversed
search-and-replace (`'seatlayer'` → something else) would.

```sh
# The menu slug must still read 'seatlayer' after any of this.
sed -n '122,128p' includes/class-seatlayer-settings.php
```

Re-lint afterwards (Part 1.1), then re-run Plugin Check (1.2).

</details>

### 0.1 `Contributors: seatlayer` must be a real wordpress.org account

Not a GitHub account — a wordpress.org one. Confirm
https://profiles.wordpress.org/seatlayer/ resolves; if it does not, register it
(or list the account that will actually own the plugin) before submitting. A
non-existent contributor is a stall in the review queue.

### 0.2 `Tested up to:` must name the current WordPress release

`readme.txt` says `6.8`. Check the current version at
https://wordpress.org/download/ and set it to that. A stale value shows users a
"may not be compatible" warning on the plugin page.

### 0.3 Screenshots — the readme no longer promises any. Optional, recommended.

`readme.txt` used to carry a `== Screenshots ==` section listing three images
that have never existed. **That section has been removed**, deliberately.

The reasoning, so it can be overruled knowingly: screenshot files do not live in
this repository and are not in the plugin zip — wp.org reads them from the SVN
`assets/` directory, which you only get **after** approval (Part 3.4, which comes
after 3.3). A readme that names three screenshots renders three broken images on
the public plugin page for as long as the files are missing, and the natural
order of the steps below guarantees a window where they are. Between a listing
with no screenshots (unremarkable — plenty of good plugins have none) and a
listing with three broken images (looks abandoned), the empty one is the honest
default, and it is the one that stays correct if this step is never done.

They are still worth adding, and adding them is four lines. To do it:

**1. Capture them.** Needs a running WordPress with the plugin active and a real
event — the same throwaway install from 1.2/1.3 is fine. Requirements:

| | |
|---|---|
| Location | SVN `assets/` (sibling of `trunk/`, **not** shipped to users) |
| Filenames | `screenshot-1.png`, `screenshot-2.png`, … — lowercase, numbered from 1, no gaps |
| Formats | PNG, JPG, or GIF. PNG for UI. |
| Size | No hard limit is enforced. wp.org displays them about 772px wide, so shoot **1544px wide or more** (2× for high-DPI) and keep every shot the same aspect ratio — mismatched ratios make the gallery jump. |

Shoot the frontend chart on a desktop viewport with real seat colours visible —
that one is the reason someone installs this — and crop out browser chrome and
any test data that reads as placeholder.

**2. Put the captions back** in `readme.txt`, immediately above `== Changelog ==`.
The list is positional: item *N* captions `screenshot-N.png`, so the order must
match the filenames exactly and a gap silently shifts every caption after it.

```
== Screenshots ==

1. A seating chart on a WordPress page, with live availability.
2. Choosing the event in the block editor.
3. The settings screen, including the optional in-page checkout.
```

**3. Commit them to SVN** in the same session as the `trunk/` commit that
contains the matching readme (Part 3.4), so the captions and the files are never
live without each other.

---

## Part 1 — verify locally

### 1.1 Syntax and logic — no WordPress needed

```sh
cd /Users/paiteq/projects/seatlayer-sdks/wordpress

# Syntax.
for f in seatlayer.php uninstall.php includes/*.php; do php -l "$f"; done
node --check assets/js/frontend.js
node --check assets/js/block.js

# Logic. Both print ALL PASS and exit 0; anything else is a blocker.
php tests/settings-logic.php
node tests/frontend-options.js
```

The two harnesses stub only what the code under test actually touches, so what
they exercise is the **real** file rather than a copy that can drift. They are
`export-ignore`d, so they are not in the zip.

- `tests/settings-logic.php` — the settings class's pure logic: the checkbox
  sanitizer (an unchecked box arrives as `null` and must mean OFF), the secret-key
  sanitizer (empty submission KEEPS the stored key, `__remove__` clears it, a bad
  shape keeps it), the base-URL sanitizer refusing `javascript:`/`data:`/`ftp:`,
  and `site_origin()` against the exact shape the server stores embed domains in.
- `tests/frontend-options.js` — runs `frontend.js` against a stub SDK and asserts
  what a `SeatPicker` is actually constructed with: `returnUrl` sent **only**
  under hosted checkout and carrying path and query verbatim, never sent in
  handoff mode, the handoff fallback still wired in both modes, and the
  no-SDK-on-page path showing one error without marking the container mounted.

**What they cannot prove**, and why 1.2 and 1.3 are not optional: nothing here
loads WordPress. Hook registration, block registration, the REST route and its
`edit_posts` gate, `wp_options` round-trips, `uninstall.php` (which only ever
runs inside WordPress's uninstall path), and every rendered admin screen are all
untested until an actual install runs them.

### 1.2 Plugin Check — the real gate

This is the tool the wp.org review team runs first, and it is the only check
here that exercises the plugin inside WordPress. It needs a WordPress install;
if you do not have one to hand, `wp-env` gives you a throwaway in one command.

```sh
# Throwaway WordPress in Docker, this plugin mounted into it.
cd /Users/paiteq/projects/seatlayer-sdks/wordpress
npx @wordpress/env start
npx @wordpress/env run cli wp plugin install plugin-check --activate

# wp-env mounts the plugin under THIS REPOSITORY'S DIRECTORY NAME (`wordpress`),
# not under the wp.org slug — so confirm what to call it before checking it.
npx @wordpress/env run cli wp plugin list
npx @wordpress/env run cli wp plugin check wordpress

npx @wordpress/env stop
```

Plugin Check's "plugin slug does not match text domain" test reads the *folder*
name, so under wp-env it will flag `wordpress` vs `seatlayer-seating-charts`.
That is an artefact of the mount, not a real defect. To see the result the
reviewers will see, check the built zip instead — unzip it into the wp-env
plugins directory under the real slug, or simply confirm by hand that
`Text Domain:` equals the `--prefix` used in Part 3.1.

Fix every **ERROR**. Read every **WARNING** and either fix it or be able to say
why not — the one already knowingly left is the CDN script's missing version,
which is annotated in `class-seatlayer-render.php`.

### 1.3 Try it by hand

Code review does not tell you whether a buyer lands on a working page. On that
same throwaway install:

- Activate, then **Settings → SeatLayer**: save a bad key (expect the shape
  warning, and that the previous key survives), then save nothing (expect the
  key to survive), then tick Remove.
- Put the block on a page, pick an event, view the page as a logged-out visitor.
- Select seats and check out against an event with a **test-mode** gateway.
- Tick the Checkout setting and repeat. Confirm the fallback: with an account
  that does not have in-page checkout, the buyer must still get the redirect.
- **The return trip, both ways round.** This is the one behaviour that cannot be
  proven without a real gateway, so prove it here. With the Checkout setting on
  and a **test-mode Stripe** event:
  - First declare this site under **Embed domains** in the SeatLayer dashboard,
    copying the address the settings screen prints verbatim. Buy a seat. The
    buyer must land back on the WordPress page, with `?order=…&status=success`
    appended and any query string the page already had still intact.
  - Then remove that embed domain and buy again. The buyer must still complete
    the purchase and still receive tickets — finishing on SeatLayer's own page.
    A failed payment, or an error, means the fallback is broken and is a release
    blocker; finishing on SeatLayer's page is the correct degraded behaviour.
  - Razorpay is unaffected by either — it never navigates away. Confirm it still
    completes in place.
- Delete the plugin from the Plugins screen and confirm the options are gone:
  `npx @wordpress/env run cli wp option get seatlayer_secret_key` → error.

---

## Part 2 — GitHub

The repository has no remote yet. `gh` is currently authenticated as
`pietechsolution`; make sure that account can create in the `seatlayer` org, or
change the owner in the first command.

```sh
cd /Users/paiteq/projects/seatlayer-sdks/wordpress

# Confirm what you are about to publish.
git log --oneline
git status --short          # expect: clean

# Create the repo and push. --source=. wires up origin for you.
gh repo create seatlayer/seatlayer-wordpress \
  --public \
  --source=. \
  --remote=origin \
  --description "SeatLayer for WordPress — interactive reserved-seating charts, with payment through your own Stripe or Razorpay account."

git push -u origin main

# Tag the release.
git tag -a v0.2.0 -m "0.2.0 — optional in-page checkout, uninstall cleanup, external-services disclosure"
git push origin v0.2.0
```

Then, optionally, a GitHub release with the zip attached (built in Part 3.1):

```sh
gh release create v0.2.0 seatlayer-seating-charts-0.2.0.zip \
  --title "0.2.0" \
  --notes "See readme.txt changelog."
```

---

## Part 3 — wordpress.org

### 3.1 Build the zip

`.gitattributes` marks the repo-only files `export-ignore`, so `git archive`
produces exactly the tagged commit minus those — no manual deleting, and no risk
of shipping something that was not reviewed.

**The `--prefix` is the folder name users end up with, so it must be the slug —
and it must equal the `Text Domain:` header.** Both are `seatlayer-seating-charts`
(0.0); if you took the reversal in 0.0, both become `seatlayer`.

```sh
cd /Users/paiteq/projects/seatlayer-sdks/wordpress
git archive --format=zip \
  --prefix=seatlayer-seating-charts/ \
  -o seatlayer-seating-charts-0.2.0.zip v0.2.0

# Check what is in it before uploading anything.
unzip -l seatlayer-seating-charts-0.2.0.zip
```

Expect: `seatlayer.php`, `uninstall.php`, `readme.txt`, `LICENSE`, `includes/`,
`assets/js/`, `assets/css/`. Expect NOT to see `.gitignore`, `.gitattributes`,
`CONTRIBUTING.md`, `RELEASE.md`.

### 3.2 Submit for review

Manual, and only once:

1. Sign in at https://wordpress.org/plugins/developers/ with the account from 0.1.
2. Go to https://wordpress.org/plugins/developers/add/ and upload
   `seatlayer-seating-charts-0.2.0.zip`.
3. Wait. Review is a human reading the source, typically days to a few weeks.
   They will email the account in 0.1 with anything they want changed; reply to
   that thread with a corrected zip rather than resubmitting through the form.

**Expect them to ask about the CDN script.** Loading executable code from
`cdn.seatlayer.io` is the one thing in this plugin that gets a second look.
The answer is that the renderer is the service's own SDK and the plugin is
useless without it — the same footing as `stripe.js` — and that this is
disclosed in the `== External services ==` section of `readme.txt`, per host,
naming what is sent. Point at that section; do not argue the general principle.

### 3.3 After approval — SVN

wp.org gives you an SVN repository, not a Git one. You get the URL by email.

```sh
cd ~   # anywhere outside this Git repo
svn checkout https://plugins.svn.wordpress.org/seatlayer-seating-charts/ seatlayer-svn
cd seatlayer-svn

# Unpack the same zip's contents into trunk.
rm -rf trunk/*
unzip -o /Users/paiteq/projects/seatlayer-sdks/wordpress/seatlayer-seating-charts-0.2.0.zip -d /tmp/sl
cp -R /tmp/sl/seatlayer-seating-charts/. trunk/

svn add --force trunk
svn status                       # read this before committing
svn commit -m "0.2.0"

# Tag it. `Stable tag` in readme.txt points here — this is what users install.
svn copy trunk tags/0.2.0
svn commit -m "Tag 0.2.0"
```

### 3.4 Listing assets

Banners, icon, and any screenshots go in `assets/`, which is a sibling of
`trunk/` and is **not** shipped to users.

```sh
cd ~/seatlayer-svn
# assets/banner-1544x500.png, assets/banner-772x250.png
# assets/icon-256x256.png,   assets/icon-128x128.png
svn add --force assets
svn commit -m "Listing assets"
```

The icon is what shows in wp-admin's plugin search, so it is the one asset worth
not skipping.

**Screenshots are optional and the readme currently promises none** — see 0.3. If
you are adding them, put the `screenshot-N.png` files here **and** the matching
`== Screenshots ==` captions in `trunk/readme.txt` in the same visit, so the
listing never renders a caption without its image.

---

## Subsequent releases

1. Land the change on `main`.
2. Bump the version in **three** places, which must agree or wp.org serves the
   wrong code: the `Version:` header in `seatlayer.php`, `SEATLAYER_VERSION` in
   the same file, and `Stable tag:` in `readme.txt`.
3. Add a `== Changelog ==` entry.
4. Tag, push, `git archive`, then SVN: copy into `trunk`, commit, `svn copy` to
   `tags/X.Y.Z`, commit.

`Stable tag` is what actually decides what users download. A tag that does not
exist in SVN, or a `Stable tag` still pointing at the old version, ships the old
code to everyone with no error anywhere.
