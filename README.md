# Nova Media Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outl1ne/nova-media-hub.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-media-hub)
[![Total Downloads](https://img.shields.io/packagist/dt/outl1ne/nova-media-hub.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-media-hub)

This [Laravel Nova](https://nova.laravel.com) package allows you to manage media and media fields.

## Requirements

- `php: >=8.0`
- `laravel/nova: ^4.0`

## Features

- Media management via separate UI
- Media addition fields

## Screenshots

![Media index view](docs/index.png)

## Installation

Install the package in a Laravel Nova project via Composer and run migrations:

```bash
# Install nova-media-hub
composer require outl1ne/nova-media-hub

# Run migrations
php artisan migrate
```

Register the tool with Nova in the `tools()` method of the `NovaServiceProvider`:

```php
// in app/Providers/NovaServiceProvider.php

public function tools()
{
    return [
        // ...
        new \Outl1ne\NovaMediaHub\MediaHub
    ];
}
```

## TODO

- Pagination for Hub view, Choose modal view
- "User can create collections" config support
- Index field
- i18n support
- Title/alt texts and translatability
- Thumbnails for Nova (?)

## Usage

### Migrate

### Configure

### Use fields

## Credits

- [Tarvo Reinpalu](https://github.com/Tarpsvo)

## License

Nova Media Library is open-sourced software licensed under the [MIT license](LICENSE.md).
