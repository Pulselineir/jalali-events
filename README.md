<div align="center">

# 📅 Jalali Events

### Iranian Jalali Calendar Events & Official Holidays for Laravel

A Laravel package for fetching, storing, querying, and automatically synchronizing Iranian Jalali calendar events and official holidays.

[![Latest Version](https://img.shields.io/packagist/v/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![Total Downloads](https://img.shields.io/packagist/dt/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![PHP Version](https://img.shields.io/packagist/php-v/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![License](https://img.shields.io/github/license/Pulselineir/jalali-events?style=flat-square)](LICENSE)
[![GitHub Release](https://img.shields.io/github/v/release/Pulselineir/jalali-events?style=flat-square)](https://github.com/Pulselineir/jalali-events/releases)

</div>

---

## ✨ Features

Jalali Events provides a simple API for working with the Iranian calendar in Laravel applications.

- 📆 Fetch complete Jalali calendar years
- 🎉 Store Iranian events and occasions
- 🚩 Detect official holidays
- 🗓 Detect weekends separately from official holidays
- 🔎 Query individual Jalali dates
- 📚 Retrieve all events for a date
- ↔️ Query arbitrary date ranges
- 🔢 Count holidays within a range
- 🔄 Synchronize calendar data from `time.ir`
- ⏰ Automatic scheduled synchronization
- 🗄 Database storage
- 📄 JSON storage
- 🐘 PostgreSQL compatible
- 🐬 MySQL compatible
- 🪶 SQLite compatible
- 🧩 Laravel auto-discovery
- 🧪 Fully tested

---

## 📦 Installation

Install the package using Composer:

```bash
composer require pulseline/jalali-events
```

Laravel will automatically discover the package service provider.

Run the package migrations:

```bash
php artisan migrate
```

Then synchronize the current Jalali year:

```bash
php artisan jalali-events:sync
```

Or synchronize a specific year:

```bash
php artisan jalali-events:sync 1405
```

---

## 🚀 Quick Start

```php
use JalaliEvents;
```

Get information about a Jalali date:

```php
$day = JalaliEvents::date('1405/04/03');
```

Example result:

```php
[
    'jalali_year' => 1405,
    'jalali_month' => 4,
    'jalali_day' => 3,
    'jalali_date' => '1405/04/03',

    'gregorian_date' => '2026-06-24',

    'weekday' => 3,

    'is_holiday' => true,
    'is_weekend' => false,

    'source' => 'time.ir',

    'events' => [
        [
            'title' => 'تاسوعای حسینی',
            'category' => 'hijri',
            'is_holiday' => true,
        ],
    ],
]
```

---

## 🎉 Check Whether a Date Is a Holiday

```php
$isHoliday = JalaliEvents::isHoliday(
    '1405/04/03'
);
```

Returns:

```php
true
```

---

## 📚 Get Events for a Date

```php
$events = JalaliEvents::events(
    '1405/04/03'
);
```

Example:

```php
[
    [
        'title' => 'تاسوعای حسینی',
        'category' => 'hijri',
        'is_holiday' => true,
    ],
]
```

A single date can contain multiple events.

---

## ↔️ Query a Date Range

```php
$days = JalaliEvents::between(
    '1405/04/01',
    '1405/04/10'
);
```

This returns all calendar days between the two supplied Jalali dates.

---

## 🚩 Holidays in a Date Range

```php
$holidays = JalaliEvents::holidaysBetween(
    '1405/04/01',
    '1405/04/31'
);
```

---

## 🔢 Count Holidays

```php
$count = JalaliEvents::holidayCount(
    '1405/04/01',
    '1405/04/31'
);
```

---

## 🔄 Calendar Synchronization

Synchronize a specific Jalali year:

```bash
php artisan jalali-events:sync 1405
```

Synchronize the current Jalali year:

```bash
php artisan jalali-events:sync
```

Synchronization downloads calendar data from the configured upstream source and stores the normalized result locally.

Application queries do **not** request data from `time.ir`.

The normal flow is:

```text
time.ir
   ↓
Calendar Source
   ↓
Parser
   ↓
Normalizer
   ↓
Calendar Updater
   ↓
Database / JSON
   ↓
Application API
```

This means your application continues querying its own local storage instead of calling the upstream website for every request.

---

## 🗄 Storage

Jalali Events supports two storage drivers.

### Database

Database storage is the default:

```env
JALALI_EVENTS_STORAGE=database
```

The package creates two tables:

```text
jalali_calendar_days
jalali_events
```

The `jalali_calendar_days` table stores one record per calendar day.

The `jalali_events` table supports multiple events for each day.

---

## 🐘 PostgreSQL, MySQL and SQLite

By default, Jalali Events uses the Laravel application's default database connection.

For example, if your Laravel application uses PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=my_application
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Jalali Events will store its tables in the same PostgreSQL database.

No additional configuration is required.

---

## 🔌 Dedicated Database Connection

You may optionally store calendar data on another Laravel database connection.

```env
JALALI_EVENTS_DB_CONNECTION=jalali
```

Then define the `jalali` connection inside:

```text
config/database.php
```

Example:

```php
'connections' => [

    'jalali' => [
        'driver' => 'pgsql',
        'host' => env('JALALI_DB_HOST', '127.0.0.1'),
        'port' => env('JALALI_DB_PORT', '5432'),
        'database' => env('JALALI_DB_DATABASE'),
        'username' => env('JALALI_DB_USERNAME'),
        'password' => env('JALALI_DB_PASSWORD'),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ],

],
```

---

## 📄 JSON Storage

To use JSON instead of a database:

```env
JALALI_EVENTS_STORAGE=json
```

The default JSON file is stored at:

```text
storage/app/jalali-events/calendar.json
```

---

## ⚙️ Publishing Configuration

Publish the package configuration:

```bash
php artisan vendor:publish --tag=jalali-events-config
```

This creates:

```text
config/jalali-events.php
```

---

## 🔧 Configuration

Available environment variables:

```env
JALALI_EVENTS_STORAGE=database

JALALI_EVENTS_DB_CONNECTION=

JALALI_EVENTS_AUTO_UPDATE=true
JALALI_EVENTS_UPDATE_FREQUENCY=monthly

JALALI_EVENTS_HTTP_TIMEOUT=15
JALALI_EVENTS_HTTP_RETRY=3
JALALI_EVENTS_HTTP_RETRY_SLEEP=1000

JALALI_EVENTS_VERIFY_SSL=true

JALALI_EVENTS_USER_AGENT="JalaliEvents Laravel Package"
```

---

## ⏰ Automatic Updates

Automatic synchronization is enabled by default.

```env
JALALI_EVENTS_AUTO_UPDATE=true
```

Available frequencies:

```env
JALALI_EVENTS_UPDATE_FREQUENCY=daily
```

```env
JALALI_EVENTS_UPDATE_FREQUENCY=weekly
```

or:

```env
JALALI_EVENTS_UPDATE_FREQUENCY=monthly
```

The default is:

```text
monthly
```

Jalali Events schedules:

```bash
php artisan jalali-events:sync
```

automatically using Laravel's scheduler.

Your Laravel application must have the Laravel scheduler configured on the server.

A typical cron entry is:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 SSL Verification

SSL verification is enabled by default:

```env
JALALI_EVENTS_VERIFY_SSL=true
```

Disabling SSL verification is not recommended in production.

If your development environment uses an internal CA or SSL inspection proxy, configure PHP/cURL with the appropriate CA certificate instead of disabling SSL verification.

---

## 🌐 Calendar Source

The default upstream calendar source is:

```text
time.ir
```

The source layer is separated from the rest of the package architecture so alternative calendar providers can be implemented in the future without changing the public API.

The package dynamically resolves the Next.js Server Action used by the upstream calendar service instead of relying on a permanently hard-coded action identifier.

> Since `time.ir` is an external service, changes to its internal implementation may require a package update.

---

## 🧩 Architecture

```text
CalendarSourceInterface
        │
        ▼
   TimeIrSource
        │
        ▼
   TimeIrParser
        │
        ▼
 CalendarUpdater
        │
        ▼
CalendarRepositoryInterface
       / \
      /   \
Database   JSON
Repository Repository
      \   /
       \ /
        ▼
 CalendarService
        │
        ▼
   JalaliEvents
```

---

## 🧪 Testing

Run the complete test suite:

```bash
composer test
```

or:

```bash
vendor/bin/phpunit
```

Current test suite:

```text
16 tests
68 assertions
```

Tests use an in-memory SQLite database for fast and isolated execution.

---

## 🛠 Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
vendor/bin/phpunit
```

Validate Composer metadata:

```bash
composer validate
```

---

## 📋 Requirements

- PHP `8.3+`
- Laravel / Illuminate `13.x`
- Composer
- One of:
  - PostgreSQL
  - MySQL / MariaDB
  - SQLite
- Internet access during calendar synchronization

The application does not require internet access when reading previously synchronized calendar data.

---

## 🗺 Roadmap

Possible future additions include:

- Additional Iranian calendar data providers
- More event classifications
- Configurable event filtering
- Additional synchronization strategies
- Cache integration
- Historical calendar datasets
- Additional Jalali date utilities

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Add or update tests.
5. Run the complete test suite.
6. Submit a pull request.

Repository:

```text
https://github.com/Pulselineir/jalali-events
```

---

## 🐛 Issues

If you find a bug or have a feature request, please open an issue:

```text
https://github.com/Pulselineir/jalali-events/issues
```

---

## 👨‍💻 Author

**Farzin Bidokhti**

GitHub: [@farzinbidokhti](https://github.com/farzinbidokhti)

Email: [pulseline.ir@gmail.com](mailto:pulseline.ir@gmail.com)

Developed and maintained under:

**Pulseline**

GitHub: [Pulselineir](https://github.com/Pulselineir)

---

## 📄 License

Jalali Events is open-source software licensed under the [MIT License](LICENSE).

---

<div align="center">

### ⭐ If Jalali Events helps your project, consider giving the repository a star.

Made with ❤️ by [Farzin Bidokhti](https://github.com/farzinbidokhti) · [Pulseline](https://github.com/Pulselineir)

</div>
