<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Luzrain\TelegramBotBundle\DependencyInjection\CommandCompilerPass;

final class TelegramBotBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CommandCompilerPass());
    }
}
