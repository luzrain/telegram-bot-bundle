<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotBundle\TelegramBot\LongPollingService;
use Luzrain\TelegramBotBundle\TelegramBot\UpdateHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PolllingStartCommand extends Command
{
    public function __construct(
        private LongPollingService $longPollingService,
        private UpdateHandler $updateHandler,
        private BotApi $botApi,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:polling:start';
    }

    public static function getDefaultDescription(): string
    {
        return 'Run polling client to receive updates';
    }

    protected function configure(): void
    {
        $this->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Timeout for long polling connection in seconds', 15);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $timeout = (int) $input->getOption('timeout');
        $output->writeln('<info>Polling service started</info>');

        try {
            $this->longPollingService->setTimeout($timeout);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        try {
            foreach ($this->longPollingService->cunsumeUpdates() as $update) {
                $date = new \DateTimeImmutable();
                $formattedDate = $date->format('Y-m-d H:i:s');
                $output->writeln(sprintf(
                    '%s: [%s] Update object received',
                    $formattedDate,
                    $update->updateId,
                ));

                $callbackResponse = $this->updateHandler->handle($update);

                try {
                    $this->botApi->call($callbackResponse);
                } catch (TelegramApiException $e) {
                    $output->writeln(sprintf(
                        '%s: [%s] <error>TelegramApiException (%s) %s</error>',
                        $formattedDate,
                        $update->updateId,
                        $e->getCode(),
                        $e->getMessage(),
                    ));
                }
            }
        } catch (TelegramApiException $e) {
            if ($e->getCode() === 409) {
                $io->error('Can\'t use polling if webhook is active. Run "telegram:webhook:delete" command to delete the webhook first.');
                return Command::FAILURE;
            }

            throw $e;
        }

        return Command::SUCCESS;
    }
}
