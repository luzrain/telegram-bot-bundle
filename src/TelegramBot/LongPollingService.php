<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Method\GetUpdates;
use Luzrain\TelegramBotApi\Type\Update;
use Psr\Http\Client\NetworkExceptionInterface;

final class LongPollingService
{
    private const UPDATES_LIMIT = 50;

    private int $timeout = 15;
    private int $timeLimit = 0;
    private int $offset = 0;

    public function __construct(
        private BotApi $botApi,
        private array $allowedUpdates,
    ) {
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setTimeLimit(int $timeLimit): void
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * @return \Generator<Update>
     */
    public function cunsumeUpdates(): \Generator
    {
        $start = \hrtime()[0];
        $checkTimeLimit = fn() => $this->timeLimit > 0 && \hrtime()[0] - $start >= $this->timeLimit;

        while (true) {
            foreach ($this->getUpdates() as $update) {
                yield $update;

                if ($checkTimeLimit()) {
                    return;
                }
            }

            if ($checkTimeLimit()) {
                return;
            }
        }
    }

    private function getUpdates(): \Generator
    {
        try {
            $updates = $this->botApi->call(new GetUpdates(
                offset: $this->offset,
                limit: self::UPDATES_LIMIT,
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
