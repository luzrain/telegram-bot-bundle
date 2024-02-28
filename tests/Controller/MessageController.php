<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Controller;

use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramCommand;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;

final class MessageController extends TelegramCommand
{
    #[OnEvent(event: Event\Message::class)]
    public function __invoke(Type\Message $message): Method
    {
        ControllerTestHelper::$isMessage = true;
        return $this->reply('You wrote: ' . $message->text);
    }
}
