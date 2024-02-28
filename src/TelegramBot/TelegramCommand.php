<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\EventCallbackReturn;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;

abstract class TelegramCommand
{
    private Type\User $user;

    public function setUser(Type\User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): Type\User
    {
        return $this->user;
    }

    protected function reply(
        string $text,
        string|null $parseMode = null,
        bool|null $disableNotification = null,
        bool|null $protectContent = null,
        Type\InlineKeyboardMarkup|Type\ReplyKeyboardMarkup|Type\ReplyKeyboardRemove|Type\ForceReply|null $replyMarkup = null,
    ): Method\SendMessage {
        return new Method\SendMessage(
            chatId: $this->getUser()->id,
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
