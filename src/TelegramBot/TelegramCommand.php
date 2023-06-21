<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use TelegramBot\Api\Methods\SendMessage;
use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\User;

abstract class TelegramCommand
{
    private User $user;

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    protected function reply(
        string $text,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
    ): SendMessage {
        return new SendMessage(
            chatId: $this->getUser()->getId(),
            text: $text,
            replyMarkup: $replyMarkup,
        );
    }
}
