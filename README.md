# BritBrokers

Property brokering website for British Brokers (Colombo), written in plain PHP with MariaDB/MySQL.

Requires **PHP 8.2 or newer** (target: PHP 8.5) with the `mysqli`, `gd`, `mbstring` and `exif` extensions, and MariaDB 10.5+ / MySQL 8.

## Configuration

No passwords or keys are stored in the code. Settings are read from, in order:

1. real environment variables;
2. `britbrokers.env` in the folder **above** the web root (recommended), or the file named in `BRITBROKERS_ENV_FILE`;
3. `.env` next to `config.php` (local development only; blocked from the web by `.htaccess`).

Copy `.env.example` and fill it in.

## After every upload

```
php bin/check-install.php
```

It checks PHP, the settings file, the database and migrations, admin accounts, writable upload folders,
the protective `.htaccess` / `.user.ini` files (upload tools often skip files whose names start with a dot),
and lists old files from earlier versions that should be deleted. Add `--deep` to also count the uploads folder
and check that the newest photos exist on disk.

The same checks, with more detail, are on the control panel's **Server health** tab.

## Control panel

`control_panel.php` (admin login required) has five tabs; the address bar keeps the open tab (`#add`, `#health` ...),
so reloading or pressing Back stays in place.

- **Dashboard** (`stats.php`): listing totals, listings added per month, listings by type and district,
  the newest listings with View/Edit buttons, and listings that need attention (no photos, available for
  over 6 months, marked sold or let).
- **Add / Edit / Delete property**: the existing property screens.
- **Server health** (`server_health.php`): PHP version and its security-support date, disk space, memory and
  upload limits, OPcache, database connection, size and migrations, protective files, writable upload
  folders, missing photos, old files, HTTPS, error display, settings-file location, admin accounts,
  failed logins, clean links, exchange-rate refresh, email and analytics settings, and the newest lines of
  the PHP error log. Each problem says what to do. The tab's badge shows the number of problems
  (red: to fix, amber: to look at).

Both the tab and `bin/check-install.php` use `inc/health.php`; add new checks there.

## Database migrations

Changes to the database structure live in `migrations/` as numbered SQL files. Apply them with:

```
php bin/migrate.php --status     # what is applied / pending
php bin/migrate.php --yes        # apply pending migrations (back up first!)
```

Applied migrations are recorded in the `schema_migrations` table. Back up before running:
`mysqldump --single-transaction DBNAME > backup.sql`. Structure changes cannot be undone automatically;
if a migration fails, restore the backup.

| Migration | What it does |
|---|---|
| 001_admin_accounts | `admin_users` and `login_attempts` tables |
| 002_innodb_utf8mb4 | Tables to InnoDB and real utf8mb4 (byte-preserving: the old latin1 columns already held UTF-8 text). Records the current display order of the lists in `sort_order` |
| 003_keys_and_indexes | Moves orphaned image records to `images_orphaned`, removes duplicate feature rows, district/city to INT, indexes, foreign keys |

After migration 002 the site must connect with `DB_CHARSET="utf8mb4"`, which is the default.

## Admin accounts

Each person has their own login, stored in `admin_users` with a password hash:

```
php bin/admin-user.php add <username> ["Display Name"] [email]
php bin/admin-user.php passwd <username>
php bin/admin-user.php disable <username>     # their open sessions end immediately
php bin/admin-user.php enable <username>
php bin/admin-user.php unlock <username>      # clear failed logins
php bin/admin-user.php list
```

Logins are throttled: 5 failures for one username or 20 from one IP address within 15 minutes lock
further attempts for 15 minutes. Sessions end after 2 hours idle, or 12 hours in total. Every request that
changes data needs the session's CSRF token, which the admin pages add to their AJAX calls automatically.

## Code layout

Only pages and endpoints live in the web root. Every one starts with `require_once __DIR__ . "/bootstrap.php";`.

| Path | What it is |
|---|---|
| `*.php` (root) | Pages (`index.php`, `list_property.php`, `single-property.php`, ...), admin screens and endpoints (`ajax_functions.php`, `upload.php`, `mailman.php`, ...) |
| `inc/` | Library code: `config.php`, `auth.php`, `functions.php` (data access), `layout.php` (page layout, property links), `health.php` (server health checks), `set_post_vars.php` (search input), image and captcha classes |
| `templates/` | Page parts: header (CSS/JS), navigation, footer, sub-footer, search box, property listing, sliders, latest news |

A page looks like this:

```php
<?php
require_once __DIR__ . "/bootstrap.php";
page_head(array("title" => "Page title", "description" => "For search engines"));
?>
<script>/* page-specific scripts and styles go here, inside <head> */</script>
<?php page_body("home"); ?>
  ...content...
<?php page_end(); /* page_end(false) leaves out the contact band */ ?>
```

Property links come from `property_url($property)`: `property-123-3-bedroom-house-colombo-7`, mapped to
`single-property.php?pid=123` by `.htaccess`. Old `single-property.php?pid=` links redirect permanently.
Clean links are only used when `.htaccess` is active (it sets `BB_PRETTY_URLS`); without it the site
falls back to `single-property.php?pid=` links rather than 404s.

## Folders

| Path | What it is |
|---|---|
| `uploads/`, `uploads/thumbs/` | Property photos uploaded by admins. Not in git; must be writable by PHP. `.htaccess` stops anything in here from running as a script. |
| `inc/`, `templates/` | Code and page parts (blocked from the web). |
| `bin/` | Command-line scripts (blocked from the web). |
| `migrations/` | Database migrations (blocked from the web). |
| `tests/` | Tests (blocked from the web). |
| `lib/PHPMailer/` | PHPMailer 6 (see `VERSION.txt`). |

## CSS, JavaScript and images

Pages load one CSS and one JavaScript bundle (plus admin bundles on the control panel):

| Bundle | Contents |
|---|---|
| `css/site.min.css` | Bootstrap 3.4.1 + theme, flexslider, `css/style.css`, Font Awesome, validator styles, `css/fixes.css` |
| `js/site.min.js` | jQuery 3.7.1 + jQuery Migrate, easing, lazyload, to-top button, Bootstrap 3.4.1, `js/custom.js` |
| `css/admin.min.css`, `js/admin.min.js` | jQuery UI, form validator, bootstrap-select, Dropzone, confirm dialogs |
| `js/validator.min.js` | Form validator only (property page) |

The bundles are built and committed, so the server needs no Node. After changing `css/style.css`,
`css/fixes.css`, `js/custom.js` or a library:

```
npm install        # once
npm run build
```

Bundle contents are listed in `bin/build-assets.mjs`. Browsers cache static files for 30 days (`.htaccess`);
the bundles are linked with `?v=<timestamp>`, so a rebuild is picked up immediately.

New photos added to `img/` can be shrunk with `python3 bin/optimize-images.py img` (needs Pillow).
It re-encodes JPEGs at quality 82, only where it saves at least 15% with no visible change.

## Search engines and analytics

- Every page gets a canonical link, Open Graph/Twitter tags for link previews, and (home, contact) business
  details as structured data. Pass `title`, `description`, `canonical`, `image`, `robots` to `page_head()`.
- Filtered search results are `noindex,follow`; login and control panel are `noindex,nofollow`.
- `sitemap.xml` (served by `sitemap.php`) lists the main pages and every property. Submit it once in
  Google Search Console.
- `robots.txt` only works at the root of the domain; if the site runs in `/live/`, copy its lines to the
  domain root's robots.txt with `/live` in front of each path.
- Google Analytics 4: set `GA4_MEASUREMENT_ID` in the config. Empty means no tracking. Admin pages are never tracked.

## Scheduled job

Exchange rates (used by the price converter) are refreshed by:

```
php /path/to/site/bin/update-exchange-rates.php
```

Run it from cron once a day (cPanel > Cron Jobs). It records when it last ran (migration 004), and the
Server health tab warns when the rates have not been refreshed for two days.

## Development and tests

```
cp .env.example .env              # point DB_NAME at a test copy of the database
php bin/migrate.php --yes
php -S 127.0.0.1:8080 bin/dev-router.php     # the router handles clean links and blocked folders
```

Tests (they add and delete records, so use a database whose name contains "test"):

```
php tests/run.php                                         # data-layer tests
php tests/search_snapshot.php /path/to/site > out.json    # snapshot of search results, to compare two versions
```

All queries use prepared statements through `db_run()` / `db_all()` / `db_one()` in `functions.php`.
`runQuery()` is only for fixed SQL with no outside values.

Database dumps are not kept in git, because they include personal data (blog users, newsletter subscribers).
