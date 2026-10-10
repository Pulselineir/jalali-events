<div align="center">

# Jalali Events

### Iranian Jalali Calendar Events & Official Holidays for Laravel

A Laravel package for fetching, storing, querying, and automatically synchronizing Iranian Jalali calendar events and official holidays.

[![Latest Stable Version](https://img.shields.io/packagist/v/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![Total Downloads](https://img.shields.io/packagist/dt/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![PHP Version](https://img.shields.io/packagist/php-v/pulseline/jalali-events.svg?style=flat-square)](https://packagist.org/packages/pulseline/jalali-events)
[![License](https://img.shields.io/github/license/Pulselineir/jalali-events?style=flat-square)](LICENSE)
[![GitHub Release](https://img.shields.io/github/v/release/Pulselineir/jalali-events?style=flat-square)](https://github.com/Pulselineir/jalali-events/releases)

</div>

---

## Features

Jalali Events provides a simple API for working with the Iranian calendar in Laravel applications.

- Fetch complete Jalali calendar years
- Store Iranian events and occasions
- Detect official holidays
- Detect weekends separately from official holidays
- Query individual Jalali dates
- Retrieve all events for a date
- Query arbitrary date ranges
- Count holidays within a range
- Synchronize calendar data from `time.ir`
- Automatic scheduled synchronization
- Database storage
- JSON storage
- PostgreSQL compatible
- MySQL and MariaDB compatible
- SQLite compatible
- Laravel auto-discovery

---

## Installation

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

## Quick Start

The package provides the `JalaliEvents` facade.

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
];
```

---

## Check Whether a Date Is a Holiday

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

## Get Events for a Date

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

## Query a Date Range

```php
$days = JalaliEvents::between(
    '1405/04/01',
    '1405/04/10'
);
```

This returns all calendar days between the two supplied Jalali dates.

---

## Holidays in a Date Range

```php
$holidays = JalaliEvents::holidaysBetween(
    '1405/04/01',
    '1405/04/31'
);
```

---

## Count Holidays

```php
$count = JalaliEvents::holidayCount(
    '1405/04/01',
    '1405/04/31'
);
```

---

## Calendar Synchronization

Synchronize a specific Jalali year:

```bash
php artisan jalali-events:sync 1405
```

Synchronize the current Jalali year:

```bash
php artisan jalali-events:sync
```

Synchronization downloads calendar data from the configured upstream source and stores the normalized result locally.

Application queries do not request data from `time.ir`.

The normal flow is:

```text
time.ir
   |
   v
Calendar Source
   |
   v
Parser
   |
   v
Normalizer
   |
   v
Calendar Updater
   |
   v
Database / JSON
   |
   v
Application API
```

This means the application queries its own local storage instead of calling the upstream website for every request.

---

## Storage

Jalali Events supports two storage drivers:

- Database
- JSON

### Database Storage

Database storage is the default:

```env
JALALI_EVENTS_STORAGE=database
```

The package creates two tables:

```text
jalali_calendar_days
jalali_events
```

The `jalali_calendar_days` table stores one record for each calendar day.

The `jalali_events` table supports multiple events for each day.

---

## PostgreSQL, MySQL, MariaDB and SQLite

By default, Jalali Events uses the Laravel application's default database connection.

For example, if the Laravel application uses PostgreSQL:

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

The same approach applies when the main Laravel project uses MySQL, MariaDB, or SQLite.

---

## Dedicated Database Connection

Calendar data can optionally be stored using a separate Laravel database connection.

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

## JSON Storage

To use JSON instead of a database:

```env
JALALI_EVENTS_STORAGE=json
```

The default JSON file is stored at:

```text
storage/app/jalali-events/calendar.json
```

---

## Publishing Configuration

Publish the package configuration:

```bash
php artisan vendor:publish --tag=jalali-events-config
```

This creates:

```text
config/jalali-events.php
```

---

## Configuration

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

## Automatic Updates

Automatic synchronization is enabled by default:

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

```env
JALALI_EVENTS_UPDATE_FREQUENCY=monthly
```

The default is:

```text
monthly
```

Jalali Events schedules the following command automatically:

```bash
php artisan jalali-events:sync
```

The Laravel scheduler must be configured on the host application.

A typical cron entry is:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## SSL Verification

SSL verification is enabled by default:

```env
JALALI_EVENTS_VERIFY_SSL=true
```

Disabling SSL verification is not recommended in production.

If the development environment uses an internal Certificate Authority or SSL inspection proxy, configure PHP and cURL with the appropriate CA certificate instead of disabling SSL verification.

---

## Calendar Source

The default upstream calendar source is:

```text
time.ir
```

The source layer is separated from the rest of the package architecture so alternative calendar providers can be implemented in future versions without changing the public API.

The package dynamically resolves the Next.js Server Action used by the upstream calendar service instead of relying on a permanently hard-coded action identifier.

Since `time.ir` is an external service, changes to its internal implementation may require a package update.

---

## Architecture

```text
CalendarSourceInterface
        |
        v
   TimeIrSource
        |
        v
   TimeIrParser
        |
        v
 CalendarUpdater
        |
        v
CalendarRepositoryInterface
       / \
      /   \
Database   JSON
Repository Repository
      \   /
       \ /
        v
 CalendarService
        |
        v
   JalaliEvents
```

---

## Testing

Install development dependencies:

```bash
composer install
```

Run the complete test suite:

```bash
composer test
```

or:

```bash
vendor/bin/phpunit
```

Validate Composer metadata:

```bash
composer validate
```

---

## Requirements

- PHP 8.3+
- Laravel / Illuminate 13.x
- Composer
- PostgreSQL, MySQL, MariaDB, or SQLite
- Internet access during calendar synchronization

The application does not require internet access when reading previously synchronized calendar data.

---

## Contributing

Contributions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Make the required changes.
4. Add or update tests where appropriate.
5. Run the complete test suite.
6. Submit a pull request.

Repository:

```text
https://github.com/Pulselineir/jalali-events
```

---

## Issues

Bug reports and feature requests can be submitted through GitHub Issues:

```text
https://github.com/Pulselineir/jalali-events/issues
```

---

## Security

For security-related issues, please avoid publishing sensitive information in a public issue.

Contact:

```text
pulseline.ir@gmail.com
```

---

## Author

**Farzin Bidokhti**

GitHub: [@farzinbidokhti](https://github.com/farzinbidokhti)

Jalali Events is developed and maintained under **Pulseline**:

[github.com/Pulselineir](https://github.com/Pulselineir)

---

## License

Jalali Events is open-source software licensed under the [MIT License](LICENSE).