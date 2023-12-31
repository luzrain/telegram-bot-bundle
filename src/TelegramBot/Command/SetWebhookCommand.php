<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method\SetWebhook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SetWebhookCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
        private string|null $secretToken,
        /** @var list<string> */
        private array $allowedUpdates,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:webhook:set';
    }

    public static function getDefaultDescription(): string
    {
        return 'Set webhook url';
    }

    protected function configure(): void
    {
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Webhook url');
        $this->addOption('max-connections', null, InputOption::VALUE_REQUIRED, 'Max connections', 40);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('url') === null) {
            $io->error('You should provide "--url" option');

            return Command::FAILURE;
        }

        try {
            $url = $this->urlValidate($input->getOption('url'));
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        try {
            $this->botApi->call(new SetWebhook(
                url: $url,
                maxConnections: (int) $input->getOption('max-connections'),
                allowedUpdates: $this->allowedUpdates,
                secretToken: $this->secretToken,
            ));
        } catch (TelegramApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Webhook url set to "%s"', $url));

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
