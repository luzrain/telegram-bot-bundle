<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\Command;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class OnCommand
{
    public string $event;
    public string $command;
    public string $description;
    public bool $publish;
    public int $priority;

    public function __construct(string $command, string $description = '', bool $publish = false, int $priority = 0)
    {
        $this->event = Command::class;
        $this->command = '/' . \ltrim($command, '/');
        $this->description = $description;
        $this->publish = $publish;
        $this->priority = $priority;

        if ($this->command === '/') {
            throw new \InvalidArgumentException('Command can\'t be empty');
        }

        if ($this->publish === true && $this->description === '') {
            throw new \InvalidArgumentException(\sprintf('Description should be set for publish command "%s"', $this->command));
        }
    }
}
