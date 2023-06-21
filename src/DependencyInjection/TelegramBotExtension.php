<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotBundle\TelegramBot\SetWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\TelegramBot\WebHookController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

final class TelegramBotExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->autowire(BotApi::class)
            ->setArguments([$config['api_token']])
        ;

        $container
            ->autowire(Client::class)
        ;

        $container
            ->registerForAutoconfiguration(TelegramCommand::class)
            ->addTag('telegram_bot.command')
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
    }

    public function getAlias(): string
    {
        return 'telegram_bot';
    }
}
