<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;

abstract class TelegramCommand
{
    protected readonly Type\User $user;

    public function setUser(Type\User $user): void
    {
        /** @psalm-suppress InaccessibleProperty */
        $this->user = $user;
    }

    protected function reply(
        string $text,
        string|null $parseMode = null,
        bool|null $disableWebPagePreview = null,
        bool|null $disableNotification = null,
        bool|null $protectContent = null,
        Type\InlineKeyboardMarkup|Type\ReplyKeyboardMarkup|Type\ReplyKeyboardRemove|Type\ForceReply|null $replyMarkup = null,
    ): Method\SendMessage {
        return new Method\SendMessage(
            chatId: $this->user->id,
            text: $text,
            parseMode: $parseMode,
            disableWebPagePreview: $disableWebPagePreview,
            disableNotification: $disableNotification,
            protectContent: $protectContent,
            replyMarkup: $replyMarkup,
        );
    }
}
