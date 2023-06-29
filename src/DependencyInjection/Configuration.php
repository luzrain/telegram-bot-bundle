<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotApi\Type\Update;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('telegram_bot');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress UndefinedMethod */
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
                ->scalarNode('secret_token')
                    ->defaultNull()
                    ->end()
                ->arrayNode('allowed_updates')
                    ->prototype('scalar')->end()
                    ->beforeNormalization()
                    ->always(fn ($values) => array_map(strval(...), $values))
                    ->end()
                    ->validate()
                    ->ifTrue(fn ($configArray) => array_diff($configArray, $this->getAllowedUpdates()) !== [])
                    ->then(function ($configArray) {
                        if (array_diff($configArray, $this->getAllowedUpdates()) !== []) {
                            $allowedKeys = implode(', ', $this->getAllowedUpdates());
                            throw new \InvalidArgumentException(sprintf('Invalid updates list. Allowed updates: %s', $allowedKeys));
                        }
                        return $configArray;
                    })
                    ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * @return list<string>
     */
    private function getAllowedUpdates(): array
    {
        return [
            Update::MESSAGE_TYPE,
            Update::EDITED_MESSAGE_TYPE,
            Update::CHANNEL_POST_TYPE,
            Update::EDITED_CHANNEL_POST_TYPE,
            Update::INLINE_QUERY_TYPE,
            Update::CHOSEN_INLINE_RESULT_TYPE,
            Update::CALLBACK_QUERY_TYPE,
            Update::SHIPPING_QUERY_TYPE,
            Update::PRE_CHECKOUT_QUERY_TYPE,
            Update::POLL_TYPE,
            Update::POLL_ANSWER_TYPE,
            Update::MY_CHAT_MEMBER_TYPE,
            Update::CHAT_MEMBER_TYPE,
            Update::CHAT_JOIN_REQUEST_TYPE,
        ];
    }
}
