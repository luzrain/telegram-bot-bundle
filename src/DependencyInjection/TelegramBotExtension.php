<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\Attribute\OnCallback;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramBot\Command\DeleteWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookInfoCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\SetWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\WebHookController;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class TelegramBotExtension extends Extension
{
    private const FIND_ATTRIBUTES = [
        OnEvent::class,
        OnCommand::class,
        OnCallback::class,
    ];

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->autowire(BotApi::class)
            ->setArgument('$requestFactory', new Reference($config['request_factory']))
            ->setArgument('$streamFactory', new Reference($config['stream_factory']))
            ->setArgument('$client', new Reference($config['http_client']))
            ->setArgument('$token', $config['api_token'])
        ;

        $container
            ->autowire('telegram_bot.client_api', ClientApi::class)
        ;

        $container
            ->register('telegram_bot.webhook_controller', WebHookController::class)
            ->setArgument('$webHookHandler', new Reference('telegram_bot.webhook_handler'))
            ->addTag('controller.service_arguments')
            ->addMethodCall('setContainer', [new Reference('service_container')])
        ;

        $container
            ->register('telegram_bot.set_webhook_command', SetWebhookCommand::class)
            ->setArgument('$botApi', new Reference(BotApi::class))
            ->addTag('console.command')
        ;

        $container
            ->register('telegram_bot.get_webhook_command', WebhookInfoCommand::class)
            ->setArgument('$botApi', new Reference(BotApi::class))
            ->addTag('console.command')
        ;

        $container
            ->register('telegram_bot.delete_webhook_command', DeleteWebhookCommand::class)
            ->setArgument('$botApi', new Reference(BotApi::class))
            ->addTag('console.command')
        ;

        foreach (self::FIND_ATTRIBUTES as $attributeClass) {
            $container->registerAttributeForAutoconfiguration($attributeClass, $this->controllerConfigurate(...));
        }
    }

    private function controllerConfigurate(ChildDefinition $definition, object $attribute, \ReflectionMethod $reflector): void
    {
        $definition->addTag('telegram_bot.command', [
            'event' => $attribute->event,
            'value' => $attribute->value,
            'controller' => $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName(),
        ]);
    }

    public function getAlias(): string
    {
        return 'telegram_bot';
    }
}
