<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class OnCommand
{
    public function __construct(public string $command)
    {
        if (!str_starts_with($this->command, '/')) {
            throw new \InvalidArgumentException(sprintf('Invalid command "%s". Did you mean "/%s"?', $this->command, $this->command));
        }
    }
}
