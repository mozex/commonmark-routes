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

        if (isset($textMatch[2])) {
            $linkText = $this->resolve($textMatch[1], $textMatch[2]);
        }

        $resolvedUrl = $this->resolve($function, $arguments);

        return "{$prefix}[{$linkText}]({$resolvedUrl})";
    }

    public function resolve(string $function, string $arguments): string
    {
        /** @phpstan-ignore return.type */
        return eval("return {$function}({$arguments});");
    }
}
