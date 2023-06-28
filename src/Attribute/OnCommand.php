<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\Command;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnCommand
{
    public string $event;
    public string $value;

    public function __construct(string $command)
    {
        $this->event = Command::class;
        $this->value = '/' . ltrim($command, '/');
    }
}
