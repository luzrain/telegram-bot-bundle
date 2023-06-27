<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramBotApiException;
use Luzrain\TelegramBotApi\Method\SetWebhook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SetWebhookCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:set-webhook-url';
    }

    public static function getDefaultDescription(): string
    {
        return 'Set Telegram bot webhook url';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $webhookUrl = $io->ask('Provide a webhook1 url', '', $this->urlValidate(...));

        try {
            $this->botApi->call(new SetWebhook(
                url: $webhookUrl,
            ));
        } catch (TelegramBotApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Webhook url set to: ' . $webhookUrl);

        return Command::SUCCESS;
    }

    private function urlValidate(string $url): string
    {
        if ($url === '') {
            throw new \RuntimeException('Url should not be blank');
        }

        if ((parse_url($url, PHP_URL_SCHEME) ?? 'https') !== 'https') {
            throw new \RuntimeException('Url should starts with https://');
        }

        if (!str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid url');
        }

        return $url;
    }
}
