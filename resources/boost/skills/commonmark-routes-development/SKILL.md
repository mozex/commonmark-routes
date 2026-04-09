---
name: commonmark-routes-development
description: Work with the CommonMark Routes extension for Laravel. Use this skill when adding, modifying, or debugging route(), url(), or asset() helpers inside Markdown content, registering the RoutesExtension with CommonMark or Spatie Laravel Markdown, writing Markdown that needs dynamic Laravel URLs for links or images, or troubleshooting why a helper isn't resolving in Markdown output.
---

# CommonMark Routes Development

## When to use this skill

Use this when you're working with Markdown content that needs dynamic Laravel URLs. That includes writing Markdown with `route()`, `url()`, or `asset()` calls, registering the extension with a CommonMark environment, integrating it with Spatie's Laravel Markdown package, or debugging cases where helpers aren't resolving correctly.

## What this package does

CommonMark Routes is a [league/commonmark](https://commonmark.thephpleague.com/) extension that resolves Laravel's `route()`, `url()`, and `asset()` helpers inside Markdown before CommonMark parses it. You write `[Home](route('home'))` in your Markdown, and it becomes `[Home](https://domain.com)` before the parser ever sees it.

It intercepts the raw Markdown via CommonMark's `DocumentPreParsedEvent`, runs a regex to find helper calls, evaluates them with PHP, and replaces them with real URLs. The parsed Markdown then flows through CommonMark normally.

## Security constraint

The extension evaluates helper calls from Markdown source using PHP's evaluation capabilities. Only use it with trusted, controlled content. Never process user-submitted Markdown with this extension. This isn't a theoretical risk; arbitrary PHP runs with no sanitization.

## Registering the extension

### Standalone CommonMark

```php
use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

$converter = new CommonMarkConverter();
$converter->getEnvironment()->addExtension(new RoutesExtension());
```

### Spatie Laravel Markdown

Register it in `config/markdown.php`:

```php
'extensions' => [
    Mozex\CommonMarkRoutes\RoutesExtension::class,
],
```

No configuration or publishing needed. The extension is self-contained.

## Markdown syntax

Three helpers are supported: `route()`, `url()`, and `asset()`. Each works identically to its Laravel counterpart.

### Links

```markdown
[Home](route('home'))
[Product](route('product', 3))
[Features](route('home', ['id' => 'features']))
[Home](route('home', absolute: false))
[About](url('about'))
[Docs](url('docs/getting-started'))
[Download PDF](asset('files/doc.pdf'))
```

### Images

Same syntax, just add `!` at the front:

```markdown
![Logo](asset('images/logo.png'))
![Banner](url('images/banner.jpg'))
![Product](route('product', 3))
```

The `asset()` helper is the most useful for images. On environments like Laravel Vapor where assets are served from S3 or CloudFront, `asset()` gives the correct absolute URL instead of a broken relative path.

### Angle brackets for complex arguments

When arguments contain characters that conflict with Markdown parsing (named arguments with colons, arrays with brackets), wrap the entire helper call in angle brackets:

```markdown
[Home](<route('home', absolute: false)>)
[Features](<route('home', ['id' => 'features'])>)
```

This tells CommonMark to treat everything inside `< >` as a single URL, preventing parsing conflicts.

### Link text resolution

When a helper appears as both the link text and the URL, both resolve:

```markdown
[route('home')](route('home'))
```

Produces: `<a href="https://domain.com">https://domain.com</a>`

The extension checks the link text separately for helper calls and resolves them independently.

### Mixing helpers and regular links

Helpers and standard Markdown links coexist in the same document with no issues:

```markdown
[Home](route('home')) | [Docs](url('docs')) | [Google](https://google.com)
```

Regular links pass through untouched. Only links matching the `route()`, `url()`, or `asset()` pattern get processed.

## How it works internally

The core logic lives in `RoutesExtension.php`. Here's the mechanism:

1. The extension registers a listener for `DocumentPreParsedEvent`, which fires before CommonMark's parser touches the Markdown.
2. The listener runs a regex against the raw Markdown: `/(!)?\[(.+)]\(<?(route|url|asset)\((.+)\)>?\)/Us`
3. For each match, it calls the helper function to get the resolved URL.
4. It also checks if the link text itself contains a helper call, and resolves that separately.
5. The Markdown gets replaced with resolved URLs, then CommonMark parses it normally.

The regex captures:
- Group 1: `!` prefix (images vs links)
- Group 2: Link text or alt text
- Group 3: Helper function name (`route`, `url`, or `asset`)
- Group 4: Arguments passed to the helper

The `U` flag makes quantifiers non-greedy. The `s` flag lets `.` match newlines.

## Common patterns

### Markdown-based documentation with dynamic routes

```php
$markdown = <<<'MD'
Visit your [dashboard](route('dashboard')) to get started.

Download the [user guide](asset('docs/guide.pdf')) for detailed instructions.

Check the [API documentation](url('docs/api')) for endpoint details.
MD;

$converter = new CommonMarkConverter();
$converter->getEnvironment()->addExtension(new RoutesExtension());
echo $converter->convert($markdown);
```

### CMS or static page rendering

When rendering Markdown stored in a database or flat files, the extension lets content authors use Laravel routes without hardcoding URLs. Routes can change, slugs can update, and the Markdown stays valid as long as the route names exist.

### CDN asset references in Markdown

On Vapor or any CDN-backed setup, relative asset paths break. Instead of `![Logo](/images/logo.png)`, use `![Logo](asset('images/logo.png'))` to get the correct CDN URL.

## What doesn't work

- Helpers in the link text position without also being in the URL position won't resolve. `[route('about')](/about)` stays as-is because the URL `/about` doesn't match the helper pattern.
- Only `route()`, `url()`, and `asset()` are supported. Other Laravel helpers like `action()` or `to_route()` aren't recognized.
- Nested or chained function calls beyond simple helper signatures aren't supported. Stick to the same arguments you'd pass in Blade.
