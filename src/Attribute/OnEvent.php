<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnEvent
{
    public string $event;
    public string $value;

    public function __construct(string $event)
    {
        if (!is_subclass_of($event, Event::class)) {
            throw new \InvalidArgumentException(sprintf('event should implement %s', Event::class));
        }

        $this->event = $event;
        $this->value = '';
    }
}
