<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\Attribute\OnMemberBanned;
use Luzrain\TelegramBotBundle\Attribute\OnMessage;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TelegramBot\Api\BaseMethod;
use TelegramBot\Api\Client;
use TelegramBot\Api\Events\Event as Event;
use TelegramBot\Api\Types as Type;

final class WebHookHandler
{
    public function __construct(
        private Client $client,
        private ServiceLocator $serviceLocator,
        private array $controllersMap,
    ) {
    }

    public function run(string $body)
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
                return $this->runController($controller, $message->getFrom(), $params);
            }),
            OnMessage::class => new Event\Message(function (Type\Message $message) use ($controller) {
                return $this->runController($controller, $message->getFrom(), [$message->getText()]);
            }),
            OnMemberBanned::class => new MemberBanned(function (Type\User $userType) use ($controller) {
                return $this->runController($controller, $userType);
            }),
        };
    }

    private function runController(string $controller, Type\User $user, array $params = []): BaseMethod|null
    {
        [$service, $method] = explode('::', $controller, 2);

        return $this->serviceLocator->get($service)->setUser($user)->$method(...$params);
    }
}
