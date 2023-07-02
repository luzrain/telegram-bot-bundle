<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Controller;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotBundle\Attribute\OnCallback;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;

final class CallbackCommandController extends TelegramCommand
{
    #[OnCallback('test_callback_1')]
    public function callback1(): Method
    {
        ControllerTestHelper::$isCallback1 = true;
        return $this->reply('Callback1 answer');
    }
}
