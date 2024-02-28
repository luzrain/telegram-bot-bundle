<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class WebhookDeleteCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:webhook:delete';
    }

    public static function getDefaultDescription(): string
    {
        return 'Remove webhook integration';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->botApi->call(new Method\DeleteWebhook(dropPendingUpdates: true));
        } catch (TelegramApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Webhook integration removed');

        return Command::SUCCESS;
    }
}
