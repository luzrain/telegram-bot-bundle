<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\ClientApi;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonDeleteCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\ButtonSetCommandsCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\DeleteWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\PolllingStartCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\SetWebhookCommand;
use Luzrain\TelegramBotBundle\TelegramBot\Command\WebhookInfoCommand;
use Luzrain\TelegramBotBundle\TelegramBot\CommandMetadataProvider;
use Luzrain\TelegramBotBundle\TelegramBot\Controller\WebHookController;
use Luzrain\TelegramBotBundle\TelegramBot\LongPollingService;
use Luzrain\TelegramBotBundle\TelegramBot\UpdateHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CommandMetadataProviderTest extends KernelTestCase
{
    public function testCommandsMetadata(): void
    {
        /** @var CommandMetadataProvider $commandMetadataProvider */
        $commandMetadataProvider = self::getContainer()->get('telegram_bot.command_metadata_provider');
        $list = iterator_to_array($commandMetadataProvider->gelMetadataList());

        $this->assertCount(4, $list);
        $this->assertObjectInArray(new OnCommand('/start', '', false, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test1', 'test1 command description', true, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test2', 'test2 command description', true, 0), $list);
        $this->assertObjectInArray(new OnCommand('/test3', 'test3 command description', false, 0), $list);
    }

    private function assertObjectInArray(object $obj, array $array): void
    {
        $this->assertContains(json_encode($obj), array_map(fn ($a) => json_encode($a), $array));
    }
}
