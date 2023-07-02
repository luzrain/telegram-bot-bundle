<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test\Helper;

final class ControllerTestHelper
{
    public static bool $isStartCommand = false;
    public static bool $isTest1CommandCommand = false;
    public static bool $isTest2CommandCommand = false;
    public static bool $isTest3CommandCommand = false;
    public static bool $isMessage = false;
    public static bool $isCallback1 = false;
    public static bool $isUpdate = false;

    public function __construct()
    {
        self::$isStartCommand = false;
        self::$isTest1CommandCommand = false;
        self::$isTest2CommandCommand = false;
        self::$isTest3CommandCommand = false;
        self::$isMessage = false;
        self::$isCallback1 = false;
        self::$isUpdate = false;
    }
}
