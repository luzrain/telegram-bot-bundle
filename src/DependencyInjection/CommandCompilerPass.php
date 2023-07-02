<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotBundle\TelegramBot\CommandMetadataProvider;
use Luzrain\TelegramBotBundle\TelegramBot\UpdateHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class CommandCompilerPass implements CompilerPassInterface
{
    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function process(ContainerBuilder $container)
    {
        $controllers = $container->findTaggedServiceIds('telegram_bot.command');
        $controllersLocatorMap = iterator_to_array($this->locatorReferenceMap(array_keys($controllers)));

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

        foreach ($controllersMap as &$row) {
            unset($row['priority']);
        }

        $container
            ->register('telegram_bot.controllers_locator', ServiceLocator::class)
            ->setArguments([$controllersLocatorMap])
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

    /**
     * @param list<class-string> $serviceClasses
     * @return \Generator<class-string, Reference>
     */
    private function locatorReferenceMap(array $serviceClasses): \Generator
    {
        foreach ($serviceClasses as $serviceClass) {
            yield $serviceClass => new Reference($serviceClass);
        }
    }
}
