<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test;

use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type\Update;
use Luzrain\TelegramBotBundle\TelegramBot\UpdateHandler;
use Luzrain\TelegramBotBundle\Test\Helper\ControllerTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UpdateHandlerTest extends KernelTestCase
{
    private UpdateHandler $updateHandler;

    public function setUp(): void
    {
        $this->updateHandler = self::getContainer()->get('telegram_bot.update_handler');
    }

    public function testMessageHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/message.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertInstanceOf(Method\SendMessage::class, $callbackResponse);
        $this->assertSame('{"method":"sendMessage","chat_id":123456789,"text":"You wrote: test test"}', json_encode($callbackResponse));
        $this->assertFalse($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertTrue($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertFalse($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }

    public function testStartCommandHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/command1.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertInstanceOf(Method\SendMessage::class, $callbackResponse);
        $this->assertSame('{"method":"sendMessage","chat_id":123456789,"text":"Start answer"}', json_encode($callbackResponse));
        $this->assertTrue($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertFalse($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertFalse($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }

    public function testTest2CommandHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/command2.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertInstanceOf(Method\SendMessage::class, $callbackResponse);
        $this->assertSame('{"method":"sendMessage","chat_id":123456789,"text":"Test2 answer"}', json_encode($callbackResponse));
        $this->assertFalse($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertTrue($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertFalse($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertFalse($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }

    public function testCallbackHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/callbackQuery.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertInstanceOf(Method\SendMessage::class, $callbackResponse);
        $this->assertSame('{"method":"sendMessage","chat_id":123456789,"text":"Callback1 answer"}', json_encode($callbackResponse));
        $this->assertFalse($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertFalse($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertTrue($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }

    public function testUnregisteredCallbackHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/unknownCallbackQuery.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertNull($callbackResponse);
        $this->assertFalse($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertFalse($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertFalse($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }

    public function testUnregisteredEventHandle(): void
    {
        $controllerTestHelper = new ControllerTestHelper();
        $update = Update::fromJson(file_get_contents(__DIR__ . '/data/events/myChatMember.json'));
        $callbackResponse = $this->updateHandler->handle($update);

        $this->assertNull($callbackResponse);
        $this->assertFalse($controllerTestHelper::$isStartCommand, '$isStartCommand');
        $this->assertFalse($controllerTestHelper::$isTest1CommandCommand, '$isTest1CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest2CommandCommand, '$isTest2CommandCommand');
        $this->assertFalse($controllerTestHelper::$isTest3CommandCommand, '$isTest3CommandCommand');
        $this->assertFalse($controllerTestHelper::$isMessage, '$isMessage');
        $this->assertFalse($controllerTestHelper::$isCallback1, '$isCallback1');
        $this->assertTrue($controllerTestHelper::$isUpdate, '$isUpdate');
    }
}
