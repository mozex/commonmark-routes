<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

it('resolves route in image source', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Home](route('home'))")->getContent()))
        ->toBe('<p><img src="http://localhost" alt="Home" /></p>');
});

it('resolves route in image source and alt', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![route('home')](route('home'))")->getContent()))
        ->toBe('<p><img src="http://localhost" alt="http://localhost" /></p>');
});

it('resolves route in image source with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Home](<route('home')>)")->getContent()))
        ->toBe('<p><img src="http://localhost" alt="Home" /></p>');
});

it('resolves route in image source with parameter', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Product](route('product', 1))")->getContent()))
        ->toBe('<p><img src="http://localhost/product/1" alt="Product" /></p>');
});

it('resolves url in image source', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Photo](url('images/photo.jpg'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/photo.jpg" alt="Photo" /></p>');
});

it('resolves url in image source and alt', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![url('images/photo.jpg')](url('images/photo.jpg'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/photo.jpg" alt="http://localhost/images/photo.jpg" /></p>');
});

it('resolves url in image source with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Photo](<url('images/photo.jpg')>)")->getContent()))
        ->toBe('<p><img src="http://localhost/images/photo.jpg" alt="Photo" /></p>');
});

it('resolves asset in image source', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Logo](asset('images/logo.png'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/logo.png" alt="Logo" /></p>');
});

it('resolves asset in image source and alt', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![asset('images/logo.png')](asset('images/logo.png'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/logo.png" alt="http://localhost/images/logo.png" /></p>');
});

it('resolves asset in image source with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Logo](<asset('images/logo.png')>)")->getContent()))
        ->toBe('<p><img src="http://localhost/images/logo.png" alt="Logo" /></p>');
});

it('does not resolve regular images', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('![Logo](/images/logo.png)')->getContent()))
        ->toBe('<p><img src="/images/logo.png" alt="Logo" /></p>');
});

it('does not resolve absolute url images', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert('![Logo](https://example.com/logo.png)')->getContent()))
        ->toBe('<p><img src="https://example.com/logo.png" alt="Logo" /></p>');
});

it('resolves images alongside links with mixed helpers', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Logo](asset('images/logo.png')) and [Home](route('home'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/logo.png" alt="Logo" /> and <a href="http://localhost">Home</a></p>');
});

it('resolves multiple images with different helpers', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("![Logo](asset('images/logo.png')) ![Banner](url('images/banner.jpg'))")->getContent()))
        ->toBe('<p><img src="http://localhost/images/logo.png" alt="Logo" /> <img src="http://localhost/images/banner.jpg" alt="Banner" /></p>');
});
