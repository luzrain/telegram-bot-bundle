<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnEvent
{
    public function __construct(public string $event, public string $value = '')
    {
    }
}
