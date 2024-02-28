<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class OnEvent
{
    public string $event;
    public int $priority;

    public function __construct(string $event, int $priority = 0)
    {
        $this->event = $event;
        $this->priority = $priority;

        if (!is_subclass_of($event, Event::class)) {
            throw new \InvalidArgumentException(sprintf('Event should implement %s', Event::class));
        }
    }
}
