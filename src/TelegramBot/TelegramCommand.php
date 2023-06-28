<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\Method\SendMessage;
use Luzrain\TelegramBotApi\Type\ForceReply;
use Luzrain\TelegramBotApi\Type\InlineKeyboardMarkup;
use Luzrain\TelegramBotApi\Type\ReplyKeyboardMarkup;
use Luzrain\TelegramBotApi\Type\ReplyKeyboardRemove;
use Luzrain\TelegramBotApi\Type\User;

abstract class TelegramCommand
{
    readonly protected User|null $user;

    public function setUser(User|null $user): self
    {
        $this->user = $user;

        return $this;
    }

    protected function reply(
        string $text,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
    ): SendMessage {
        return new SendMessage(
            chatId: $this->user->id,
            text: $text,
            replyMarkup: $replyMarkup,
        );
    }
}
