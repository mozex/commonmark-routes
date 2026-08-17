<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

function html(string $markdown): string
{
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($markdown)->getContent());
}

it('keeps a double quoted link title', function () {
    expect(html("[Home](route('home') \"Go home\")"))
        ->toBe('<p><a href="http://localhost" title="Go home">Home</a></p>');
});

it('keeps a single quoted link title', function () {
    expect(html("[Home](route('home') 'Go home')"))
        ->toBe('<p><a href="http://localhost" title="Go home">Home</a></p>');
});

it('keeps a parenthesised link title', function () {
    expect(html("[Home](route('home') (Go home))"))
        ->toBe('<p><a href="http://localhost" title="Go home">Home</a></p>');
});

it('keeps a link title when using angle brackets', function () {
    expect(html("[Home](<route('home', absolute: false)> \"Go home\")"))
        ->toBe('<p><a href="/" title="Go home">Home</a></p>');
});

it('keeps an image title', function () {
    expect(html("![Logo](asset('logo.png') \"Our logo\")"))
        ->toBe('<p><img src="http://localhost/logo.png" alt="Logo" title="Our logo" /></p>');
});

it('keeps a title when the link text is also a helper', function () {
    expect(html("[url('about')](url('about') \"About us\")"))
        ->toBe('<p><a href="http://localhost/about" title="About us">http://localhost/about</a></p>');
});
