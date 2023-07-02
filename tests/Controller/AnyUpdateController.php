<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Controller;

use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;

final class AnyUpdateController extends TelegramCommand
{
    #[OnEvent(event: Event\Update::class, priority: 10)]
    public function __invoke(): void
    {
        ControllerTestHelper::$isUpdate = true;
    }
}
