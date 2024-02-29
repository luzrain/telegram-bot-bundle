<?php

declare(strict_types=1);

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\Attribute\OnCallback;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\Command\ButtonDeleteCommand;
use Luzrain\TelegramBotBundle\Command\ButtonUpdateCommand;
use Luzrain\TelegramBotBundle\Command\PolllingStartCommand;
use Luzrain\TelegramBotBundle\Command\WebhookDeleteCommand;
use Luzrain\TelegramBotBundle\Command\WebhookInfoCommand;
use Luzrain\TelegramBotBundle\Command\WebhookUpdateCommand;
use Luzrain\TelegramBotBundle\LongPollingService;
use Luzrain\TelegramBotBundle\WebHookController;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

return static function (array $config, ContainerBuilder $container) {
    $container
        ->register(BotApi::class)
        ->setArguments([
            new Reference($config['request_factory']),
            new Reference($config['stream_factory']),
            new Reference($config['http_client']),
            $config['api_token'],
        ])
    ;

    $container
        ->register('telegram_bot.client_api', ClientApi::class)
    ;

    $container
        ->register('telegram_bot.webhook_controller', WebHookController::class)
        ->addTag('controller.service_arguments')
        ->setArguments([
            new Reference('telegram_bot.update_handler'),
            $config['webhook']['secret_token'],
        ])
    ;

    $container
        ->register('telegram_bot.long_polling_service', LongPollingService::class)
        ->setArguments([
            new Reference(BotApi::class),
            $config['allowed_updates'],
        ])
    ;

    $container
        ->register('telegram_bot.webhook_update_command', WebhookUpdateCommand::class)
        ->addTag('console.command')
        ->setArguments([
            new Reference(BotApi::class),
            $config['allowed_updates'],
            $config['webhook']['url'],
            $config['webhook']['max_connections'],
            $config['webhook']['secret_token'],
            $config['webhook']['certificate'],
        ])
    ;

    $container
        ->register('telegram_bot.webhook_info_command', WebhookInfoCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    $container
        ->register('telegram_bot.webhook_delete_command', WebhookDeleteCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    $container
        ->register('telegram_bot.polling_start_command', PolllingStartCommand::class)
        ->addTag('console.command')
        ->setArguments([
            new Reference('telegram_bot.long_polling_service'),
            new Reference('telegram_bot.update_handler'),
            new Reference(BotApi::class),
        ])
    ;

    $container
        ->register('telegram_bot.button_update_commands', ButtonUpdateCommand::class)
        ->addTag('console.command')
        ->setArguments([
            new Reference(BotApi::class),
            new Reference('telegram_bot.command_metadata_provider'),
            new Reference('telegram_bot.description_processor', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ])
    ;

    $container
        ->register('telegram_bot.button_delete_command', ButtonDeleteCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    /** @var \Closure $controllerConfigurate */
    $controllerConfigurate = static function (ChildDefinition $definition, object $attribute, \ReflectionMethod $reflector) use ($container): void {
        $value = $attribute->command ?? $attribute->callbackData ?? '';

        $definition->addTag('telegram_bot.controller', [
            'event' => $attribute->event,
            'value' => $container->getParameterBag()->resolveValue($value),
            'controller' => $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName(),
            'priority' => $attribute->priority,
        ]);
    };

    $container->registerAttributeForAutoconfiguration(OnEvent::class, $controllerConfigurate);
    $container->registerAttributeForAutoconfiguration(OnCommand::class, $controllerConfigurate);
    $container->registerAttributeForAutoconfiguration(OnCallback::class, $controllerConfigurate);
};
