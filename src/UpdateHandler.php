<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Exception\TelegramCallbackException;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type\Update;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class UpdateHandler
{
    public function __construct(
        private ClientApi $client,
        private ServiceLocator $serviceLocator,
        array $controllersMap,
    ) {
        foreach ($controllersMap as ['event' => $eventClass, 'value' => $value, 'controller' => $controller]) {
            $this->client->on($this->createEvent($eventClass, $value, $controller));
        }
    }

    /**
     * @throws TelegramCallbackException
     */
    public function handle(Update $update): Method|null
    {
        return $this->client->handle($update);
    }

    /**
     * @param class-string<Event> $event
     */
    private function createEvent(string $event, string $value, string $controller): Event
    {
        /** @psalm-suppress UnsafeInstantiation */
        return match ($event) {
            Event\Command::class => new $event($value, function (object $update, string ...$params) use ($controller): mixed {
                return $this->runController($controller, $update, $params);
            }),
            Event\CallbackDataQuery::class => new $event($value, function (object $update) use ($controller): mixed {
                return $this->runController($controller, $update);
            }),
            default => new $event(function (object $update) use ($controller): mixed {
                return $this->runController($controller, $update);
            }),
        };
    }

    private function runController(string $controller, object $update, array $params = []): mixed
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [$service, $method] = \explode('::', $controller, 2);
        $controllerService = $this->serviceLocator->get($service);

        if ($controllerService instanceof TelegramCommand) {
            $controllerService->chatId = $this->findChatId($update);
        }

        return $controllerService->$method($update, ...$params);
    }

    private function findChatId(object $update): int|null
    {
        /** @psalm-suppress RawObjectIteration */
        if ($update instanceof Update) {
            foreach ($update as $updateItem) {
                if (null !== $chatId = $updateItem->chat->id ?? $updateItem->message->chat->id ?? null) {
                    return $chatId;
                }
            }
        }

        return $update->chat->id ?? $update->message->chat->id ?? null;
    }
}
