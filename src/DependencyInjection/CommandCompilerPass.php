<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotBundle\TelegramBot\WebHookHandler;
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
        foreach ($controllers as $controllerClass) {
            foreach ($controllerClass as $controllerMethod) {
                $controllersMap[] = $controllerMethod;
            }
        }

        // Commands should be first
        usort($controllersMap, fn (array $a, array $b) => $a['value'] === '' ? 1 : -1);

        $container
            ->register('telegram_bot.controllers_locator', ServiceLocator::class)
            ->setArguments([$controllersLocatorMap])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.webhook_handler', WebHookHandler::class)
            ->setArgument('$client', new Reference('telegram_bot.client_api'))
            ->setArgument('$serviceLocator', new Reference('telegram_bot.controllers_locator'))
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
