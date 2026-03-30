<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

it('replaces url in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](url('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a></p>');
});

it('replaces url in links and title', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[url('about')](url('about'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">http://localhost/about</a></p>');
});

it('replaces url in links with path segments', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Docs](url('docs/getting-started'))")->getContent()))
        ->toBe('<p><a href="http://localhost/docs/getting-started">Docs</a></p>');
});

it('replaces url in links with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](<url('about')>)")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a></p>');
});

it('replaces url in links and title with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<url('about')>](<url('about')>)")->getContent()))
        ->toBe('<p><a href="http://localhost/about">http://localhost/about</a></p>');
});

it('does not replace url when used incorrectly', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[url('about')](/about)")->getContent()))
        ->toBe('<p><a href="/about">url(\'about\')</a></p>');
});

it('replaces url alongside regular links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](url('about')) and [Google](https://google.com)")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a> and <a href="https://google.com">Google</a></p>');
});

it('replaces url alongside route links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[About](url('about')) and [Home](route('home'))")->getContent()))
        ->toBe('<p><a href="http://localhost/about">About</a> and <a href="http://localhost">Home</a></p>');
});
