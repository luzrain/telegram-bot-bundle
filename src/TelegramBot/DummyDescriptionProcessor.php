<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

final class DummyDescriptionProcessor implements CommandDescriptionProcessor
{
    public function process(string $description): string
    {
        return $description;
    }
}
