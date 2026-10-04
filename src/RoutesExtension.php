<?php

namespace Mozex\CommonMarkRoutes;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Input\MarkdownInput;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class RoutesExtension implements ConfigurationAwareInterface, ExtensionInterface
{
    protected string $pattern = '/(!)?\[(.+)]\(<?(route|url|asset)\((.+)\)>?\)/Us';

    protected ConfigurationInterface $configuration;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(
            eventClass: DocumentPreParsedEvent::class,
            listener: $this->onPreParsed(...)
        );
    }

    public function onPreParsed(DocumentPreParsedEvent $event): void
    {
        $event->replaceMarkdown(
            new MarkdownInput(
                str($event->getMarkdown()->getContent())
                    ->replaceMatches(
                        $this->pattern,
                        $this->replaceWithUrl(...)
                    )
            )
        );
    }

    /**
     * @param  array<string>  $matches
     */
    private function replaceWithUrl(array $matches): string
    {
        $prefix = $matches[1];
        $linkText = $matches[2];
        $function = $matches[3];
        $arguments = $matches[4];

        preg_match('/<?(route|url|asset)\((.+)\)>?/Us', $linkText, $textMatch);

        if (isset($textMatch[1], $textMatch[2])) {
            $linkText = $this->resolve($textMatch[1], $textMatch[2]);
        }

        $resolvedUrl = $this->resolve($function, $arguments);

        return "{$prefix}[{$linkText}]({$resolvedUrl})";
    }

    public function resolve(string $function, string $arguments): string
    {
        /** @phpstan-ignore argument.type */
        return $this->relative(eval("return {$function}({$arguments});"));
    }

    /**
     * With the absolute option off, a URL on this app's own root loses its
     * scheme and host. Anything on another host (a CDN behind ASSET_URL, say)
     * stays absolute, since a relative path would point at the wrong server.
     */
    protected function relative(string $url): string
    {
        if ((bool) config('commonmark-routes.absolute', true)) {
            return $url;
        }

        $root = rtrim(url('/'), '/');

        if (! str_starts_with($url, $root)) {
            return $url;
        }

        $rest = substr($url, strlen($root));

        if ($rest === '') {
            return '/';
        }

        // A longer host that merely starts with ours (example.com.evil).
        if (! in_array($rest[0], ['/', '?', '#'], true)) {
            return $url;
        }

        return $rest[0] === '/' ? $rest : '/'.$rest;
    }
}
