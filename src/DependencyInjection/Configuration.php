<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('telegram_bot');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
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
        ->end();

        return $treeBuilder;
    }
}
