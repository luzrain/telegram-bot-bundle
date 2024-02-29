<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Luzrain\TelegramBotApi\EventCallbackReturn;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;

abstract class TelegramCommand
{
    public int|null $chatId = null;

    protected function reply(
        string $text,
        string|null $parseMode = null,
        bool|null $disableNotification = null,
        bool|null $protectContent = null,
        Type\InlineKeyboardMarkup|Type\ReplyKeyboardMarkup|Type\ReplyKeyboardRemove|Type\ForceReply|null $replyMarkup = null,
    ): Method\SendMessage|EventCallbackReturn {
        if ($this->chatId === null) {
            return $this->stop();
        }

        return new Method\SendMessage(
            chatId: $this->chatId,
            text: $text,
            parseMode: $parseMode,
            disableNotification: $disableNotification,
            protectContent: $protectContent,
            replyMarkup: $replyMarkup,
        );
    }

    protected function stop(): EventCallbackReturn
    {
        return EventCallbackReturn::STOP;
    }

    protected function continue(): EventCallbackReturn
    {
        return EventCallbackReturn::CONTINUE;
    }
}
