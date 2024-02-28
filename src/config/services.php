<?php

declare(strict_types=1);

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\Attribute\OnCallback;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonDeleteCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonUpdateCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\PolllingStartCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookDeleteCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookInfoCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookUpdateCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Controller\WebHookController;
use Luzrain\TelegramBotBundle\TelegramBot\LongPollingService;
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
        ->register('telegram_bot.set_webhook_command', WebhookUpdateCommand::class)
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
        ->register('telegram_bot.get_webhook_command', WebhookInfoCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    $container
        ->register('telegram_bot.delete_webhook_command', WebhookDeleteCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    $container
        ->register('telegram_bot.polling_command', PolllingStartCommand::class)
        ->addTag('console.command')
        ->setArguments([
            new Reference('telegram_bot.long_polling_service'),
            new Reference('telegram_bot.update_handler'),
            new Reference(BotApi::class),
        ])

    ;

    $container
        ->register('telegram_bot.menu_button_set_commands', ButtonUpdateCommand::class)
        ->addTag('console.command')
        ->setArguments([
            new Reference(BotApi::class),
            new Reference('telegram_bot.command_metadata_provider'),
            new Reference('telegram_bot.description_processor', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ])
    ;

    $container
        ->register('telegram_bot.menu_button_delete_command', ButtonDeleteCommand::class)
        ->addTag('console.command')
        ->setArguments([new Reference(BotApi::class)])
    ;

    /** @var \Closure $controllerConfigurate */
    $controllerConfigurate = static function (ChildDefinition $definition, object $attribute, \ReflectionMethod $reflector): void {
        $definition->addTag('telegram_bot.command', [
            'event' => $attribute->event,
            'value' => $attribute->command ?? $attribute->callbackData ?? '',
            'controller' => $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName(),
            'priority' => $attribute->priority,
        ]);
    };

    $container->registerAttributeForAutoconfiguration(OnEvent::class, $controllerConfigurate);
    $container->registerAttributeForAutoconfiguration(OnCommand::class, $controllerConfigurate);
    $container->registerAttributeForAutoconfiguration(OnCallback::class, $controllerConfigurate);
};
