# BritBrokers

Property brokering website for British Brokers (Colombo), written in plain PHP with MariaDB/MySQL.

Requires **PHP 8.3 or newer** (target: PHP 8.5) with the `mysqli`, `gd`, `mbstring` and `exif` extensions.

## Configuration

No passwords or keys are stored in the code. Settings are read from, in order:

1. real environment variables;
2. `britbrokers.env` in the folder **above** the web root (recommended), or the file named in `BRITBROKERS_ENV_FILE`;
3. `.env` next to `config.php` (local development only; blocked from the web by `.htaccess`).

Copy `.env.example`, fill it in, and create the admin password hash with:

```
php bin/hash-password.php
```

## Folders

| Path | What it is |
|---|---|
| `uploads/`, `uploads/thumbs/` | Property photos uploaded by admins. Not in git; must be writable by PHP. `.htaccess` stops anything in here from running as a script. |
| `bin/` | Command-line scripts (blocked from the web). |
| `lib/PHPMailer/` | PHPMailer 6 (see `VERSION.txt`). |

## Scheduled job

Exchange rates (used by the price converter) are refreshed by:

```
php /path/to/site/bin/update-exchange-rates.php
```

Run it from cron once a day. It replaces the old web-accessible `exchange_rates.php`.

## Local development

```
cp .env.example .env              # then edit the DB settings
php -S 127.0.0.1:8080
```

Database dumps are not kept in git, because they include personal data (blog users, newsletter subscribers).
