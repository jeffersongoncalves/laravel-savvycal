<div class="filament-hidden">

![Laravel SavvyCal](https://raw.githubusercontent.com/jeffersongoncalves/laravel-savvycal/main/art/jeffersongoncalves-laravel-savvycal.png)

</div>

# Laravel SavvyCal

[![Tests](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/tests.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/tests.yml)
[![PHPStan](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/phpstan.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/pint.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-savvycal/actions/workflows/pint.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-savvycal.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-savvycal)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-savvycal.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-savvycal)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-savvycal.svg?style=flat-square)](LICENSE.md)

A lightweight SavvyCal REST API client for Laravel. It wraps the `api.savvycal.com` v1 endpoints behind a small static client, threads your API key, and defensively returns `null` (rather than throwing) on network failures or non-2xx responses.

## Features

- **Users** — `me()` fetches the authenticated user
- **Scheduling links** — `links()`, `link()`, `createLink()`, `updateLink()`, `deleteLink()`, `duplicateLink()`, `toggleLink()`, `linkSlots()`
- **Events** — `events()`, `event()`, `createEvent()`, `cancelEvent()`
- **Webhooks** — `webhooks()`, `createWebhook()`, `deleteWebhook()`

## Installation

```bash
composer require jeffersongoncalves/laravel-savvycal
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="laravel-savvycal-config"
```

## Configuration

Add to your `.env`:

```env
SAVVYCAL_API_KEY=sk_live_...
```

Create an API key from your [SavvyCal developer settings](https://savvycal.com/settings/developer).

### Config Options

```php
// config/savvycal.php
return [
    'token' => env('SAVVYCAL_API_KEY'),
    'timeout' => (int) env('SAVVYCAL_TIMEOUT', 8),
];
```

When `savvycal.token` is null the client falls back to `config('services.savvycal.token')`.

## Usage

```php
use JeffersonGoncalves\SavvyCal\SavvyCalClient;

// Users
$me = SavvyCalClient::me();

// Scheduling links
$links = SavvyCalClient::links(['limit' => 50]);
$link = SavvyCalClient::link('link_00000000');
$created = SavvyCalClient::createLink('Intro call', slug: 'intro', durationMinutes: 30);
SavvyCalClient::updateLink('link_00000000', ['name' => 'Discovery call']);
SavvyCalClient::duplicateLink('link_00000000');
SavvyCalClient::toggleLink('link_00000000');
SavvyCalClient::deleteLink('link_00000000');

// Availability
$slots = SavvyCalClient::linkSlots(
    id: 'link_00000000',
    startTime: '2026-09-06T00:00:00Z',
    endTime: '2026-09-13T00:00:00Z',
);

// Events
$events = SavvyCalClient::events(['limit' => 20]);
$event = SavvyCalClient::event('event_00000000');
SavvyCalClient::createEvent(
    schedulingLinkId: 'link_00000000',
    startAt: '2026-09-08T14:00:00Z',
    name: 'Jane Doe',
    email: 'jane@example.com',
);
SavvyCalClient::cancelEvent('event_00000000');

// Webhooks
$webhooks = SavvyCalClient::webhooks();
SavvyCalClient::createWebhook('https://example.com/webhooks/savvycal', ['event.created', 'event.canceled']);
SavvyCalClient::deleteWebhook('hook_00000000');
```

All methods return `array<string, mixed>|null` (or `bool` for `deleteLink()` and `deleteWebhook()`), returning `null`/`false` on any network failure or non-2xx response instead of throwing. Null arguments are stripped from the payload before it is sent, so optional fields are simply omitted.

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
