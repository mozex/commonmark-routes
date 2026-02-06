<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

it('will not replace normal relative link route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[About](/about)')->getContent()))
        ->toBe('<p><a href="/about">About</a></p>');
});

it('will not replace normal relative link and title route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[/about](/about)')->getContent()))
        ->toBe('<p><a href="/about">/about</a></p>');
});

it('will not replace normal absolute link route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[Google](https://google.com)')->getContent()))
        ->toBe('<p><a href="https://google.com">Google</a></p>');
});

it('will not replace normal absolute link and title route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[https://google.com](https://google.com)')->getContent()))
        ->toBe('<p><a href="https://google.com">https://google.com</a></p>');
});

it('will not replace incorrect order links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('about')](/about)")->getContent()))
        ->toBe('<p><a href="/about">route(\'about\')</a></p>');
});

it('replaces route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](route('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a></p>');
});

it('replaces route in links and title', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('about')](route('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">http://localhost/about</a></p>');
});

it('replaces route in links with parameter', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Product](route('product', 1))")->getContent()))
        ->toBe('<p><a href="http://localhost/product/1">Product</a></p>');
});

it('replaces route in links and title with parameter', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('product', 1)](route('product', 1))")->getContent()))
        ->toBe('<p><a href="http://localhost/product/1">http://localhost/product/1</a></p>');
});

it('replaces route in links with query string', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', ['id' => 'features']))")->getContent()))
        ->toBe('<p><a href="http://localhost?id=features">Home</a></p>');
});

it('replaces route in links and title with query string', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('home', ['id' => 'features'])](route('home', ['id' => 'features']))")->getContent()))
        ->toBe('<p><a href="http://localhost?id=features">http://localhost?id=features</a></p>');
});

it('replaces route in links as relative url', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', ['id' => 'features'], false))")->getContent()))
        ->toBe('<p><a href="/?id=features">Home</a></p>');
});

it('replaces route in links and title as relative url', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('home', ['id' => 'features'], false)](route('home', ['id' => 'features'], false))")->getContent()))
        ->toBe('<p><a href="/?id=features">/?id=features</a></p>');
});

it('replaces route with named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', absolute: false))")->getContent()))
        ->toBe('<p><a href="/">Home</a></p>');
});

it('replaces route and title with named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[route('home', absolute: false)](route('home', absolute: false))")->getContent()))
        ->toBe('<p><a href="/">/</a></p>');
});

it('replaces route with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](<route('home')>)")->getContent()))
        ->toBe('<p><a href="http://localhost">Home</a></p>');
});

it('replaces route and title with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home')>](<route('home')>)")->getContent()))
        ->toBe('<p><a href="http://localhost">http://localhost</a></p>');
});

it('replaces route with angle brackets and named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](<route('home', absolute: false)>)")->getContent()))
        ->toBe('<p><a href="/">Home</a></p>');
});

it('replaces route and title with angle brackets and named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home', absolute: false)>](<route('home', absolute: false)>)")->getContent()))
        ->toBe('<p><a href="/">/</a></p>');
});

it('replaces route and title with angle brackets and named arguments and some text', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home', absolute: false)>](<route('home', absolute: false)>) some text")->getContent()))
        ->toBe('<p><a href="/">/</a> some text</p>');
});

it('replaces route and title with angle brackets and named arguments multiple times and some text', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home', absolute: false)>](<route('home', absolute: false)>) some text [<route('home', absolute: false)>](<route('home', absolute: false)>)")->getContent()))
        ->toBe('<p><a href="/">/</a> some text <a href="/">/</a></p>');
});

it('replaces route and title with and without angle brackets and with and without named arguments multiple times and some text', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home', absolute: false)>](<route('home', absolute: false)>) some text [<route('product', 1)>](route('product', 1))")->getContent()))
        ->toBe('<p><a href="/">/</a> some text <a href="http://localhost/product/1">http://localhost/product/1</a></p>');
});

it('replaces route and title with and without angle brackets and with and without named arguments multiple times and some text with default links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<route('home', absolute: false)>](<route('home', absolute: false)>) some text [<route('product', 1)>](route('product', 1)) some other text [About](/about)")->getContent()))
        ->toBe('<p><a href="/">/</a> some text <a href="http://localhost/product/1">http://localhost/product/1</a> some other text <a href="/about">About</a></p>');
});
