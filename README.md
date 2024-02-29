# Symfony bundle for Telegram Bot API
[![PHP >=8.2](https://img.shields.io/badge/PHP->=8.2-777bb3.svg?style=flat)](https://www.php.net/releases/8.2/en.php)
![Symfony ^7.0](https://img.shields.io/badge/Symfony-^7.0-374151.svg?style=flat)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/luzrain/telegram-bot-bundle/tests.yaml?branch=master)](../../actions/workflows/tests.yaml)

A symfony bundle for [luzrain/telegram-bot-api](https://github.com/luzrain/telegram-bot-api) library.

## Getting started
### Install composer packages
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

### Configure bundle
```yaml
# config/packages/telegram_bot.yaml

telegram_bot:
  api_token: API_TOKEN
#  webhook:
#    url: https://localhost/tg-webhook
```

### Optional. Configure webhook route
```yaml
# config/routes.yaml

# ...
telegram_webhook:
  path: /tg-webhook
  controller: telegram_bot.webhook_controller
```

Note that *symfony/http-client* and *nyholm/psr7* are not necessary. You can use any PSR-18 client and PSR-17 factories.  
Set custom services in *http_client*, *request_factory*, *stream_factory* options in *telegram_bot.yaml* configuration file.  
Here is an example how to use [guzzle](https://github.com/guzzle/guzzle) http client:

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
  http_client: psr18.guzzle_client
  request_factory: psr17.guzzle_factory
  stream_factory: psr17.guzzle_factory
  api_token: API_TOKEN
```

For a complete list of available options with documentation, see the command output.
```bash
$ bin/console config:dump-reference telegram_bot
```

### Getting messages from telegram
There are two ways to receive messages from Telegram.
#### Webhook. Recommended way.
You must configure the webhook route and make it available from the Internet.  
Configure *webhook.url* option in *telegram_bot.yaml* configuration file;  
Update the webhook configuration in telegram bot with the command.  
```bash
$ bin/console telegram:webhook:update
```

Note that each time you change *webhook* and *allowed_updates* options in configuration files you should run this command for update telegram bot settings.

#### Polling daemon.  
Use it in a development environment or if you can't provide public access to the webhook url.  
Run the polling daemon with the command.  
```bash
$ bin/console telegram:polling:start
```

## Examples
### Command controller
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

### Any event controller
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

### Publish command list as bot button
It's possible to publish all your commands, which will be shown as a list of available commands in the bot's menu button.
To do this, fill in the *description* field and the *publish* flag in the OnCommand attribute.
```php
#[OnCommand(command: '/command1', description: 'Test command 1', publish: true)]
```

Run the command for publish.
```bash
$ bin/console telegram:button:update
```

For button delete.
```bash
$ bin/console telegram:button:delete
```
