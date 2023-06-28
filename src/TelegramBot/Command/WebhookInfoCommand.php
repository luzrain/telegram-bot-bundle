<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class WebhookInfoCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:webhook:info';
    }

    public static function getDefaultDescription(): string
    {
        return 'Get current webhook status';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $webhookInfo = $this->botApi->call(new Method\GetWebhookInfo());
        } catch (TelegramApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($webhookInfo->url === '') {
            $io->warning('Webhook is not installed');

            return Command::SUCCESS;
        }

        $io->writeln(sprintf("<comment>Webhook url:</comment>\t\t%s", $webhookInfo->url));
        $io->writeln(sprintf("<comment>Max Connections:</comment>\t%s", $webhookInfo->maxConnections));

        return Command::SUCCESS;
    }
}
