<?php

declare(strict_types=1);

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\Attribute\OnCallback;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonDeleteCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonSetCommandsCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\DeleteWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\PolllingStartCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\SetWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookInfoCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Controller\WebHookController;
use Luzrain\TelegramBotBundle\TelegramBot\DummyDescriptionProcessor;
use Luzrain\TelegramBotBundle\TelegramBot\LongPollingService;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (array $config, ContainerBuilder $container) {
    $container
        ->autowire(BotApi::class)
        ->setArgument('$requestFactory', new Reference($config['request_factory']))
        ->setArgument('$streamFactory', new Reference($config['stream_factory']))
        ->setArgument('$client', new Reference($config['http_client']))
        ->setArgument('$token', $config['api_token'])
    ;

    $container
        ->register('telegram_bot.client_api', ClientApi::class)
    ;

    $container
        ->register('telegram_bot.webhook_controller', WebHookController::class)
        ->setArgument('$updateHandler', new Reference('telegram_bot.update_handler'))
        ->setArgument('$secretToken', $config['secret_token'])
        ->addTag('controller.service_arguments')
    ;

    $container
        ->register('telegram_bot.long_polling_service', LongPollingService::class)
        ->setArgument('$botApi', new Reference(BotApi::class))
        ->setArgument('$allowedUpdates', $config['allowed_updates'])
    ;

    $container
        ->register('telegram_bot.set_webhook_command', SetWebhookCommand::class)
        ->setArgument('$botApi', new Reference(BotApi::class))
        ->setArgument('$secretToken', $config['secret_token'])
        ->setArgument('$allowedUpdates', $config['allowed_updates'])
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

    $container
        ->register('telegram_bot.polling_command', PolllingStartCommand::class)
        ->setArgument('$longPollingService', new Reference('telegram_bot.long_polling_service'))
        ->setArgument('$updateHandler', new Reference('telegram_bot.update_handler'))
        ->setArgument('$botApi', new Reference(BotApi::class))
        ->addTag('console.command')
    ;

    $container
        ->register('telegram_bot.menu_button_set_commands', ButtonSetCommandsCommand::class)
        ->setArgument('$botApi', new Reference(BotApi::class))
        ->setArgument('$commandMetadataProvider', new Reference('telegram_bot.command_metadata_provider'))
        ->setArgument('$descriptionProcessor', new Reference('telegram_bot.description_processor'))
        ->addTag('console.command')
    ;

    $container
        ->register('telegram_bot.menu_button_delete_command', ButtonDeleteCommand::class)
        ->setArgument('$botApi', new Reference(BotApi::class))
        ->addTag('console.command')
    ;

    $container
        ->register('telegram_bot.description_processor', DummyDescriptionProcessor::class)
    ;

    $controllerConfigurate = static function(ChildDefinition $definition, object $attribute, \ReflectionMethod $reflector): void {
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
