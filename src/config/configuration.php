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
                ->isRequired()
                ->cannotBeEmpty()
                ->defaultValue('Psr\Http\Client\ClientInterface')
                ->end()
            ->scalarNode('request_factory')
                ->isRequired()
                ->cannotBeEmpty()
                ->defaultValue('Psr\Http\Message\RequestFactoryInterface')
                ->end()
            ->scalarNode('stream_factory')
                ->isRequired()
                ->cannotBeEmpty()
                ->defaultValue('Psr\Http\Message\StreamFactoryInterface')
                ->end()
            ->scalarNode('api_token')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->arrayNode('allowed_updates')
                ->prototype('scalar')->end()
                    ->beforeNormalization()
                    ->always(fn ($values) => array_map(strval(...), $values))
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
                        ->defaultNull()
                        ->end()
                    ->integerNode('max_connections')
                        ->defaultNull()
                        ->min(1)
                        ->max(100)
                        ->end()
                    ->scalarNode('secret_token')
                        ->defaultNull()
                        ->end()
                    ->scalarNode('certificate')
                        ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
