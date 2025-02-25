# Use Laravel Routes inside markdown

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![GitHub Tests Workflow Status](https://img.shields.io/github/actions/workflow/status/mozex/commonmark-routes/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mozex/commonmark-routes/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![Total Downloads](https://img.shields.io/packagist/dt/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)

An extension for [league/commonmark](https://github.com/thephpleague/commonmark) that allows you to use Laravel routes
inside markdown, just as you would in your PHP code.

> **Warning:** This extension is intended for use in controlled environments where the markdown is trusted. Do not use
this extension for processing user-input markdown due to potential security risks.

## Table of Contents

- [Support Us](#support-us)
- [Installation](#installation)
- [Usage](#usage)
  - [Basic Usage](#usage)
  - [Spatie Laravel Markdown](#spatie-laravel-markdown)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

## Support us

Creating and maintaining open-source projects requires significant time and effort. Your support will help enhance the project and enable further contributions to the PHP community.

Sponsorship can be made through the [GitHub Sponsors](https://github.com/sponsors/mozex) program. Just click the "**[Sponsor](https://github.com/sponsors/mozex)**" button at the top of this repository. Any amount is greatly appreciated, even a contribution as small as $1 can make a big difference and will go directly towards developing and improving this package.

Thank you for considering sponsoring. Your support truly makes a difference!

## Installation

> **Requires [PHP 8.1+](https://php.net/releases/)**

You can install the package via composer:

```bash
composer require mozex/commonmark-routes
```

## Usage

Register RoutesExtension as a CommonMark extension and use the route function instead of URLs in your markdown, just as
you would in your PHP code.

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

$converter = new CommonMarkConverter($environment);
$converter->getEnvironment()->addExtension(new RoutesExtension());

echo $converter->convert("[Home](route('home'))");
// Output: <p><a href="https://domain.com">Home</a></p>

echo $converter->convert("[Home](<route('home')>)");
// Output: <p><a href="https://domain.com">Home</a></p>

echo $converter->convert("[Home](route('home', absolute: false))");
// Output: <p><a href="/">Home</a></p>

echo $converter->convert("[Product](route('product', 3))");
// Output: <p><a href="https://domain.com/product/3">Product</a></p>

echo $converter->convert("[Features](route('home', ['id' => 'features']))");
// Output: <p><a href="https://domain.com?id=features">Features</a></p>

echo $converter->convert("[Features](route('home', ['id' => 'features'], false))");
// Output: <p><a href="/?id=features">Features</a></p>
```

For more information on CommonMark extensions and environments, refer to the [CommonMark documentation](https://commonmark.thephpleague.com/2.4/basic-usage/).

### Spatie Laravel Markdown

When using the [Laravel Markdown](https://github.com/spatie/laravel-markdown/) package, you may register the extension in `config/markdown.php`:

```php
/*
 * These extensions should be added to the markdown environment. A valid
 * extension implements League\CommonMark\Extension\ExtensionInterface
 *
 * More info: https://commonmark.thephpleague.com/2.4/extensions/overview/
 */
'extensions' => [
    Mozex\CommonMarkRoutes\RoutesExtension::class,
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mozex](https://github.com/mozex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
