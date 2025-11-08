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
            ->scalarNode('api_token')
                ->info('Telegram Bot API token')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->scalarNode('api_server')
                ->info('Telegram Bot API server')
                ->cannotBeEmpty()
                ->defaultValue('https://api.telegram.org')
                ->end()
            ->arrayNode('allowed_updates')
                ->info('List of update types the bot should receive. Empty to receive all update types except chat_member')
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
            ->scalarNode('http_client')
                ->info('PSR-18 http client service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->scalarNode('request_factory')
                ->info('PSR-7 request factory service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->scalarNode('stream_factory')
                ->info('PSR-7 stream factory service id')
                ->cannotBeEmpty()
                ->defaultValue('psr18.http_client')
                ->end()
            ->arrayNode('webhook')
                ->info('Webhook options')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('url')
                        ->info('HTTPS URL to which Telegram will send updates')
                        ->defaultNull()
                        ->end()
                    ->integerNode('max_connections')
                        ->info('Maximum number of simultaneous HTTPS connections allowed for the webhook (1–100)')
                        ->defaultValue(40)
                        ->min(1)
                        ->max(100)
                        ->end()
                    ->scalarNode('secret_token')
                        ->info('Secret token to protect webhook url from unauthorized requests')
                        ->defaultNull()
                        ->end()
                    ->scalarNode('certificate')
                        ->info('Path to a public key certificate for verifying a self-signed HTTPS certificate')
                        ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
