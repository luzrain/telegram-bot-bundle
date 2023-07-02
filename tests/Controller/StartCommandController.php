<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Controller;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;

final class StartCommandController extends TelegramCommand
{
    #[OnCommand('/start')]
    public function __invoke(): Method
    {
        ControllerTestHelper::$isStartCommand = true;
        return $this->reply('Start answer');
    }
}
