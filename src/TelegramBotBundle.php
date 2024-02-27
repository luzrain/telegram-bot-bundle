<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class TelegramBotBundle extends AbstractBundle
{
    protected string $extensionAlias = 'telegram_bot';

    public function configure(DefinitionConfigurator $definition): void
    {
        $configurator = require __DIR__ . '/config/configuration.php';
        $configurator($definition);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(require __DIR__ . '/config/compilerpass.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configurator = require __DIR__ . '/config/services.php';
        $configurator($config, $builder);
    }
}
