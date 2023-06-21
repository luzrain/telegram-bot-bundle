<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnCommand
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
