<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\CallbackDataQuery;
use Luzrain\TelegramBotApi\Event\CallbackQuery;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class OnCallback
{
    public string $event;
    public string $callbackData;
    public int $priority;

    public function __construct(string $callbackData = '', int $priority = 0)
    {
        $this->event = $callbackData === '' ? CallbackQuery::class : CallbackDataQuery::class;
        $this->callbackData = $callbackData;
        $this->priority = $priority;
    }
}
