<?php

use Mozex\CommonMarkRoutes\ArgumentParser;
use Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException;

it('parses an empty argument list', function () {
    expect(ArgumentParser::parse(''))->toBe([]);
    expect(ArgumentParser::parse('   '))->toBe([]);
});

it('parses single quoted strings', function () {
    expect(ArgumentParser::parse("'home'"))->toBe(['home']);
});

it('parses double quoted strings', function () {
    expect(ArgumentParser::parse('"home"'))->toBe(['home']);
});

it('parses escapes inside single quoted strings', function () {
    expect(ArgumentParser::parse("'it\\'s'"))->toBe(["it's"]);
    expect(ArgumentParser::parse("'a\\\\b'"))->toBe(['a\\b']);
    expect(ArgumentParser::parse("'a\\nb'"))->toBe(['a\\nb']);
});

it('parses escapes inside double quoted strings', function () {
    expect(ArgumentParser::parse('"say \\"hi\\""'))->toBe(['say "hi"']);
    expect(ArgumentParser::parse('"a\\nb"'))->toBe(["a\nb"]);
});

it('does not interpolate variables inside double quoted strings', function () {
    expect(ArgumentParser::parse('"$path"'))->toBe(['$path']);
});

it('parses integers and floats', function () {
    expect(ArgumentParser::parse('3'))->toBe([3]);
    expect(ArgumentParser::parse('-3'))->toBe([-3]);
    expect(ArgumentParser::parse('1.5'))->toBe([1.5]);
    expect(ArgumentParser::parse('1e3'))->toBe([1000.0]);
});

it('parses booleans and null', function () {
    expect(ArgumentParser::parse('true'))->toBe([true]);
    expect(ArgumentParser::parse('false'))->toBe([false]);
    expect(ArgumentParser::parse('null'))->toBe([null]);
});

it('parses multiple arguments', function () {
    expect(ArgumentParser::parse("'product', 3"))->toBe(['product', 3]);
});

it('parses list arrays', function () {
    expect(ArgumentParser::parse("['a', 'b']"))->toBe([['a', 'b']]);
});

it('parses keyed arrays', function () {
    expect(ArgumentParser::parse("['id' => 'features']"))->toBe([['id' => 'features']]);
});

it('parses nested arrays', function () {
    expect(ArgumentParser::parse("['filters' => ['tag' => 'php', 'page' => 2]]"))
        ->toBe([['filters' => ['tag' => 'php', 'page' => 2]]]);
});

it('parses trailing commas', function () {
    expect(ArgumentParser::parse("'home', ['a' => 1,],"))->toBe(['home', ['a' => 1]]);
});

it('parses named arguments', function () {
    expect(ArgumentParser::parse("'home', absolute: false"))->toBe(['home', 'absolute' => false]);
});

it('parses arguments spread across multiple lines', function () {
    expect(ArgumentParser::parse("'home',\n    ['id' => 'features'],\n    false"))
        ->toBe(['home', ['id' => 'features'], false]);
});

it('parses parentheses inside strings', function () {
    expect(ArgumentParser::parse("'files/report (final).pdf'"))->toBe(['files/report (final).pdf']);
});

it('rejects function calls', function () {
    ArgumentParser::parse("strtoupper('a')");
})->throws(InvalidHelperArgumentsException::class);

it('rejects concatenation', function () {
    ArgumentParser::parse("'a' . 'b'");
})->throws(InvalidHelperArgumentsException::class);

it('rejects variables', function () {
    ArgumentParser::parse('$path');
})->throws(InvalidHelperArgumentsException::class);

it('rejects statements', function () {
    ArgumentParser::parse("'a'; unlink('/etc/passwd')");
})->throws(InvalidHelperArgumentsException::class);

it('rejects unterminated strings', function () {
    ArgumentParser::parse("'home");
})->throws(InvalidHelperArgumentsException::class);

it('rejects unterminated arrays', function () {
    ArgumentParser::parse("['a' => 1");
})->throws(InvalidHelperArgumentsException::class);

it('rejects non scalar array keys', function () {
    ArgumentParser::parse("[['a'] => 1]");
})->throws(InvalidHelperArgumentsException::class);

it('names the offending source in the exception message', function () {
    ArgumentParser::parse("strtoupper('a')");
})->throws(InvalidHelperArgumentsException::class, "strtoupper('a')");

it('unescapes an escaped dollar sign in double quoted strings', function () {
    expect(ArgumentParser::parse('"costs/\$100"'))->toBe(['costs/$100']);
    expect(ArgumentParser::parse("'costs/\\$100'"))->toBe(['costs/\$100']);
});
