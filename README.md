
# Symfony bundle for Telegram Bot API

A symfony bundle for `luzrain/telegram-bot-api`

## Installation
### Install composer package
``` bash
$ composer require luzrain/telegram-bot-bundle (TODO)
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
``` bash
$ bin/console telegram:set-webhook-url
```

..... TODO

