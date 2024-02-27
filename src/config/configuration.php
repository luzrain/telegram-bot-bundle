<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\config;

use Luzrain\TelegramBotApi\Type\Update;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/** @php-cs-fixer-ignore */
return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('http_client')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->scalarNode('request_factory')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->scalarNode('stream_factory')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->scalarNode('api_token')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->scalarNode('secret_token')
                ->defaultNull()
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
        ->end();
};
