<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Attribute;

use Luzrain\TelegramBotApi\Event\Command;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class OnCommand
{
    public string $event;
    public string $command;
    public string $description;
    public bool $publish;

    public function __construct(string $command, string $description = '', bool $publish = false)
    {
        $this->event = Command::class;
        $this->command = '/' . ltrim($command, '/');
        $this->description = $description;
        $this->publish = $publish;

        if ($this->command === '/') {
            throw new \InvalidArgumentException('Command can\'t be empty');
        }

        if ($this->publish === true && $this->description === '') {
            throw new \InvalidArgumentException(sprintf('Description should be set for publish command "%s"', $this->command));
        }
    }
}
