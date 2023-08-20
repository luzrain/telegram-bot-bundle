<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Luzrain\TelegramBotBundle\DependencyInjection\CommandCompilerPass;
use Luzrain\TelegramBotBundle\DependencyInjection\TelegramBotExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class TelegramBotBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CommandCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new TelegramBotExtension();
    }
}
