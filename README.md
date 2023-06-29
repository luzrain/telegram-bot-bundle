# Symfony bundle for Telegram Bot API

[![PHP ^8.2](https://img.shields.io/badge/PHP-^8.2-777bb3.svg?style=flat)](https://www.php.net/releases/8.2/en.php)
![Symfony ^6.3](https://img.shields.io/badge/Symfony-^6.3-374151.svg?style=flat)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/luzrain/telegram-bot-api/tests.yaml?branch=master)](../../actions/workflows/tests.yaml)

A symfony bundle for [luzrain/telegram-bot-api](https://github.com/luzrain/telegram-bot-api) library.

## Installation
### Install composer package
``` bash
$ composer require luzrain/telegram-bot-bundle
```

### Enable the Bundle
```php
<?php
// config/bundles.php

return [
    // ...
    Luzrain\TelegramBotBundle\TelegramBotBundle::class => ['all' => true],
];
```

### Configure bundle
```yaml
# config/packages/telegram_bot.yaml

telegram_bot:
  # Psr\Http\Client\ClientInterface implementation
  http_client: GuzzleHttp\ClientInterface
  # Psr\Http\Message\RequestFactoryInterface implementation
  request_factory: httpFactory
  # Psr\Http\Message\StreamFactoryInterface implementation
  stream_factory: httpFactory
  # Bot api token
  api_token: API_TOKEN
  # Optional. Secret token to protect webhook endpoint from unauthenticated requests
  secret_token: CHANGE_ME
```

### Configure webhook route
```yaml
# config/routes.yaml

# ...
telegram_webhook:
    path: /telagram-webhook
    controller: telegram_bot.webhook_controller
```

### Set webhook url
Install webhook url using the console command:
``` bash
$ bin/console telegram:webhook:set --url=https://domain.xyz/telagram-webhook
```

## Example of usage
### Command controller
```php
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;

final class StartCommandController extends TelegramCommand
{
    // Be aware to set default values for arguments as they won't necessarily will be passed
    #[OnCommand('/start')]
    public function __invoke(Type\Message $message, string $arg1 = '', string $arg2 = ''): Method
    {
        return $this->reply('Hello from symfony bot');
    }
}
```

### Message controller
```php
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;

// It's not necessary to extend TelegramCommand
final class OnMessageController
{
    #[OnEvent(Event\Message::class)]
    public function __invoke(Type\Message $message): Method
    {
        return new Method\SendMessage(
            chatId: $message->from->id,
            text: 'You write: ' . $message->text,
        );
    }
}
```