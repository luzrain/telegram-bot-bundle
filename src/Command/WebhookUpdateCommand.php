<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method\SetWebhook;
use Luzrain\TelegramBotApi\Type\InputFile;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'telegram:webhook:update',
    description: 'Update webhook settings',
)]
final class WebhookUpdateCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
        /** @var list<string> */
        private array $allowedUpdates,
        private string|null $webhookUrl,
        private int|null $maxConnections,
        #[\SensitiveParameter]
        private string|null $secretToken,
        private string|null $certificate,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Webhook url');
        $this->addOption('max-connections', null, InputOption::VALUE_REQUIRED, 'Max connections');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $webhookUrl = $input->getOption('url') ?? $this->webhookUrl;
        $maxConnections = (int) ($input->getOption('max-connections') ?? $this->maxConnections ?? 40);

        if ($webhookUrl === null) {
            $io->error('webhook_url config option is not set up. Provide "--url" option');

            return Command::FAILURE;
        }

        try {
            $url = $this->urlValidate($webhookUrl);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        try {
            $this->botApi->call(new SetWebhook(
                url: $url,
                certificate: $this->certificate === null ? null : new InputFile($this->certificate),
                maxConnections: $maxConnections,
                allowedUpdates: $this->allowedUpdates,
                secretToken: $this->secretToken,
            ));
        } catch (TelegramApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('Webhook url set to "%s"', $url));

        return Command::SUCCESS;
    }

    private function urlValidate(string $url): string
    {
        if ($url === '') {
            throw new \RuntimeException('Url should not be blank');
        }

        if ((\parse_url($url, PHP_URL_SCHEME) ?? 'https') !== 'https') {
            throw new \RuntimeException('Url should starts with https://');
        }

        if (!\str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        if (!\filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid url');
        }

        return $url;
    }
}
