<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Symfony\Component\DependencyInjection\ServiceLocator;
use TelegramBot\Api\BaseMethod;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\User;

final class WebHookHandler
{
    public function __construct(
        private Client $client,
        private array $commandMap,
        private ServiceLocator $commandLocator,
        private array $memberBannedMap,
        private ServiceLocator $memberBannedLocatorMap,
        private array $messageMap,
        private ServiceLocator $messageLocatorMap,
    ) {
    }

    public function run(string $body)
    {
        $this->incomeCommandInit();
        $this->incomeMemberBannedInit();
        $this->incomeMessageInit();

        return $this->client->webhookHandle($body);
    }

    private function incomeCommandInit(): void
    {
        foreach ($this->commandMap as $commandName => [$serviceId, $methodName]) {
            $this->client->onCommand($commandName, function (Message $message, string ...$params) use ($serviceId, $methodName) {
                return $this->runController(
                    locator: $this->commandLocator,
                    service: $serviceId,
                    method: $methodName,
                    user: $message->getFrom(),
                    params: $params,
                );
            });
        }
    }

    private function incomeMemberBannedInit(): void
    {
        foreach ($this->memberBannedMap as [$serviceId, $methodName]) {
            $this->client->on(new MemberBanned(function (User $userType) use ($serviceId, $methodName) {
                return $this->runController(
                    locator: $this->memberBannedLocatorMap,
                    service: $serviceId,
                    method: $methodName,
                    user: $userType,
                );
            }));
        }
    }

    private function incomeMessageInit(): void
    {
        foreach ($this->messageMap as [$serviceId, $methodName]) {
            $this->client->onMessage(function (Message $message) use ($serviceId, $methodName) {
                return $this->runController(
                    locator: $this->messageLocatorMap,
                    service: $serviceId,
                    method: $methodName,
                    user: $message->getFrom(),
                    params: [$message->getText()],
                );
            });
        }
    }

    private function runController(ServiceLocator $locator, string $service, string $method, User $user, array $params = []): BaseMethod|null
    {
        return $locator
            ->get($service)
            ->setUser($user)
            ->$method(...$params)
        ;
    }
}
