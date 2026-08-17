<?php

namespace Mozex\CommonMarkRoutes;

use Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException;

/**
 * Turns the argument list of a helper call into PHP values.
 *
 * Only literals are accepted: strings, integers, floats, booleans, null, and
 * arrays of those, optionally named. Anything else - variables, concatenation,
 * nested function calls - is rejected instead of being executed.
 */
class ArgumentParser
{
    protected int $position = 0;

    protected int $length;

    final protected function __construct(protected string $source)
    {
        $this->length = strlen($source);
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function parse(string $arguments): array
    {
        return (new static($arguments))->argumentList();
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function argumentList(): array
    {
        $arguments = [];

        $this->skipWhitespace();

        while (! $this->finished()) {
            $name = $this->argumentName();
            $value = $this->value();

            if ($name === null) {
                $arguments[] = $value;
            } else {
                $arguments[$name] = $value;
            }

            $this->skipWhitespace();

            if (! $this->take(',')) {
                break;
            }

            $this->skipWhitespace();
        }

        if (! $this->finished()) {
            $this->fail();
        }

        return $arguments;
    }

    protected function argumentName(): ?string
    {
        $matched = $this->match('/\G([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)[ \t\r\n]*:(?!:)/');

        return $matched === null ? null : $matched[1];
    }

    protected function value(): mixed
    {
        $this->skipWhitespace();

        return match ($this->current()) {
            "'", '"' => $this->string(),
            '[' => $this->array(),
            default => $this->scalar(),
        };
    }

    protected function string(): string
    {
        $singleQuoted = $this->current() === "'";

        $matched = $singleQuoted
            ? $this->match('/\G\'((?:[^\'\\\\]|\\\\.)*)\'/s')
            : $this->match('/\G"((?:[^"\\\\]|\\\\.)*)"/s');

        if ($matched === null) {
            $this->fail();
        }

        return $this->unescape($matched[1], $singleQuoted);
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function array(): array
    {
        $this->position++;

        $items = [];

        while (true) {
            $this->skipWhitespace();

            if ($this->take(']')) {
                return $items;
            }

            if ($this->finished()) {
                $this->fail();
            }

            $value = $this->value();

            $this->skipWhitespace();

            if ($this->take('=>')) {
                $items[$this->key($value)] = $this->value();
            } else {
                $items[] = $value;
            }

            $this->skipWhitespace();

            if (! $this->take(',') && ! $this->matches(']')) {
                $this->fail();
            }
        }
    }

    protected function scalar(): int|float|bool|null
    {
        if ($this->match('/\Gtrue\b/i') !== null) {
            return true;
        }

        if ($this->match('/\Gfalse\b/i') !== null) {
            return false;
        }

        if ($this->match('/\Gnull\b/i') !== null) {
            return null;
        }

        $matched = $this->match('/\G[+-]?(?:\d+\.\d*|\.\d+|\d+)(?:[eE][+-]?\d+)?/');

        if ($matched === null) {
            $this->fail();
        }

        return preg_match('/[.eE]/', $matched[0]) === 1
            ? (float) $matched[0]
            : (int) $matched[0];
    }

    protected function key(mixed $value): int|string
    {
        if (is_int($value) || is_string($value)) {
            return $value;
        }

        $this->fail();
    }

    protected function unescape(string $value, bool $singleQuoted): string
    {
        $replacements = $singleQuoted
            ? ['\\\\' => '\\', "\\'" => "'"]
            : ['\\\\' => '\\', '\\"' => '"', '\\$' => '$', '\\n' => "\n", '\\r' => "\r", '\\t' => "\t"];

        return preg_replace_callback(
            '/\\\\./s',
            fn (array $match): string => $replacements[$match[0]] ?? $match[0],
            $value
        ) ?? $value;
    }

    /**
     * @return array<int|string, string>|null
     */
    protected function match(string $pattern): ?array
    {
        if (preg_match($pattern, $this->source, $matches, 0, $this->position) !== 1) {
            return null;
        }

        $this->position += strlen($matches[0]);

        return $matches;
    }

    protected function take(string $token): bool
    {
        if (! $this->matches($token)) {
            return false;
        }

        $this->position += strlen($token);

        return true;
    }

    protected function matches(string $token): bool
    {
        return substr($this->source, $this->position, strlen($token)) === $token;
    }

    protected function current(): ?string
    {
        return $this->source[$this->position] ?? null;
    }

    protected function skipWhitespace(): void
    {
        $this->position += strspn($this->source, " \t\r\n", $this->position);
    }

    protected function finished(): bool
    {
        return $this->position >= $this->length;
    }

    protected function fail(): never
    {
        throw InvalidHelperArgumentsException::at($this->source, $this->position);
    }
}
