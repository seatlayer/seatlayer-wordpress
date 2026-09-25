# Releasing

Two destinations: **GitHub** is the source of truth and **wordpress.org** is where
users install the plugin (https://wordpress.org/plugins/seatlayer-seating-charts/).
The normal release path is automated by `.github/workflows/release-wordpress.yml`.
The manual SVN steps further down are the recovery procedure.

Run every command below from the root of this repository. The examples use a
shell variable for the version being released:

```sh
VERSION=0.3.0
```

## Normal automated release

1. Bump the version in **three** places, which must agree or wordpress.org serves
   the wrong code:
   - the `Version:` header in `seatlayer.php`
   - `SEATLAYER_VERSION` in `seatlayer.php`
   - `Stable tag:` in `readme.txt`
2. Add a `== Changelog ==` entry in `readme.txt`, and check that `Tested up to:`
   names the current WordPress release (https://wordpress.org/download/).
3. Verify locally (next section), then merge the change to `main`.
4. Create and publish a GitHub Release whose tag is that version, in `v0.3.0`
   style.
5. GitHub Actions verifies the tag and the focused tests, deploys the release to
   the WordPress.org SVN repository, and attaches the installable ZIP to the
   GitHub Release.

The workflow rejects prereleases and mismatched versions. It needs the repository
secrets `SVN_USERNAME` and `SVN_PASSWORD`. WordPress.org still uses SVN as its
publishing backend; GitHub Actions performs that SVN operation for maintainers.

`Stable tag` is what decides what users download. A tag that does not exist in
SVN, or a `Stable tag` still pointing at the old version, ships the old code to
everyone with no error anywhere.

## Verify locally

### Syntax and logic (no WordPress needed)

```sh
for f in seatlayer.php uninstall.php includes/*.php; do php -l "$f"; done
node --check assets/js/frontend.js
node --check assets/js/block.js

# Both print ALL PASS and exit 0. Anything else blocks the release.
php tests/settings-logic.php
node tests/frontend-options.js
```

The two harnesses stub only what the code under test touches, so they exercise
the real files. They are `export-ignore`d and are not in the ZIP.

- `tests/settings-logic.php` covers the settings class's pure logic: the checkbox
  sanitizer, the secret-key sanitizer (an empty submission keeps the stored key,
  `__remove__` clears it, a bad shape keeps it), the base-URL sanitizer refusing
  `javascript:`, `data:` and `ftp:`, and `site_origin()`.
- `tests/frontend-options.js` runs `frontend.js` against a stub SDK and checks
  what a `SeatPicker` is constructed with, including when `returnUrl` is sent.

These harnesses do not load WordPress. Hook and block registration, the REST
route and its `edit_posts` gate, option round-trips, `uninstall.php` and the admin
screens are only exercised by the next two steps.

### Plugin Check

Plugin Check is the tool the wordpress.org review team runs first. `wp-env` gives
you a throwaway WordPress in Docker with this plugin mounted:

```sh
npx @wordpress/env start
npx @wordpress/env run cli wp plugin install plugin-check --activate

# wp-env mounts the plugin under this repository's directory name,
# not under the wordpress.org slug, so confirm the name first.
npx @wordpress/env run cli wp plugin list
npx @wordpress/env run cli wp plugin check <mounted-plugin-name>

npx @wordpress/env stop
```

Under wp-env, the "plugin slug does not match text domain" test compares against
the folder name, so it can flag a mismatch that is only an artefact of the mount.
The real slug and the `Text Domain:` header are both `seatlayer-seating-charts`.

Fix every **ERROR**. Read every **WARNING** and either fix it or be able to say
why not. The one known warning is the CDN script's missing version, which is
annotated in `includes/class-seatlayer-render.php`.

### Try it by hand

On the same throwaway install:

- Activate, then open **Settings, SeatLayer**: save a bad key (expect the shape
  warning, and the previous key survives), save nothing (the key survives), then
  tick Remove.
- Put the block on a page, pick an event, and view the page as a logged-out
  visitor.
- Select seats and check out against an event with a **test-mode** gateway.
- Tick the Checkout setting and repeat. With an account that does not have
  in-page checkout, the buyer must still get the redirect.
- Check the return trip with the Checkout setting on and a test-mode Stripe event:
  - Declare this site under **Embed domains** in the SeatLayer dashboard, copying
    the address the settings screen prints. Buy a seat. The buyer lands back on
    the WordPress page with `?order=…&status=success` appended and any existing
    query string intact.
  - Remove that embed domain and buy again. The buyer must still complete the
    purchase and receive tickets, finishing on SeatLayer's own page. A failed
    payment or an error here blocks the release.
  - Razorpay never navigates away. Confirm it still completes in place.
- Delete the plugin from the Plugins screen and confirm its options are gone:
  `npx @wordpress/env run cli wp option get seatlayer_secret_key` returns an
  error.

## Manual recovery release

Use this only if GitHub Actions or the automated deployment is unavailable. Do
not also publish the GitHub Release while following these steps, or the same
version is deployed twice.

### Tag on GitHub

```sh
git status --short          # expect: clean
git log --oneline -5        # confirm the commit being released
git tag -a "v$VERSION" -m "$VERSION"
git push origin "v$VERSION"
```

### Build the ZIP

`.gitattributes` marks the repository-only files `export-ignore`, so `git archive`
produces exactly the tagged commit minus those files. The `--prefix` is the folder
name users end up with, so it must be the slug, which equals the `Text Domain:`
header.

```sh
git archive --format=zip \
  --prefix=seatlayer-seating-charts/ \
  -o "seatlayer-seating-charts-$VERSION.zip" "v$VERSION"

unzip -l "seatlayer-seating-charts-$VERSION.zip"
```

Expect `seatlayer.php`, `uninstall.php`, `readme.txt`, `LICENSE`, `includes/`,
`assets/js/` and `assets/css/`. Expect **not** to see `.gitignore`,
`.gitattributes`, `CONTRIBUTING.md`, `RELEASE.md` or `tests/`.

### Commit to SVN

```sh
ZIP="$PWD/seatlayer-seating-charts-$VERSION.zip"
WORK="$(mktemp -d)"

svn checkout https://plugins.svn.wordpress.org/seatlayer-seating-charts/ "$WORK/svn"
unzip -o "$ZIP" -d "$WORK/unpacked"

cd "$WORK/svn"
rm -rf trunk/*
cp -R "$WORK/unpacked/seatlayer-seating-charts/." trunk/

svn add --force trunk
svn status                       # read this before committing
svn commit -m "$VERSION"

# `Stable tag` in readme.txt points here. This is what users install.
svn copy trunk "tags/$VERSION"
svn commit -m "Tag $VERSION"
```

## Listing assets

Banners, the icon and any screenshots live in the SVN `assets/` directory, a
sibling of `trunk/` that is **not** shipped to users:

- `assets/banner-1544x500.png`, `assets/banner-772x250.png`
- `assets/icon-256x256.png`, `assets/icon-128x128.png`
- `assets/screenshot-1.png`, `assets/screenshot-2.png`, … (optional)

The icon is what shows in wp-admin's plugin search, so it is the one asset worth
not skipping.

`readme.txt` currently lists no screenshots. If you add them, commit the
`screenshot-N.png` files and the matching `== Screenshots ==` captions in
`trunk/readme.txt` in the same visit. The captions are positional (item *N*
captions `screenshot-N.png`), so a gap shifts every caption after it. Shoot at
1544px wide or more and keep one aspect ratio across the set.
