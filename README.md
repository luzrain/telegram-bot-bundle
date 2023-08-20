# Symfony bundle for Telegram Bot API
[![PHP ^8.2](https://img.shields.io/badge/PHP-^8.2-777bb3.svg?style=flat)](https://www.php.net/releases/8.2/en.php)
![Symfony ^6.3](https://img.shields.io/badge/Symfony-^6.3-374151.svg?style=flat)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/luzrain/telegram-bot-bundle/tests.yaml?branch=master)](../../actions/workflows/tests.yaml)

A symfony bundle for [luzrain/telegram-bot-api](https://github.com/luzrain/telegram-bot-api) library.

## Installation
### Install composer package
```bash
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
  http_client: GuzzleHttp\ClientInterface      # Psr\Http\Client\ClientInterface implementation
  request_factory: GuzzleHttp\Psr7\HttpFactory # Psr\Http\Message\RequestFactoryInterface implementation
  stream_factory: GuzzleHttp\Psr7\HttpFactory  # Psr\Http\Message\StreamFactoryInterface implementation
  api_token: API_TOKEN                         # Bot api token
  #secret_token: CHANGE_ME                     # Optional. Secret token to protect webhook endpoint from unauthenticated requests (update webhook url after change)
  #allowed_updates: ['message']                # Optional. List of the update types you want your bot to receive (update webhook url after change)
```

### Optional. Configure webhook route
```yaml
# config/routes.yaml

# ...
telegram_webhook:
  path: /telagram-webhook
  controller: telegram_bot.webhook_controller
```

### Getting messages from telegram
There are two ways to receive messages from Telegram.
#### Webhook. Recommended way.
For this you need to configure webhook route and make it available from the Internet.  
Send webhook url to Telegram with the command:  
``` bash
$ bin/console telegram:webhook:set --url=https://domain.xyz/telagram-webhook
```

#### Polling daemon.  
Use it in a development environment or if you can't provide public access to the webhook url.  
Run the polling daemon with the command:  
``` bash
$ bin/console telegram:polling:start
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
    // Be aware to set default values for command arguments as they won't necessarily will be passed
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

// It's not necessary to extend TelegramCommand at all
final class OnMessageController
{
    #[OnEvent(Event\Message::class)]
    public function __invoke(Type\Message $message): Method
    {
        return new Method\SendMessage(
            chatId: $message->from->id,
            text: 'You wrote: ' . $message->text,
        );
    }
}
```

### Access control controller
```php
use Luzrain\TelegramBotApi\Event;
use Luzrain\TelegramBotApi\EventCallbackReturn;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnEvent;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;

final class AccessControlController extends TelegramCommand
{
    // Set the highest priority to ensure that this method is executed before any others.
    #[OnEvent(Event\Update::class, priority: 10)]
    public function __invoke(Type\Update $update): EventCallbackReturn
    {
        // Stop executing other controllers if the sender doesn't meet some conditions
        if ($update->message?->from->id !== 123456789) {
            return $this->stop();
        }

        return $this->continue();
    }
}
```

### Publish list of command as bot button
It's possible to publish all your commands that will be shown as list of available commands in the bot's menu button.
To do this fill in the _description_ field and the _publish_ flag in the OnCommand attribute, and run the command.
``` bash
$ bin/console telegram:button:setcommands
```

For button delete.
``` bash
$ bin/console telegram:button:delete
```

```php
use Luzrain\TelegramBotApi\Method;
use Luzrain\TelegramBotApi\Type;
use Luzrain\TelegramBotBundle\Attribute\OnCommand;
use Luzrain\TelegramBotBundle\TelegramBot\TelegramCommand;

final class CommandsController extends TelegramCommand
{
    #[OnCommand(command: '/command1', description: 'Test command 1', publish: true)]
    public function __invoke(Type\Message $message): Method
    {
        return $this->reply('Command 1 response');
    }

    #[OnCommand(command: '/command2', description: 'Test command 2', publish: true)]
    public function __invoke(Type\Message $message): Method
    {
        return $this->reply('Command 2 response');
    }

    #[OnCommand(command: '/command3', description: 'This command will not be published', publish: false)]
    public function __invoke(Type\Message $message): Method
    {
        return $this->reply('Command 3 response');
    }
}
```