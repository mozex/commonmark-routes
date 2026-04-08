# Use Laravel URL Helpers inside Markdown

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![GitHub Tests Workflow Status](https://img.shields.io/github/actions/workflow/status/mozex/commonmark-routes/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mozex/commonmark-routes/actions/workflows/tests.yml)
[![Docs](https://img.shields.io/badge/docs-mozex.dev-10B981?style=flat-square)](https://mozex.dev/docs/commonmark-routes/v1)
[![License](https://img.shields.io/github/license/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![Total Downloads](https://img.shields.io/packagist/dt/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)

A [league/commonmark](https://github.com/thephpleague/commonmark) extension that lets you use `route()`, `url()`, and `asset()` inside your Markdown content. Write links and images using the same Laravel helpers you already use in Blade, and they'll resolve to real URLs when the Markdown is converted.

> **[Read the full documentation at mozex.dev](https://mozex.dev/docs/commonmark-routes/v1)**: searchable docs, version requirements, detailed changelog, and more.

> **Warning:** This extension evaluates helper calls from the Markdown source. Only use it with trusted content that you control. Don't process user-submitted Markdown with this extension.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Links](#links)
  - [Images](#images)
  - [Spatie Laravel Markdown](#spatie-laravel-markdown)

## Support This Project

I maintain this package along with [several other open-source PHP packages](https://mozex.dev/docs) used by thousands of developers every day.

If my packages save you time or help your business, consider [**sponsoring my work on GitHub Sponsors**](https://github.com/sponsors/mozex). Your support lets me keep these packages updated, respond to issues quickly, and ship new features.

Business sponsors get logo placement in package READMEs. [**See sponsorship tiers →**](https://github.com/sponsors/mozex)

## Installation

> **Requires [PHP 8.2+](https://php.net/releases/)** - see [all version requirements](https://mozex.dev/docs/commonmark-routes/v1/requirements)

Install the package via Composer:

```bash
composer require mozex/commonmark-routes
```

## Usage

Register the extension with your CommonMark environment, then use `route()`, `url()`, or `asset()` in place of URLs in your Markdown.

```php
use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

$converter = new CommonMarkConverter();
$converter->getEnvironment()->addExtension(new RoutesExtension());
```

### Links

The `route()` helper works exactly the way it does in your PHP code. Named routes, parameters, query strings, relative URLs:

```php
echo $converter->convert("[Home](route('home'))");
// <p><a href="https://domain.com">Home</a></p>

echo $converter->convert("[Product](route('product', 3))");
// <p><a href="https://domain.com/product/3">Product</a></p>

echo $converter->convert("[Features](route('home', ['id' => 'features']))");
// <p><a href="https://domain.com?id=features">Features</a></p>

echo $converter->convert("[Home](route('home', absolute: false))");
// <p><a href="/">Home</a></p>
```

The `url()` helper generates URLs from plain paths:

```php
echo $converter->convert("[About](url('about'))");
// <p><a href="https://domain.com/about">About</a></p>

echo $converter->convert("[Docs](url('docs/getting-started'))");
// <p><a href="https://domain.com/docs/getting-started">Docs</a></p>
```

The `asset()` helper resolves static file paths through Laravel's asset pipeline. This is especially useful in environments like [Laravel Vapor](https://vapor.laravel.com) where assets are served from S3 or CloudFront and relative paths won't work:

```php
echo $converter->convert("[Download PDF](asset('files/doc.pdf'))");
// <p><a href="https://domain.com/files/doc.pdf">Download PDF</a></p>
```

When the helper is used as both the link text and the URL, the resolved URL appears in both places:

```php
echo $converter->convert("[route('home')](route('home'))");
// <p><a href="https://domain.com">https://domain.com</a></p>
```

Angle brackets work too, which can help with complex arguments:

```php
echo $converter->convert("[Home](<route('home', absolute: false)>)");
// <p><a href="/">Home</a></p>
```

You can freely mix helpers with regular Markdown links in the same document:

```php
echo $converter->convert("[Home](route('home')) | [Docs](url('docs')) | [Google](https://google.com)");
// <p><a href="https://domain.com">Home</a> | <a href="https://domain.com/docs">Docs</a> | <a href="https://google.com">Google</a></p>
```

### Images

Image syntax works the same way. Put a helper inside `![alt](...)` and it resolves just like links do:

```php
echo $converter->convert("![Logo](asset('images/logo.png'))");
// <p><img src="https://domain.com/images/logo.png" alt="Logo" /></p>

echo $converter->convert("![Banner](url('images/banner.jpg'))");
// <p><img src="https://domain.com/images/banner.jpg" alt="Banner" /></p>

echo $converter->convert("![Product](route('product', 3))");
// <p><img src="https://domain.com/product/3" alt="Product" /></p>
```

The `asset()` helper is the most common choice for images. If you're on Vapor or any setup that serves assets from a CDN, `asset()` gives you the correct absolute URL instead of a broken relative path.

Regular images without helpers pass through untouched:

```php
echo $converter->convert("![Photo](https://example.com/photo.jpg)");
// <p><img src="https://example.com/photo.jpg" alt="Photo" /></p>
```

For more details on CommonMark extensions and environments, check the [CommonMark documentation](https://commonmark.thephpleague.com/2.4/basic-usage/).

### Spatie Laravel Markdown

If you're using the [Laravel Markdown](https://github.com/spatie/laravel-markdown/) package by Spatie, register the extension in `config/markdown.php`:

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

## Resources

Visit the [documentation site](https://mozex.dev/docs/commonmark-routes/v1) for searchable docs, auto-updated from this repository.

- **[Requirements](https://mozex.dev/docs/commonmark-routes/v1/requirements)**: PHP, Laravel, and dependency versions
- **[Changelog](https://mozex.dev/docs/commonmark-routes/v1/changelog)**: Release history with linked pull requests and diffs
- **[Contributing](https://mozex.dev/docs/commonmark-routes/v1/contributing)**: Development setup, code quality, and PR guidelines
- **[Questions & Issues](https://mozex.dev/docs/commonmark-routes/v1/questions-and-issues)**: Bug reports, feature requests, and help
- **[Security](mailto:hello@mozex.dev)**: Report vulnerabilities directly via email

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
