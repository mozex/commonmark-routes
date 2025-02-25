<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

it('will not replace normal relative link route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[About](/about)')->getContent()))
        ->toBe('<p><a href="/about">About</a></p>');
});

it('will not replace normal absolute link route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('[Google](https://google.com)')->getContent()))
        ->toBe('<p><a href="https://google.com">Google</a></p>');
});

it('replaces route in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](route('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a></p>');
});

it('replaces route in links with parameter', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Product](route('product', 1))")->getContent()))
        ->toBe('<p><a href="http://localhost/product/1">Product</a></p>');
});

it('replaces route in links with query string', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', ['id' => 'features']))")->getContent()))
        ->toBe('<p><a href="http://localhost?id=features">Home</a></p>');
});

it('replaces route in links as relative url', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', ['id' => 'features'], false))")->getContent()))
        ->toBe('<p><a href="/?id=features">Home</a></p>');
});

it('replaces route with named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](route('home', absolute: false))")->getContent()))
        ->toBe('<p><a href="/">Home</a></p>');
});

it('replaces route with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](<route('home')>)")->getContent()))
        ->toBe('<p><a href="http://localhost">Home</a></p>');
});

it('replaces route with angle brackets and named arguments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Home](<route('home', absolute: false)>)")->getContent()))
        ->toBe('<p><a href="/">Home</a></p>');
});
