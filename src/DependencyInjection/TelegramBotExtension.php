<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\DependencyInjection;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\TelegramBot\SetWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\TelegramBot\WebHookController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class TelegramBotExtension extends Extension
{
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
