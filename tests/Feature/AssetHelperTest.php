<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

it('replaces asset in links', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Download](asset('files/doc.pdf'))")->getContent()))
        ->toBe('<p><a href="http://localhost/files/doc.pdf">Download</a></p>');
});

it('replaces asset in links and title', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[asset('files/doc.pdf')](asset('files/doc.pdf'))")->getContent()))
        ->toBe('<p><a href="http://localhost/files/doc.pdf">http://localhost/files/doc.pdf</a></p>');
});

it('replaces asset in links with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[Download](<asset('files/doc.pdf')>)")->getContent()))
        ->toBe('<p><a href="http://localhost/files/doc.pdf">Download</a></p>');
});

it('replaces asset in links and title with angle brackets', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[<asset('files/doc.pdf')>](<asset('files/doc.pdf')>)")->getContent()))
        ->toBe('<p><a href="http://localhost/files/doc.pdf">http://localhost/files/doc.pdf</a></p>');
});

it('does not replace asset when used incorrectly', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[asset('logo.png')](/logo.png)")->getContent()))
        ->toBe('<p><a href="/logo.png">asset(\'logo.png\')</a></p>');
});

it('replaces asset alongside other helpers', function () {
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    expect(trim($converter->convert("[PDF](asset('files/doc.pdf')) and [About](url('about')) and [Home](route('home'))")->getContent()))
        ->toBe('<p><a href="http://localhost/files/doc.pdf">PDF</a> and <a href="http://localhost/about">About</a> and <a href="http://localhost">Home</a></p>');
});
