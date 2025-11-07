<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\CommandMetadataProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'telegram:button:update',
    description: 'Set published list of commands as as default bot\'s menu button',
)]
final class ButtonUpdateCommand extends Command
{
    private \Closure $descriptionProcessor;

    public function __construct(
        private BotApi $botApi,
        private CommandMetadataProvider $commandMetadataProvider,
        callable|null $descriptionProcessor,
    ) {
        $this->descriptionProcessor = $descriptionProcessor !== null ? $descriptionProcessor(...) : static fn(string $str): string => $str;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln('<info>This command list will be set as the default bot button:</info>');

        $commands = [];
        foreach ($this->commandMetadataProvider->gelMetadataList() as $attr) {
            if ($attr->publish === false) {
                continue;
            }

            $description = ($this->descriptionProcessor)($attr->description);
            $output->writeln(\sprintf("%s\t\t%s", $attr->command, $description));
            $commands[] = new Type\BotCommand(command: $attr->command, description: $description);
        }

        if ($commands === []) {
            $io->warning('Could not find any commands to publish');

            return Command::SUCCESS;
        }

        if (!$io->confirm('Continue?')) {
            return Command::SUCCESS;
        }

        try {
            $this->botApi->call(new Method\SetChatMenuButton(menuButton: new Type\MenuButtonCommands()));
            $this->botApi->call(new Method\SetMyCommands(commands: $commands));
        } catch (TelegramApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Bot\'s menu button has been set');

        return Command::SUCCESS;
    }
}
