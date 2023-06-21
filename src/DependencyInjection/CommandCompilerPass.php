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
    public function process(ContainerBuilder $container)
    {
        $commandTaggedServices = $container->findTaggedServiceIds('telegram_bot.command');

        $commandMap = $this->attributeProcess($container, $commandTaggedServices, OnCommand::class);
        $memberBannedMap = $this->attributeProcess($container, $commandTaggedServices, OnMemberBanned::class);
        $messageMap = $this->attributeProcess($container, $commandTaggedServices, OnMessage::class);

        $container
            ->register('telegram_bot.command_controllers_locator', ServiceLocator::class)
            ->setArguments([$commandMap[0]])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.member_banned_controllers_locator', ServiceLocator::class)
            ->setArguments([$memberBannedMap[0]])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.message_controllers_locator', ServiceLocator::class)
            ->setArguments([$messageMap[0]])
            ->addTag('container.service_locator')
        ;

        $container
            ->register('telegram_bot.webhook_handler', WebHookHandler::class)
            ->setArgument('$client', new Reference(Client::class))
            ->setArgument('$commandMap', $commandMap[1])
            ->setArgument('$commandLocator', new Reference('telegram_bot.command_controllers_locator'))
            ->setArgument('$memberBannedMap', $memberBannedMap[1])
            ->setArgument('$memberBannedLocatorMap', new Reference('telegram_bot.member_banned_controllers_locator'))
            ->setArgument('$messageMap', $messageMap[1])
            ->setArgument('$messageLocatorMap', new Reference('telegram_bot.message_controllers_locator'))
        ;
    }

    private function attributeProcess(ContainerBuilder $container, array $taggedServices, string $attributeClass): array
    {
        $serviceDefinitions = array_map(fn (string $id) => $container->getDefinition($id), array_keys($taggedServices));
        $locatorClassMap = [];
        $commandCallbackMap = [];

        foreach ($serviceDefinitions as $serviceDefinition) {
            $class = $serviceDefinition->getClass();
            $reflectionClass = $container->getReflectionClass($class);

            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                $attributes = $reflectionMethod->getAttributes($attributeClass);
                $methodName = $reflectionMethod->getName();

                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();
                    $locatorClassMap[$class] = new Reference($class);
                    $commandCallbackMap[$attributeInstance->name ?? $attributeInstance::class] = [$class, $methodName];
                }
            }
        }

        return [$locatorClassMap, $commandCallbackMap];
    }
}
