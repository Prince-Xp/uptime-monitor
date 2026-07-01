# Website Uptime Monitor

Laravel + Vue app that checks a list of client websites every 15 minutes and
emails the client when one goes down. Built as a take-home assessment.

Stack: Laravel 11, MySQL, Redis (queue), Vue 3 SPA via Vite.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set these in `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=uptime_monitor
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1

MAIL_MAILER=log        # switch to ses in production
MAIL_FROM_ADDRESS="do-not-reply@example.com"
```

```bash
php artisan migrate
php artisan db:seed --class=ClientSeeder   # optional, adds demo clients/sites
npm run build
```

Two processes need to run for monitoring to actually work:

```bash
php artisan queue:work redis --queue=monitoring
php artisan schedule:work
```

(In production, `schedule:work` gets replaced by a real cron entry calling
`schedule:run` every minute, and the queue worker runs under supervisor so it
restarts if it dies.)

Clients and their websites go straight into the DB — no signup form, per the
spec.

## How it works

The scheduler fires `websites:check` every 15 minutes. That command doesn't
do any actual checking — it just loops through every website and dispatches
a `CheckWebsiteJob` onto a Redis queue. The real HTTP checks happen in the
queue worker, in parallel. This is the part that makes it scale: doesn't
matter if there are 10 websites or 10,000, the scheduler tick stays instant
either way.

Each job hits the site's homepage with a 10 second timeout (both connect and
response). Non-2xx status or a connection failure = down. If the site just
flipped from up to down, it sends the alert email. If it was already down
last check, it stays quiet — otherwise a 3-hour outage would mean 12
identical emails, which is annoying more than useful.

Every check also gets logged to a `website_checks` table, mostly so the job
is testable and there's an audit trail if I want to build an uptime history
view later.

## Tests

```bash
php artisan test
```

Covers the check job (up stays quiet, down triggers the right email with the
right subject, timeouts count as down, no repeat alert while still down) and
the two API endpoints. Didn't write frontend tests — ran out of time and the
backend logic felt like the higher-value thing to cover.

## What I'd add with more time

- A "back up" recovery email
- Admin UI for adding clients instead of raw DB inserts
- Per-website check interval instead of one global 15 min for everyone