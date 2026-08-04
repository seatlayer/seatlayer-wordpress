# Releasing

Two destinations, in this order: **GitHub** (source of truth, where issues live)
and **wordpress.org** (where users actually install from). Nothing below has been
run — this file is the recipe, not a log.

---

## Part 0 — decide these BEFORE you submit anything

Three decisions, all cheap now and awkward later. (The slug/text-domain question
that used to head this list is **settled and applied** — see 0.0 for what was
decided and how to reverse it.)

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

### 0.2 `Contributors: seatlayer` must be a real wordpress.org account

Not a GitHub account — a wordpress.org one. Confirm
https://profiles.wordpress.org/seatlayer/ resolves; if it does not, register it
(or list the account that will actually own the plugin) before submitting. A
non-existent contributor is a stall in the review queue.

### 0.3 `Tested up to:` must name the current WordPress release

`readme.txt` says `6.8`. Check the current version at
https://wordpress.org/download/ and set it to that. A stale value shows users a
"may not be compatible" warning on the plugin page.

### 0.4 Screenshots must exist before the listing goes live

`readme.txt` lists three. They do **not** live in this repository — wp.org reads
them from the SVN `assets/` directory (Part 3.4). Produce them first, at
1280×720 or larger, named `screenshot-1.png`, `screenshot-2.png`,
`screenshot-3.png`, matching the captions in the readme in order:

1. A seating chart on a WordPress page.
2. Choosing the event in the block editor.
3. Plugin settings.

Until they are uploaded the listing shows three broken images. Either supply
them or delete the `== Screenshots ==` section — do not ship the section empty.

---

## Part 1 — verify locally

### 1.1 Syntax

```sh
cd /Users/paiteq/projects/seatlayer-sdks/wordpress
for f in seatlayer.php uninstall.php includes/*.php; do php -l "$f"; done
node --check assets/js/frontend.js
node --check assets/js/block.js
```

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

1. Sign in at https://wordpress.org/plugins/developers/ with the account from 0.2.
2. Go to https://wordpress.org/plugins/developers/add/ and upload
   `seatlayer-seating-charts-0.2.0.zip`.
3. Wait. Review is a human reading the source, typically days to a few weeks.
   They will email the account in 0.2 with anything they want changed; reply to
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

Banners, icon, and the screenshots from 0.4 go in `assets/`, which is a sibling
of `trunk/` and is **not** shipped to users.

```sh
cd ~/seatlayer-svn
# assets/banner-1544x500.png, assets/banner-772x250.png
# assets/icon-256x256.png,   assets/icon-128x128.png
# assets/screenshot-1.png, screenshot-2.png, screenshot-3.png
svn add --force assets
svn commit -m "Listing assets"
```

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
