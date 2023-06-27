<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Exception\TelegramCallbackException;
use Luzrain\TelegramBotApi\Exception\TelegramTypeException;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnMemberBanned;
use Luzrain\TelegramBotBundle\Attribute\OnMessage;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class WebHookHandler
{
    public function __construct(
        private ClientApi $client,
        private ServiceLocator $serviceLocator,
        private array $controllersMap,
    ) {
    }

    /**
     * @param string $body json request
     * @return string json response
     * @throws TelegramTypeException
     * @throws TelegramCallbackException
     * @throws \JsonException
     */
    public function run(string $body): string
    {
        foreach ($this->controllersMap as $command => $controller) {
            $this->client->on($this->createClosure($command, $controller));
        }

        return $this->client->webhookHandle($body);
    }

    private function createClosure(string $command, string $controller): Event
    {
        return match (str_starts_with($command, '/') ? OnCommand::class : $command) {
            OnCommand::class => new Event\Command($command, function (Type\Message $message, string ...$params) use ($controller) {
                return $this->runController($controller, $message->from, $params);
            }),
            OnMessage::class => new Event\Message(function (Type\Message $message) use ($controller) {
                return $this->runController($controller, $message->from, [$message->text]);
            }),
            OnMemberBanned::class => new Event\ChatMemberBanned(function (Type\User $userType) use ($controller) {
                return $this->runController($controller, $userType);
            }),
        };
    }

    private function runController(string $controller, Type\User $user, array $params = []): mixed
    {
        [$service, $method] = explode('::', $controller, 2);

        return $this->serviceLocator->get($service)->setUser($user)->$method(...$params);
    }
}
