<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnMemberBanned;
use Luzrain\TelegramBotBundle\Attribute\OnMessage;
use Luzrain\TelegramBotBundle\TelegramBot\WebHookHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TelegramBot\Api\Client;

final class CommandCompilerPass implements CompilerPassInterface
{
    private const FIND_ATTRIBUTES = [
        OnCommand::class,
        OnMemberBanned::class,
        OnMessage::class
    ];

    public function process(ContainerBuilder $container)
    {
        $serviceClasses = array_keys($container->findTaggedServiceIds('telegram_bot.command'));
        $controllersMap = iterator_to_array($this->controllerMap($serviceClasses, $container->getReflectionClass(...)));
        $locatorReferenceMap = iterator_to_array($this->locatorReferenceMap($serviceClasses));

        // Commands should be first
        uksort($controllersMap, fn (string $a) => !str_starts_with($a, '/'));

        $container
            ->register('telegram_bot.controllers_locator', ServiceLocator::class)
            ->setArguments([$locatorReferenceMap])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.webhook_handler', WebHookHandler::class)
            ->setArgument('$client', new Reference(Client::class))
            ->setArgument('$serviceLocator', new Reference('telegram_bot.controllers_locator'))
            ->setArgument('$controllersMap', $controllersMap)
        ;
    }

    /**
     * @param list<class-string> $commandServiceIds
     * @param \Closure(string):\ReflectionClass $reflectionClosure
     * @return \Generator<string, string>
     */
    private function controllerMap(array $commandServiceIds, \Closure $reflectionClosure): \Generator
    {
        foreach ($commandServiceIds as $class) {
            foreach ($reflectionClosure($class)->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach (self::FIND_ATTRIBUTES as $attribute) {
                    foreach ($reflectionMethod->getAttributes($attribute) as $controllerAttribute) {
                        $attributeInstance = $controllerAttribute->newInstance();
                        yield $attributeInstance->command ?? $attributeInstance::class => "$class::$method";
                    }
                }
            }
        }
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
