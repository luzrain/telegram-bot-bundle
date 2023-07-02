<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Controller;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;

final class TestCommandController extends TelegramCommand
{
    #[OnCommand(command: '/test1', description: 'test1 command description', publish: true)]
    public function test1(): Method
    {
        ControllerTestHelper::$isTest1CommandCommand = true;
        return $this->reply('Test1 answer');
    }

    #[OnCommand(command: '/test2', description: 'test2 command description', publish: true)]
    public function test2(): Method
    {
        ControllerTestHelper::$isTest2CommandCommand = true;
        return $this->reply('Test2 answer');
    }

    #[OnCommand(command: '/test3', description: 'test3 command description', publish: false)]
    public function test3(): Method
    {
        ControllerTestHelper::$isTest3CommandCommand = true;
        return $this->reply('Test3 answer');
    }
}
