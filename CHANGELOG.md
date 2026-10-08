# Changelog

All notable changes to `commonmark-routes` will be documented in this file.

## 2.1.0 - 2026-10-08

### What's Changed

* Drop Laravel 11 support
* Bump minimum `laravel/framework` requirement from `^11.29|^12.12|^13.0` to `^12.69|^13.30`
* Add the package banner and icon

Laravel 11 stopped receiving security fixes on March 12, 2026. Three advisories published since then ([GHSA-5vg9-5847-vvmq](https://github.com/advisories/GHSA-5vg9-5847-vvmq), [GHSA-crmm-hgp2-wgrp](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) and [GHSA-jh5r-qr3c-85q8](https://github.com/advisories/GHSA-jh5r-qr3c-85q8)) affect every Laravel 11 release and were fixed only in Laravel 12 and 13. The new minimums, 12.69.0 and 13.30.0, are the first releases that include all three fixes.

If your app is still on Laravel 11, Composer keeps you on 2.0.1.

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/2.0.1...2.1.0

## 2.0.1 - 2026-10-04

### What's Changed

* Improve package setup

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/2.0.0...2.0.1

## 2.0.0 - 2026-08-17

### Breaking Changes

* Helper arguments are parsed instead of evaluated. Only literal values are accepted: strings, numbers, booleans, null, arrays, and named arguments
* Expressions such as `url('docs/' . $section)`, `url(config('app.docs_path'))`, and `asset(strtolower('Logo.png'))` now throw `Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException`
* Helpers inside fenced code blocks and inline code are no longer resolved
* Link text that mixes prose with a helper keeps the prose. `[Go to route('home') now](route('home'))` renders as `Go to https://domain.com now` instead of `https://domain.com`
* `RoutesExtension` no longer implements `League\Config\ConfigurationAwareInterface`, and `setConfiguration()` is removed

### What's Changed

* Remove `eval()` from helper resolution, closing the arbitrary code execution path that any converted Markdown could reach
* Add `ArgumentParser` for literal helper arguments, including nested arrays, named arguments, and escapes
* Add `InvalidHelperArgumentsException`, which names the offending source in its message
* Fix a regular link placed before a helper link being deleted when the helper link resolved its own text
* Fix link and image titles being dropped, so `[Home](route('home') "Go home")` keeps its title
* Fix empty link text, so `[](route('home'))` resolves
* Fix unbalanced angle brackets producing a broken destination instead of being left alone
* Skip fenced code blocks, tilde fences, and inline code spans, including code spans inside link text
* Detect closing fences longer than their opening fence, which previously stopped every later helper from resolving
* Detect fenced code blocks written with CRLF line endings
* Stop treating unpaired backticks separated by a blank line as a code span

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.7.0...2.0.0

## 1.7.0 - 2026-04-09

### What's Changed

* Add Laravel Boost skill for AI coding assistant integration
* Update README to the new documentation structure with docs badge, documentation callout, and consolidated Resources section
* Update Context7 config with schema, project title, and description

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.6.1...1.7.0

## 1.6.1 - 2026-03-30

* update composer.json

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.6.0...1.6.1

## 1.6.0 - 2026-03-30

### What's Changed

* Add `url()` and `asset()` helper support in links
* Add image source resolution for `route()`, `url()`, and `asset()`

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.5.0...1.6.0

## 1.5.0 - 2026-03-18

* add laravel 13 support

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.4.1...1.5.0

## 1.4.1 - 2026-03-05

* bump dependencies
* drop laravel 10 support

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.4.0...1.4.1

## 1.4.0 - 2026-02-06

### What's Changed

* Improved regex and added more tests that were previously failing by @Bloafer in https://github.com/mozex/commonmark-routes/pull/7
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/8
* Drop PHP 8.1 support and bump pest to ^2.36.1 by @mozex in https://github.com/mozex/commonmark-routes/pull/11
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/9
* Bump actions/cache from 4 to 5 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/10

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.3.0...1.4.0

## 1.3.0 - 2025-08-23

### What's Changed

* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/3
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/4
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/mozex/commonmark-routes/pull/5
* Added link titles by @Bloafer in https://github.com/mozex/commonmark-routes/pull/6

### New Contributors

* @Bloafer made their first contribution in https://github.com/mozex/commonmark-routes/pull/6

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.2.0...1.3.0

## 1.2.0 - 2025-02-25

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/mozex/commonmark-routes/pull/1
* Add Laravel 12 compatibility by @mozex in https://github.com/mozex/commonmark-routes/pull/2

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.1.0...1.2.0

## 1.1.0 - 2024-06-28

* add support for angle brackets

**Full Changelog**: https://github.com/mozex/commonmark-routes/compare/1.0.0...1.1.0

## 1.0.0 - 2024-06-28

Initial Release
