<?php

namespace Mozex\CommonMarkRoutes;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Input\MarkdownInput;
use Mozex\CommonMarkRoutes\Exceptions\InvalidHelperArgumentsException;

class RoutesExtension implements ExtensionInterface
{
    /**
     * Code regions come first so that helper calls inside fenced code blocks and
     * code spans are handed back untouched. Everything after the first
     * alternation is a link or image whose destination is a helper call.
     */
    protected string $pattern = <<<'REGEX'
        /
        (?<code>
            ^ [ ]{0,3} (?<btfence>`{3,}) [^\n`]* $
            (?: \n [\s\S]*? \n [ ]{0,3} \k<btfence> `* [ \t\r]* $ | [\s\S]* \z )
          |
            ^ [ ]{0,3} (?<tfence>~{3,}) [^\n]* $
            (?: \n [\s\S]*? \n [ ]{0,3} \k<tfence> ~* [ \t\r]* $ | [\s\S]* \z )
          |
            (?<ticks>`+)
            [^\n`]*+ (?: (?: ` | \n (?! [ \t\r]* \n ) ) [^\n`]*+ )*?
            \k<ticks> (?!`)
        )
        |
        (?<image>!)?
        \[
            (?<text>
                (?: \\. | [^\[\]] | \[ (?P>text) \] )*+
            )
        \]
        \(
            [ \t]*
            (?<open><)?
            [ \t]*
            (?<function>route|url|asset)
            \(
                (?<arguments>
                    (?:
                        [^()'"]++
                      | ' (?: \\. | [^'\\] )*+ '
                      | " (?: \\. | [^"\\] )*+ "
                      | \( (?P>arguments) \)
                    )*+
                )
            \)
            (?: [ \t]* (?<close>>) )?
            (?: \s+ (?<title> "[^"]*" | '[^']*' | \( [^)]* \) ) )?
            [ \t]*
        \)
        /mx
        REGEX;

    /**
     * A bare helper call, used to resolve helpers that appear in link text.
     * Code spans come first here for the same reason they do above: a link text
     * can hold one, and the outer pattern never sees inside it.
     */
    protected string $textPattern = <<<'REGEX'
        /
        (?<code>
            (?<ticks>`+)
            [^\n`]*+ (?: (?: ` | \n (?! [ \t\r]* \n ) ) [^\n`]*+ )*?
            \k<ticks> (?!`)
        )
        |
        (?<open><)?
        (?<function>route|url|asset)
        \(
            (?<arguments>
                (?:
                    [^()'"]++
                  | ' (?: \\. | [^'\\] )*+ '
                  | " (?: \\. | [^"\\] )*+ "
                  | \( (?P>arguments) \)
                )*+
            )
        \)
        (?<close>>)?
        /x
        REGEX;

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(
            eventClass: DocumentPreParsedEvent::class,
            listener: $this->onPreParsed(...)
        );
    }

    public function onPreParsed(DocumentPreParsedEvent $event): void
    {
        $content = $event->getMarkdown()->getContent();

        $event->replaceMarkdown(
            new MarkdownInput(
                preg_replace_callback($this->pattern, $this->replaceWithUrl(...), $content) ?? $content
            )
        );
    }

    public function resolve(string $function, string $arguments): string
    {
        $parsed = ArgumentParser::parse($arguments);

        $resolved = match ($function) {
            'route' => route(...$parsed), // @phpstan-ignore argument.type
            'asset' => asset(...$parsed), // @phpstan-ignore argument.type
            default => url(...$parsed), // @phpstan-ignore argument.type
        };

        if (! is_string($resolved)) {
            throw InvalidHelperArgumentsException::unresolvable($function);
        }

        return $resolved;
    }

    /**
     * @param  array<int|string, string>  $matches
     */
    protected function replaceWithUrl(array $matches): string
    {
        $original = $matches[0] ?? '';

        if (($matches['code'] ?? '') !== '') {
            return $original;
        }

        if ($this->hasUnbalancedBrackets($matches)) {
            return $original;
        }

        $title = ($matches['title'] ?? '') === '' ? '' : ' '.$matches['title'];

        $text = $this->resolveWithin($matches['text']);
        $url = $this->resolve($matches['function'], $matches['arguments']);

        return "{$matches['image']}[{$text}]({$url}{$title})";
    }

    /**
     * Replaces only the helper calls inside the link text, leaving the rest of
     * the text alone.
     */
    protected function resolveWithin(string $text): string
    {
        return preg_replace_callback(
            $this->textPattern,
            /**
             * @param  array<int|string, string>  $matches
             */
            function (array $matches): string {
                if (($matches['code'] ?? '') !== '' || $this->hasUnbalancedBrackets($matches)) {
                    return $matches[0] ?? '';
                }

                return $this->resolve($matches['function'], $matches['arguments']);
            },
            $text
        ) ?? $text;
    }

    /**
     * @param  array<int|string, string>  $matches
     */
    protected function hasUnbalancedBrackets(array $matches): bool
    {
        return (($matches['open'] ?? '') === '') !== (($matches['close'] ?? '') === '');
    }
}
