
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
  api_token: 'paste_your_telegram_bot_token'

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

