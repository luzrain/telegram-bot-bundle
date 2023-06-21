<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use TelegramBot\Api\BaseMethod;
use TelegramBot\Api\Events\Event;
use TelegramBot\Api\Types\ChatMemberBanned;
use TelegramBot\Api\Types\Update;

class BotBlockEvent extends Event
{
    public function executeChecker(Update $update): bool
    {
        return $update->getMyChatMember()?->getNewChatMember() instanceof ChatMemberBanned;
    }

    public function executeAction(Update $update): BaseMethod|null
    {
        return $this->callback($update->getMyChatMember()->getFrom());
    }
}
