<?php

declare(strict_types=1);

use Luzrain\TelegramBotBundle\TelegramBot\CommandMetadataProvider;
use Luzrain\TelegramBotBundle\TelegramBot\UpdateHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

return new class () implements CompilerPassInterface {
    public function process(ContainerBuilder $container): void
    {
        $controllers = $container->findTaggedServiceIds('telegram_bot.command');

        $controllersMap = [];
        foreach ($controllers as $attributeEntries) {
            foreach ($attributeEntries as $attributeEntry) {
                $controllersMap[] = [
                    'event' => $attributeEntry['event'],
                    'value' => $attributeEntry['value'],
                    'controller' => $attributeEntry['controller'],
                    'priority' => $attributeEntry['priority'],
                ];
            }
        }

        // Commands have the highest priority by default
        usort($controllersMap, fn (array $a, array $b) => str_starts_with($a['value'], '/') ? -1 : 1);

        // Sort by priority
        usort($controllersMap, fn (array $a, array $b) => $b['priority'] <=> $a['priority']);

        foreach ($controllersMap as $id => $row) {
            unset($controllersMap[$id]['priority']);
        }

        $container
            ->register('telegram_bot.controllers_locator', ServiceLocator::class)
            ->setArguments([$this->referenceMap(array_keys($controllers))])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.update_handler', UpdateHandler::class)
            ->setArgument('$client', new Reference('telegram_bot.client_api'))
            ->setArgument('$serviceLocator', new Reference('telegram_bot.controllers_locator'))
            ->setArgument('$controllersMap', $controllersMap)
        ;

        $container
            ->register('telegram_bot.command_metadata_provider', CommandMetadataProvider::class)
            ->setArgument('$controllersMap', $controllersMap)
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
