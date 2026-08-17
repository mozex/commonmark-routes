<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

function doc(string $markdown): string
{
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($markdown)->getContent());
}

it('matches the README parenthesised image title claim', function () {
    expect(doc("![Logo](asset('logo.png') (Our logo))"))
        ->toBe('<p><img src="http://localhost/logo.png" alt="Logo" title="Our logo" /></p>');
});

it('matches the README nested array claim', function () {
    expect(doc("[S](route('home', ['filters' => ['tag' => 'php']]))"))
        ->toBe('<p><a href="http://localhost?filters%5Btag%5D=php">S</a></p>');
});

it('matches the README positional false claim', function () {
    expect(doc("[H](route('home', [], false))"))
        ->toBe('<p><a href="/">H</a></p>');
});

it('renders the package README without touching it', function () {
    $readme = file_get_contents(__DIR__.'/../../README.md');

    $plain = new CommonMarkConverter;
    $withExtension = new CommonMarkConverter;
    $withExtension->getEnvironment()->addExtension(new RoutesExtension);

    expect($withExtension->convert($readme)->getContent())
        ->toBe($plain->convert($readme)->getContent());
});

it('renders the boost skill without touching it', function () {
    $skill = file_get_contents(__DIR__.'/../../resources/boost/skills/commonmark-routes-development/SKILL.md');

    $plain = new CommonMarkConverter;
    $withExtension = new CommonMarkConverter;
    $withExtension->getEnvironment()->addExtension(new RoutesExtension);

    expect($withExtension->convert($skill)->getContent())
        ->toBe($plain->convert($skill)->getContent());
});
