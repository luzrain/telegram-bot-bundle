<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\Test;

use Luzrain\TelegramBotBundle\TelegramBotBundle;
use Luzrain\TelegramBotBundle\Test\Controller\AnyUpdateController;
use Luzrain\TelegramBotBundle\Test\Controller\CallbackCommandController;
use Luzrain\TelegramBotBundle\Test\Controller\MessageController;
use Luzrain\TelegramBotBundle\Test\Controller\StartCommandController;
use Luzrain\TelegramBotBundle\Test\Controller\TestCommandController;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TelegramBotBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->register('httpClient', ClientInterface::class);
            $container->register('requestFactory', RequestFactoryInterface::class);
            $container->register('streamFactory', StreamFactoryInterface::class);

            $container->loadFromExtension('framework', [
                'test' => true,
            ]);

            $container->loadFromExtension('telegram_bot', [
                'http_client' => 'httpClient',
                'request_factory' => 'requestFactory',
                'stream_factory' => 'streamFactory',
                'api_token' => '9999999999:AAABBBCCCDDDEEEFFF',
            ]);

            $container->autowire(StartCommandController::class)->setAutoconfigured(true);
            $container->autowire(TestCommandController::class)->setAutoconfigured(true);
            $container->autowire(MessageController::class)->setAutoconfigured(true);
            $container->autowire(CallbackCommandController::class)->setAutoconfigured(true);
            $container->autowire(AnyUpdateController::class)->setAutoconfigured(true);
        });
    }
}
