---
name: commonmark-routes-development
description: Work with the CommonMark Routes extension for Laravel. Use this skill when adding, modifying, or debugging route(), url(), or asset() helpers inside Markdown content, registering the RoutesExtension with CommonMark or Spatie Laravel Markdown, writing Markdown that needs dynamic Laravel URLs for links or images, or troubleshooting why a helper isn't resolving in Markdown output.
---

# CommonMark Routes Development

## When to use this skill

Use this when you're working with Markdown content that needs dynamic Laravel URLs. That includes writing Markdown with `route()`, `url()`, or `asset()` calls, registering the extension with a CommonMark environment, integrating it with Spatie's Laravel Markdown package, or debugging cases where helpers aren't resolving correctly.

## What this package does

CommonMark Routes is a [league/commonmark](https://commonmark.thephpleague.com/) extension that resolves Laravel's `route()`, `url()`, and `asset()` helpers inside Markdown before CommonMark parses it. You write `[Home](route('home'))` in your Markdown, and it becomes `[Home](https://domain.com)` before the parser ever sees it.

It intercepts the raw Markdown via CommonMark's `DocumentPreParsedEvent`, runs a regex to find helper calls, parses their arguments, calls the helper, and replaces the call with the real URL. The rewritten Markdown then flows through CommonMark normally.

Pre-parsing is deliberate. CommonMark rejects link destinations containing spaces, so `[Home](route('home', ['id' => 'x']))` would never parse as a link. Rewriting before the parser runs is what makes multi-argument helpers possible.

## Argument handling

Arguments are parsed, not executed. The parser accepts literal values only:

- Strings, single or double quoted. Double quotes don't interpolate.
- Integers and floats.
- `true`, `false`, `null`.
- Arrays, including nested and keyed ones.
- Named arguments (`absolute: false`).

Anything else throws `Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException`. That includes variables, function calls, concatenation, and constants. Version 1.x used `eval()` here and accepted arbitrary PHP; version 2 doesn't. When a user reports that an expression stopped working after upgrading, this is why. The fix is to compute the value in PHP and pass finished Markdown to the converter.

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

### Relative URLs

Helpers produce absolute URLs by default. `php artisan commonmark-routes:install` publishes `config/commonmark-routes.php`; setting `'absolute' => false` there (or `COMMONMARK_ROUTES_ABSOLUTE=false`) turns URLs on the app's own host into paths (`/about`, `/images/logo.png`, `/?q=term`). URLs on another host, such as a CDN `ASSET_URL`, stay absolute.

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

### Link titles

Titles are preserved on links and images, in all three CommonMark forms:

```markdown
[Home](route('home') "Go home")
[Home](route('home') 'Go home')
![Logo](asset('logo.png') (Our logo))
```

### Link text resolution

Helper calls in the link text resolve too, wherever they sit inside it:

```markdown
[route('home')](route('home'))
[Go to route('home') now](route('home'))
```

Produce `<a href="https://domain.com">https://domain.com</a>` and `<a href="https://domain.com">Go to https://domain.com now</a>`.

Only the matched helper call is replaced; the rest of the link text is untouched. Empty link text works as well: `[](route('home'))`.

### Mixing helpers and regular links

Helpers and standard Markdown links coexist in the same document with no issues:

```markdown
[Home](route('home')) | [Docs](url('docs')) | [Google](https://google.com)
```

Regular links pass through untouched. Only links matching the `route()`, `url()`, or `asset()` pattern get processed.

## How it works internally

Two classes do the work:

- `RoutesExtension.php` holds the extension, the match pattern, and the rewrite logic.
- `ArgumentParser.php` turns an argument string into PHP values.

The mechanism:

1. The extension registers a listener for `DocumentPreParsedEvent`, which fires before CommonMark's parser touches the Markdown.
2. The listener runs one `preg_replace_callback` over the raw Markdown.
3. The pattern's first alternative matches code regions. When it wins, the callback returns the match untouched, which is how code blocks stay intact.
4. The second alternative matches a link or image whose destination is a helper call. Named groups capture the `!` prefix, link text, helper name, arguments, angle brackets, and title.
5. The callback resolves the destination, resolves any helper calls inside the link text with a second pass, and rebuilds the link with its title.
6. CommonMark then parses the rewritten Markdown normally.

Details worth knowing when editing the pattern:

- Link text uses a recursive subpattern so balanced brackets are allowed (`route('home', ['id' => 'x'])` in the text position) while an unbalanced `]` stops the match. This is what keeps a link from swallowing the text before it.
- The arguments group recurses on itself to balance parentheses, and matches quoted strings as units so parentheses inside a string don't end the match.
- Angle brackets are captured as separate `open` and `close` groups. If only one is present, the match is returned unchanged.
- If the regex fails (a backtrack limit, say), `preg_replace_callback` returns null and the original Markdown is used. Failure leaves content alone rather than corrupting it.

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

## Code blocks

Helpers inside fenced code blocks (backtick or tilde) and inline code spans are left alone. Writing documentation about this package no longer resolves the examples in it.

Indented code blocks, the four-space kind, are not protected. Detecting them needs a full block parse, because four spaces of indentation is also how nested list content is written, and guessing wrong would silently break working links inside lists. Tell users to switch to fenced blocks.

## What doesn't work

- Helpers in the link text position without also being in the URL position won't resolve. `[route('about')](/about)` stays as-is because the destination `/about` doesn't match the helper pattern.
- Only `route()`, `url()`, and `asset()` are supported. Other Laravel helpers like `action()` or `to_route()` aren't recognized.
- Reference-style links don't work. `[Home][h]` with `[h]: route('home')` leaves the definition unresolved.
- Helpers inside HTML blocks are still rewritten.
- An undefined route name throws `RouteNotFoundException`, the same as calling `route()` anywhere else. Wrap the conversion if the Markdown isn't yours.
