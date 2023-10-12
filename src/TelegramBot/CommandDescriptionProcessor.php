<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

interface CommandDescriptionProcessor
{
    public function process(string $description): string;
}
