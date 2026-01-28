# Symfony Bundle for Telegram Bot API
![PHP >=8.2](https://img.shields.io/badge/PHP->=8.2-777bb3.svg?style=flat)
![Symfony ^7.0|^8.0](https://img.shields.io/badge/Symfony-^7.0|^8.0-374151.svg?style=flat)
![Tests Status](https://img.shields.io/github/actions/workflow/status/luzrain/telegram-bot-bundle/tests.yaml?branch=master)

A symfony bundle for [luzrain/telegram-bot-api](https://github.com/luzrain/telegram-bot-api) library.

## Getting Started
### Install Composer Packages
```bash
$ composer require luzrain/telegram-bot-bundle symfony/http-client nyholm/psr7
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

### Configure the Bundle
```yaml
# config/packages/telegram_bot.yaml

telegram_bot:
  api_token: API_TOKEN
#  webhook:
#    url: https://localhost/tg-webhook
```

Note that `symfony/http-client` and `nyholm/psr7` are not required. You may use any PSR-18 http client and PSR-17 factory implementations.
To use your own services, set the `http_client`, `request_factory`, and `stream_factory` options in the `telegram_bot.yaml` configuration file.  
Here is an example how to use [Guzzle](https://github.com/guzzle/guzzle) http client:

```yaml
# config/services.yaml

psr18.guzzle_client:
  class: GuzzleHttp\Client
  arguments:
    - http_errors: false

psr17.guzzle_factory:
  class: GuzzleHttp\Psr7\HttpFactory
```

```yaml
# config/packages/telegram_bot.yaml

telegram_bot:
  api_token: API_TOKEN
  http_client: psr18.guzzle_client
  request_factory: psr17.guzzle_factory
  stream_factory: psr17.guzzle_factory
```

For a full list of available configuration options and their documentation, run:
```bash
$ bin/console config:dump-reference telegram_bot
```

### Receiving Messages from Telegram
There are two ways to receive updates from Telegram:
#### 1. Webhook (recommended)
- Configure a webhook and make sure the endpoint is publicly accessible.
```yaml
# config/routes.yaml

# ...
telegram_webhook:
  path: /tg-webhook
  controller: telegram_bot.webhook_controller
```

- Set the `webhook.url` option in your `telegram_bot.yaml` configuration file.
- Apply the webhook settings to your bot:
```bash
$ bin/console telegram:webhook:update
```

Whenever you change the `webhook` or `allowed_updates` configuration options, run this command again to update the bot's webhook settings.

#### 2. Polling Daemon
Useful during development or when you cannot expose a public URL.  
Start the polling daemon:
```bash
$ bin/console telegram:polling:start
```

## Examples
### Command Controller
```php
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramCommand;

final class StartCommandController extends TelegramCommand
{
    // You can pass command arguments next to $message.
    // Be aware to set default values for arguments as they won't necessarily will be passed
    #[OnCommand('/start')]
    public function __invoke(Type\Message $message, string $arg1 = '', string $arg2 = ''): Method
    {
        return $this->reply('Hello from symfony bot');
    }
}
```

### Any Event Controller
```php
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;

// It's not necessary to extend TelegramCommand
final class OnMessageController
{
    // Listen any available event from Event namespace
    #[OnEvent(Event\Message::class)]
    public function __invoke(Type\Message $message): Method
    {
        return new Method\SendMessage(
            chatId: $message->chat->id,
            text: 'You wrote: ' . $message->text,
        );
    }
}
```

### Publishing Commands to the Bot Menu Button
You can publish your bot commands so they appear in the bot’s menu button.  
To do this, set both the `description` and `publish` arguments in the `OnCommand` attribute:
```php
#[OnCommand(command: '/command1', description: 'Test command 1', publish: true)]
```

Publish commands list to the menu button:
```bash
$ bin/console telegram:button:update
```

To remove menu button:
```bash
$ bin/console telegram:button:delete
```
