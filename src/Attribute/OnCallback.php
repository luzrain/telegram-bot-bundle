<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\CallbackQuery;
use Luzrain\TelegramBotApi\Event\NamedCallbackQuery;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnCallback
{
    public string $event;
    public string $callbackData;
    public int $priority;

    public function __construct(string $callbackData = '', int $priority = 0)
    {
        $this->event = $callbackData === '' ? CallbackQuery::class : NamedCallbackQuery::class;
        $this->callbackData = $callbackData;
        $this->priority = $priority;
    }
}
