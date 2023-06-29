<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Exception\TelegramCallbackException;
use Luzrain\TelegramBotApi\Exception\TelegramTypeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class WebHookHandler
{
    public function __construct(
        private ClientApi $client,
        private ServiceLocator $serviceLocator,
        array $controllersMap,
    ) {
        foreach ($controllersMap as ['event' => $event, 'value' => $value, 'controller' => $controller]) {
            $this->client->on($this->createClosure($event, $value, $controller));
        }
    }

    /**
     * @param string $body raw json request
     * @return string raw json response
     * @throws TelegramTypeException
     * @throws TelegramCallbackException
     * @throws \JsonException
     */
    public function run(string $body): string
    {
        return $this->client->webhookHandle($body);
    }

    /**
     * @param class-string<Event> $event
     */
    private function createClosure(string $event, string $value, string $controller): Event
    {
        return match ($event) {
            Event\Command::class => new $event($value, function (object $update, string ...$params) use ($controller) {
                return $this->runController($controller, $update, $params);
            }),
            Event\NamedCallbackQuery::class => new $event($value, function (object $update) use ($controller) {
                return $this->runController($controller, $update);
            }),
            default => new $event(function (object $update) use ($controller) {
                return $this->runController($controller, $update);
            }),
        };
    }

    private function runController(string $controller, object $update, array $params = []): mixed
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [$service, $method] = explode('::', $controller, 2);
        $controllerService = $this->serviceLocator->get($service);

        if ($controllerService instanceof TelegramCommand && isset($update->from)) {
            $controllerService->setUser($update->from);
        }

        return $controllerService->$method($update, ...$params);
    }
}
