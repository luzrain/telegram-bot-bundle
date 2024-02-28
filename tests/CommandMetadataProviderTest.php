<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\CommandMetadataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CommandMetadataProviderTest extends KernelTestCase
{
    public function testCommandsMetadata(): void
    {
        /** @var CommandMetadataProvider $commandMetadataProvider */
        $commandMetadataProvider = self::getContainer()->get('telegram_bot.command_metadata_provider');
        $list = \iterator_to_array($commandMetadataProvider->gelMetadataList());

        $this->assertCount(5, $list);
        $this->assertObjectInArray(new OnCommand('/start', '', false, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test1', 'test1 command description', true, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test2', 'test2 command description', true, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test3', 'test3 command description', false, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test4', 'test4 command description', false, 0), $list);
    }

    private function assertObjectInArray(object $obj, array $array): void
    {
        $this->assertContains(\json_encode($obj), \array_map(fn($a) => \json_encode($a), $array));
    }
}
