<?php

use League\CommonMark\CommonMarkConverter;
use Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException;
use Mozex\CommonMarkRoutes\RoutesExtension;

function markdown(string $source): string
{
    $converter = new CommonMarkConverter;
    $converter->getEnvironment()->addExtension(new RoutesExtension);

    return trim($converter->convert($source)->getContent());
}

it('does not execute nested function calls', function () {
    markdown("[X](url(strtoupper('about')))");
})->throws(InvalidHelperArgumentsException::class);

it('does not execute string concatenation', function () {
    markdown("[X](url('a' . 'b'))");
})->throws(InvalidHelperArgumentsException::class);

it('does not execute chained statements', function () {
    expect(markdown("[X](url('a'); phpinfo())"))
        ->toBe("<p>[X](url('a'); phpinfo())</p>");
});

it('does not execute file system calls', function () {
    markdown("[X](asset(file_get_contents('/etc/passwd')))");
})->throws(InvalidHelperArgumentsException::class);

it('does not evaluate variables', function () {
    markdown('[X](url($_SERVER))');
})->throws(InvalidHelperArgumentsException::class);

it('does not resolve a helper without a path', function () {
    markdown('[X](url())');
})->throws(InvalidHelperArgumentsException::class);

it('treats a dollar sign in a path as a literal', function () {
    expect(markdown('[X](url("costs/$100"))'))
        ->toBe('<p><a href="http://localhost/costs/$100">X</a></p>');
});

it('still resolves valid literal arguments', function () {
    expect(markdown("[X](route('product', 3))"))
        ->toBe('<p><a href="http://localhost/product/3">X</a></p>');
});
