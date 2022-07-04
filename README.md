# Nova Media Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outl1ne/nova-media-hub.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-media-hub)
[![Total Downloads](https://img.shields.io/packagist/dt/outl1ne/nova-media-hub.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-media-hub)

This [Laravel Nova](https://nova.laravel.com) package allows you to manage media and media fields.

## Requirements

- `php: >=8.0`
- `laravel/nova: ^4.0`

## Features

- Media Hub UI in separate view
- Media Hub field for selecting single/multiple media
- Image optimization and multiple conversions support
- File naming and path making customization

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

## Usage

### Fields

This package provides a field `MediaHubField` which allows you to select media. This saves the media as a JSON array into the database.

Example usage:

```php
use Outl1ne\NovaMediaHub\Nova\Fields\MediaHubField;

// ...

MediaHubField::make('Media', 'media')
  ->defaultCollection('products') // Define the default collection the "Choose media" modal shows
  ->multiple(), // Define whether multiple media can be selected
```

### Configure

The config file can be published using the following command:

```bash
php artisan vendor:publish --provider="Outl1ne\NovaMediaHub\MediaHubServiceProvider" --tag="config"
```

## Localization

The translation file(s) can be published by using the following command:

```bash
php artisan vendor:publish --provider="Outl1ne\NovaMediaHub\MediaHubServiceProvider" --tag="translations"
```

## Credits

- [Tarvo Reinpalu](https://github.com/Tarpsvo)

## License

Nova Media Library is open-sourced software licensed under the [MIT license](LICENSE.md).
