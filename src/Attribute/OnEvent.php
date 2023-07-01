<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnEvent
{
    public string $event;

    public function __construct(string $event)
    {
        $this->event = $event;

        if (!is_subclass_of($event, Event::class)) {
            throw new \InvalidArgumentException(sprintf('Event should implement %s', Event::class));
        }
    }
}
