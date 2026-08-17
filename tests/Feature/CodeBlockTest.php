<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\RoutesExtension;

function convert(string $markdown): string
{
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($markdown)->getContent());
}

it('leaves helpers inside fenced code blocks alone', function () {
    expect(convert("```\n[Home](route('home'))\n```"))
        ->toBe("<pre><code>[Home](route('home'))\n</code></pre>");
});

it('leaves helpers inside fenced code blocks with an info string alone', function () {
    expect(convert("```markdown\n[Home](route('home'))\n```"))
        ->toBe('<pre><code class="language-markdown">[Home](route(\'home\'))'."\n".'</code></pre>');
});

it('leaves helpers inside tilde fenced code blocks alone', function () {
    expect(convert("~~~\n[Home](route('home'))\n~~~"))
        ->toBe("<pre><code>[Home](route('home'))\n</code></pre>");
});

it('leaves helpers inside an unclosed fenced code block alone', function () {
    expect(convert("```\n[Home](route('home'))"))
        ->toBe("<pre><code>[Home](route('home'))\n</code></pre>");
});

it('leaves helpers inside code spans alone', function () {
    expect(convert("Use `[Home](route('home'))` in markdown."))
        ->toBe("<p>Use <code>[Home](route('home'))</code> in markdown.</p>");
});

it('leaves helpers inside multi backtick code spans alone', function () {
    expect(convert("``[Home](route('home'))``"))
        ->toBe("<p><code>[Home](route('home'))</code></p>");
});

it('leaves image helpers inside code spans alone', function () {
    expect(convert("`![Logo](asset('logo.png'))`"))
        ->toBe("<p><code>![Logo](asset('logo.png'))</code></p>");
});

it('still resolves helpers around a fenced code block', function () {
    expect(convert("[Home](route('home'))\n\n```\n[Home](route('home'))\n```\n\n[About](route('about'))"))
        ->toBe(
            '<p><a href="http://localhost">Home</a></p>'."\n".
            "<pre><code>[Home](route('home'))\n</code></pre>\n".
            '<p><a href="http://localhost/about">About</a></p>'
        );
});

it('still resolves helpers around a code span on the same line', function () {
    expect(convert("`route('home')` links to [Home](route('home'))"))
        ->toBe('<p><code>route(\'home\')</code> links to <a href="http://localhost">Home</a></p>');
});

it('resolves helpers after a fence closed by a longer fence', function () {
    expect(convert("```\ncode\n````\n\n[Home](route('home'))"))
        ->toBe("<pre><code>code\n</code></pre>\n".'<p><a href="http://localhost">Home</a></p>');
});

it('resolves helpers after a tilde fence closed by a longer fence', function () {
    expect(convert("~~~\ncode\n~~~~\n\n[Home](route('home'))"))
        ->toBe("<pre><code>code\n</code></pre>\n".'<p><a href="http://localhost">Home</a></p>');
});

it('resolves helpers between unpaired backticks in separate paragraphs', function () {
    expect(convert("Cost is 3` units.\n\n[Home](route('home'))\n\nAbout 5` more."))
        ->toBe("<p>Cost is 3` units.</p>\n".'<p><a href="http://localhost">Home</a></p>'."\n".'<p>About 5` more.</p>');
});

it('protects a fenced code block written with CRLF line endings', function () {
    expect(convert("```\r\n[Home](route('home'))\r\n```\r\n\r\n[About](route('about'))"))
        ->toBe("<pre><code>[Home](route('home'))\n</code></pre>\n".'<p><a href="http://localhost/about">About</a></p>');
});

it('leaves helpers inside a code span within link text alone', function () {
    expect(convert("[`route('home')` docs](route('home'))"))
        ->toBe('<p><a href="http://localhost"><code>route(\'home\')</code> docs</a></p>');
});

it('leaves helpers inside a code span within image alt text alone', function () {
    expect(convert("![`asset('logo.png')`](asset('logo.png'))"))
        ->toBe('<p><img src="http://localhost/logo.png" alt="asset(\'logo.png\')" /></p>');
});

it('resolves a helper in link text next to a code span', function () {
    expect(convert("[`route` gives route('home')](route('home'))"))
        ->toBe('<p><a href="http://localhost"><code>route</code> gives http://localhost</a></p>');
});

it('does not treat a triple backtick code span as a fence', function () {
    expect(convert("```route('home')``` and [Home](route('home'))"))
        ->toBe('<p><code>route(\'home\')</code> and <a href="http://localhost">Home</a></p>');
});

it('handles a very long code span without giving up on the document', function () {
    $long = str_repeat('x', 50000);

    expect(convert("`{$long}`\n\n[Home](route('home'))"))
        ->toContain('<a href="http://localhost">Home</a>');
});
