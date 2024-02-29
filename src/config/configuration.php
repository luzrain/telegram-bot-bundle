<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\config;

use Luzrain\TelegramBotApi\Type\Update;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/** @php-cs-fixer-ignore */
return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->addDefaultsIfNotSet()
        ->children()
            ->scalarNode('http_client')
                ->info('PSR-18 client implementation service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->scalarNode('request_factory')
                ->info('PSR-7 request factory implementation service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->scalarNode('stream_factory')
                ->info('PSR-7 stream factory implementation service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->scalarNode('api_token')
                ->info('Telegram bot api token')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->arrayNode('allowed_updates')
                ->info('A list of the update types you want your bot to receive')
                ->prototype('scalar')->end()
                    ->beforeNormalization()
                        ->always(fn ($values) => \array_map(\strval(...), $values))
                        ->end()
                    ->validate()
                    ->ifTrue(fn ($configArray) => array_diff($configArray, Update::getUpdateTypes()) !== [])
                    ->then(function ($configArray) {
                        if (array_diff($configArray, Update::getUpdateTypes()) !== []) {
                            $allowedKeys = implode(', ', Update::getUpdateTypes());
                            throw new \InvalidArgumentException(sprintf('Invalid updates list. Allowed updates: %s', $allowedKeys));
                        }
                        return $configArray;
                    })
                    ->end()
                ->end()
            ->arrayNode('webhook')
                ->info('Webhook options')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('url')
                        ->info('HTPS url to send updates to')
                        ->defaultNull()
                        ->end()
                    ->integerNode('max_connections')
                        ->info('The maximum allowed number of simultaneous HTTPS connections to the webhook. 1-100')
                        ->defaultNull()
                        ->min(1)
                        ->max(100)
                        ->end()
                    ->scalarNode('secret_token')
                        ->info('A secret token to protect webhook url from unauthorized requests')
                        ->defaultNull()
                        ->end()
                    ->scalarNode('certificate')
                        ->info('Path to public key certificate for verifying self-signed https certificate')
                        ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
