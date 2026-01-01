<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\config;

use Luzrain\TelegramBotBundle\CommandMetadataProvider;
use Luzrain\TelegramBotBundle\UpdateHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

return new class implements CompilerPassInterface {
    public function process(ContainerBuilder $container): void
    {
        $controllers = $container->findTaggedServiceIds('telegram_bot.controller');

        $controllersMap = [];
        foreach ($controllers as $controller) {
            foreach ($controller as $attributeEntry) {
                $controllersMap[] = $attributeEntry;
            }
        }

        // Commands have the highest priority by default
        \usort($controllersMap, static fn(array $a, array $b) => \str_starts_with($a['value'], '/') ? -1 : 1);

        // Sort by priority
        \usort($controllersMap, static fn(array $a, array $b) => $b['priority'] <=> $a['priority']);

        $container
            ->register('telegram_bot.controllers_locator', ServiceLocator::class)
            ->addTag('container.service_locator')
            ->setArguments([$this->referenceMap(\array_keys($controllers))])
        ;

        $container
            ->register('telegram_bot.update_handler', UpdateHandler::class)
            ->setArguments([
                new Reference('telegram_bot.client_api'),
                new Reference('telegram_bot.controllers_locator'),
                $controllersMap,
            ])
        ;

        $container
            ->register('telegram_bot.command_metadata_provider', CommandMetadataProvider::class)
            ->setArguments([\array_unique(\array_column($controllersMap, 'controller'))])
        ;
    }

    private function referenceMap(array $serviceClasses): array
    {
        $result = [];
        foreach ($serviceClasses as $serviceClass) {
            $result[$serviceClass] = new Reference($serviceClass);
        }
        return $result;
    }
};
