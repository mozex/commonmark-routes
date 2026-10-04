<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

function convertRelative(string $markdown): string
{
    config(['commonmark-routes.absolute' => false]);

    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($markdown)->getContent());
}

it('keeps urls absolute by default', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](route('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a></p>');
});

it('makes route links relative when absolute is off', function () {
    expect(convertRelative("[About](route('about'))"))->toBe('<p><a href="/about">About</a></p>')
        ->and(convertRelative("[Home](route('home'))"))->toBe('<p><a href="/">Home</a></p>')
        ->and(convertRelative("[Product](route('product', 3))"))->toBe('<p><a href="/product/3">Product</a></p>');
});

it('makes url and asset links relative when absolute is off', function () {
    expect(convertRelative("[Docs](url('docs/api'))"))->toBe('<p><a href="/docs/api">Docs</a></p>')
        ->and(convertRelative("![Logo](asset('images/logo.png'))"))->toBe('<p><img src="/images/logo.png" alt="Logo" /></p>');
});

it('keeps the query string on a relative url', function () {
    expect(convertRelative("[Search](route('home', ['q' => 'term']))"))->toBe('<p><a href="/?q=term">Search</a></p>')
        ->and(convertRelative("[Section](route('about', ['id' => 'features']))"))->toBe('<p><a href="/about?id=features">Section</a></p>');
});

it('resolves helpers in link text to relative urls too', function () {
    expect(convertRelative("[url('about')](url('about'))"))->toBe('<p><a href="/about">/about</a></p>');
});

it('keeps an asset on another host absolute', function () {
    config(['app.asset_url' => 'https://cdn.example.com']);
    app()->forgetInstance('url');

    expect(convertRelative("![Logo](asset('images/logo.png'))"))
        ->toBe('<p><img src="https://cdn.example.com/images/logo.png" alt="Logo" /></p>');
});

it('keeps a url on a host that only starts with the app host absolute', function () {
    expect(convertRelative("[Elsewhere](url('http://localhost.example.com/page'))"))
        ->toBe('<p><a href="http://localhost.example.com/page">Elsewhere</a></p>');
});
