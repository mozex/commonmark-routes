<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

function render(string $markdown): string
{
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($markdown)->getContent());
}

it('keeps preceding regular links when a helper link resolves its own text', function () {
    expect(render("[About](/about) and [route('about')](route('about'))"))
        ->toBe('<p><a href="/about">About</a> and <a href="http://localhost/about">http://localhost/about</a></p>');
});

it('keeps preceding regular images when a helper link resolves its own text', function () {
    expect(render("![Logo](/logo.png) then [url('about')](url('about'))"))
        ->toBe('<p><img src="/logo.png" alt="Logo" /> then <a href="http://localhost/about">http://localhost/about</a></p>');
});

it('resolves only the helper part of the link text', function () {
    expect(render("[Go to route('home') now](route('home'))"))
        ->toBe('<p><a href="http://localhost">Go to http://localhost now</a></p>');
});

it('resolves multiple helpers inside a single link text', function () {
    expect(render("[route('home') and route('about')](route('home'))"))
        ->toBe('<p><a href="http://localhost">http://localhost and http://localhost/about</a></p>');
});

it('supports empty link text', function () {
    expect(render("[](route('home'))"))
        ->toBe('<p><a href="http://localhost"></a></p>');
});

it('supports empty image alt text', function () {
    expect(render("![](asset('logo.png'))"))
        ->toBe('<p><img src="http://localhost/logo.png" alt="" /></p>');
});

it('supports balanced brackets in link text', function () {
    expect(render("[See [1] for details](route('home'))"))
        ->toBe('<p><a href="http://localhost">See [1] for details</a></p>');
});

it('leaves a helper link text alone when the destination is a plain url', function () {
    expect(render("[route('about')](/about)"))
        ->toBe('<p><a href="/about">route(\'about\')</a></p>');
});

it('leaves the markdown untouched when angle brackets are unbalanced', function () {
    expect(render("[Home](<route('home'))"))
        ->toBe('<p>[Home](&lt;route(\'home\'))</p>');
});
