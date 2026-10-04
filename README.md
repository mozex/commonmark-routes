# Use Laravel URL Helpers inside Markdown

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![GitHub Checks Workflow Status](https://img.shields.io/github/actions/workflow/status/mozex/commonmark-routes/checks.yml?branch=main&label=checks&style=flat-square)](https://github.com/mozex/commonmark-routes/actions/workflows/checks.yml)
[![Docs](https://img.shields.io/badge/docs-mozex.dev-10B981?style=flat-square)](https://mozex.dev/docs/commonmark-routes/v2)
[![License](https://img.shields.io/github/license/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)
[![Total Downloads](https://img.shields.io/packagist/dt/mozex/commonmark-routes.svg?style=flat-square)](https://packagist.org/packages/mozex/commonmark-routes)

A [league/commonmark](https://github.com/thephpleague/commonmark) extension that lets you use `route()`, `url()`, and `asset()` inside your Markdown content. Write links and images using the same Laravel helpers you already use in Blade, and they'll resolve to real URLs when the Markdown is converted.

> **[Read the full documentation at mozex.dev](https://mozex.dev/docs/commonmark-routes/v2)**: searchable docs, version requirements, detailed changelog, and more.

> **Note:** Helper arguments are parsed, not executed. Only literal values get through, so Markdown can't run PHP. See [Helper Arguments](#helper-arguments).

## Table of Contents

- [Installation](#installation)
- [Upgrading from 1.x](#upgrading-from-1x)
- [Usage](#usage)
  - [Links](#links)
  - [Link Titles](#link-titles)
  - [Images](#images)
  - [Helper Arguments](#helper-arguments)
  - [Relative URLs](#relative-urls)
  - [Code Blocks](#code-blocks)
  - [Spatie Laravel Markdown](#spatie-laravel-markdown)

## Support This Project

I maintain this package along with [several other open-source PHP packages](https://mozex.dev/docs) used by thousands of developers every day.

If my packages save you time or help your business, consider [**sponsoring my work on GitHub Sponsors**](https://github.com/sponsors/mozex). Your support lets me keep these packages updated, respond to issues quickly, and ship new features.

Business sponsors get logo placement in package READMEs. [**See sponsorship tiers →**](https://github.com/sponsors/mozex)

## Installation

> **Requires [PHP 8.2+](https://php.net/releases/)** - see [all version requirements](https://mozex.dev/docs/commonmark-routes/v2/requirements)

Install the package via Composer:

```bash
composer require mozex/commonmark-routes
```

Then run the install command. It publishes `config/commonmark-routes.php`, where you can switch the generated links to [relative URLs](#relative-urls):

```bash
php artisan commonmark-routes:install
```

## Upgrading from 1.x

Version 1 ran every helper call through PHP's `eval()`. Any Markdown you converted could execute arbitrary code, which is why the old README told you never to process user-submitted content. Version 2 parses the arguments instead and accepts literal values only.

Ordinary usage is unaffected. `route('product', 3)`, `url('docs/api')`, and `route('home', ['id' => 'features'])` all behave exactly as before.

What breaks is anything that isn't a literal:

```markdown
[Docs](url('docs/' . $section))
[Docs](url(config('app.docs_path')))
[Logo](asset(strtolower('Logo.png')))
```

Those now throw `Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException`. Work the value out in PHP and pass the finished Markdown to the converter.

Three smaller changes, none of which raise an error, so check your output if any apply:

- Link text that mixes prose with a helper keeps the prose now. `[Go to route('home') now](route('home'))` used to render as `<a href="…">https://domain.com</a>`, because version 1 replaced the whole text. It now renders as `<a href="…">Go to https://domain.com now</a>`. Text that was only a helper call, like `[route('home')](route('home'))`, is unaffected.
- `RoutesExtension` no longer implements `League\Config\ConfigurationAwareInterface`, and `setConfiguration()` is gone with it. The injected config was never read. This only matters if you subclassed the extension and touched `$this->configuration`.
- Helper calls inside fenced code blocks and inline code used to resolve. They don't any more. If you were relying on that, move the content out of the code block.

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

Helpers resolve in the link text too, wherever they appear:

```php
echo $converter->convert("[route('home')](route('home'))");
// <p><a href="https://domain.com">https://domain.com</a></p>

echo $converter->convert("[Go to route('home') now](route('home'))");
// <p><a href="https://domain.com">Go to https://domain.com now</a></p>
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

### Link Titles

Titles survive the rewrite. All three CommonMark forms work, on links and images alike:

```php
echo $converter->convert("[Home](route('home') \"Go home\")");
// <p><a href="https://domain.com" title="Go home">Home</a></p>

echo $converter->convert("![Logo](asset('logo.png') 'Our logo')");
// <p><img src="https://domain.com/logo.png" alt="Logo" title="Our logo" /></p>
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

### Helper Arguments

Write arguments the way you'd write them in PHP. These are the values the parser accepts:

- Strings, single or double quoted: `route('product')`, `url("about")`
- Integers and floats: `route('product', 3)`
- Booleans and null: `route('home', [], false)`
- Arrays, including nested ones: `route('search', ['filters' => ['tag' => 'php']])`
- Named arguments: `route('home', absolute: false)`

Double-quoted strings don't interpolate. `url("costs/$100")` gives you a literal `$100` in the path.

Anything else throws `Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException` with the offending source in the message. That covers variables, function calls, concatenation, and constants. It's what stops Markdown from reaching PHP.

### Relative URLs

Every helper produces a full URL by default, like `http://example.com/about`. That's what you want for email or a feed, but HTML that gets cached and served on more than one host, or a page shown both on staging and production, is better off with paths. Turn `absolute` off in `config/commonmark-routes.php`:

```php
'absolute' => false,
```

Or set `COMMONMARK_ROUTES_ABSOLUTE=false` in your `.env`. Now `[About](route('about'))` renders as `<a href="/about">`, and `asset('images/logo.png')` becomes `/images/logo.png`. Query strings stay on the path.

Only URLs on your app's own host change. An asset served from a CDN through `ASSET_URL`, or a `url()` pointing at another site, stays absolute, because a relative path would send it to the wrong server.

### Code Blocks

Helpers inside fenced code blocks and inline code are left alone, so you can document the syntax without it resolving on you:

````markdown
```php
[Home](route('home'))
```

Inline `[Home](route('home'))` stays put too.
````

Both render as literal text. This README goes through the extension unchanged.

One gap worth knowing about: indented code blocks (the four-space kind) aren't protected. Telling them apart from nested list content needs a full block parse, and guessing wrong would silently break links inside lists. Use fenced blocks when the content has helper calls in it.

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

Visit the [documentation site](https://mozex.dev/docs/commonmark-routes/v2) for searchable docs, auto-updated from this repository.

- **[AI Integration](https://mozex.dev/docs/commonmark-routes/v2/ai-integration)**: Use this package with AI coding assistants via Context7 and Laravel Boost
- **[Requirements](https://mozex.dev/docs/commonmark-routes/v2/requirements)**: PHP, Laravel, and dependency versions
- **[Changelog](https://mozex.dev/docs/commonmark-routes/v2/changelog)**: Release history with linked pull requests and diffs
- **[Contributing](https://mozex.dev/docs/commonmark-routes/v2/contributing)**: Development setup, code quality, and PR guidelines
- **[Questions & Issues](https://mozex.dev/docs/commonmark-routes/v2/questions-and-issues)**: Bug reports, feature requests, and help
- **[Security](mailto:hello@mozex.dev)**: Report vulnerabilities directly via email

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
