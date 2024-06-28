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
    protected string $pattern = '/\[([^]]+)]\(route\((.*?)\)\)/s';

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
                        $this->replaceRouteWithUrl(...)
                    )
            )
        );
    }

    /**
     * @param  array<string>  $matches
     */
    private function replaceRouteWithUrl(array $matches): string
    {
        $linkText = $matches[1];
        $url = $this->getUrl($matches[2]);

        return "[$linkText]($url)";
    }

    public function getUrl(string $routeString): string
    {
        return eval("return route($routeString);");
    }
}
