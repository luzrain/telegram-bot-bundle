<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Method\GetUpdates;
use Luzrain\TelegramBotApi\Type\Update;
use Psr\Http\Client\NetworkExceptionInterface;

final class LongPollingService
{
    private const LIMIT = 50;

    private int $timeout = 15;
    private int $offset = 0;

    public function __construct(
        private BotApi $botApi,
        /** @var list<string> */
        private array $allowedUpdates,
    ) {
    }

    public function setTimeout(int $timeout): void
    {
        if ($timeout <= 0) {
            throw new \InvalidArgumentException('timeout should be greater that 0');
        }

        $this->timeout = $timeout;
    }

    /**
     * @return \Generator<Update>
     */
    public function cunsumeUpdates(): \Generator
    {
        while (true) {
            foreach ($this->getUpdates() as $update) {
                yield $update;
            }
        }
    }

    private function getUpdates(): \Generator
    {
        try {
            $updates = $this->botApi->call(new GetUpdates(
                offset: $this->offset,
                limit: self::LIMIT,
                timeout: $this->timeout,
                allowedUpdates: $this->allowedUpdates,
            ));
        } catch (NetworkExceptionInterface) {
            return;
        }

        foreach ($updates as $update) {
            $this->offset = $update->updateId + 1;
            yield $update;
        }
    }
}
