<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\CallbackQuery;
use Luzrain\TelegramBotApi\Event\NamedCallbackQuery;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnCallback
{
    public string $event;
    public string $value;

    public function __construct(string|null $callbackData = null)
    {
        $this->event = $callbackData === null ? CallbackQuery::class : NamedCallbackQuery::class;
        $this->value = $callbackData ?? '';
    }
}
