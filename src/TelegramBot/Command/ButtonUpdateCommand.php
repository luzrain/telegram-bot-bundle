<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot\Command;

use Luzrain\TelegramBotApi\BotApi;
use Luzrain\TelegramBotApi\Exception\TelegramApiException;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\TelegramBot\CommandDescriptionProcessor;
use Luzrain\TelegramBotBundle\TelegramBot\CommandMetadataProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ButtonUpdateCommand extends Command
{
    public function __construct(
        private BotApi $botApi,
        private CommandMetadataProvider $commandMetadataProvider,
        private CommandDescriptionProcessor $descriptionProcessor,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'telegram:button:update';
    }

    public static function getDefaultDescription(): string
    {
        return 'Set published list of commands as as default bot\'s mebu button';
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

            $description = $this->descriptionProcessor->process($attr->description);
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
