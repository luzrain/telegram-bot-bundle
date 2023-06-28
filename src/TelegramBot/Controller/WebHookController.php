<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot\Controller;

use Luzrain\TelegramBotApi\Exception\TelegramCallbackException;
use Luzrain\TelegramBotApi\Exception\TelegramTypeException;
use Luzrain\TelegramBotBundle\TelegramBot\WebHookHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

final class WebHookController extends AbstractController
{
    public function __construct(
        private WebHookHandler $webHookHandler,
        private string|null $secretToken,
    ) {
    }

    /**
     * @throws TelegramTypeException
     * @throws TelegramCallbackException
     * @throws \JsonException
     */
    public function __invoke(Request $request): Response
    {
        if ($request->getMethod() !== 'POST') {
            throw new MethodNotAllowedHttpException(['POST'], 'Method Not Allowed');
        }

        if ($this->secretToken !== null && $request->headers->get('X-Telegram-Bot-Api-Secret-Token') !== $this->secretToken) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $response = new JsonResponse(
            data: $this->webHookHandler->run($request->getContent()),
            json: true,
        );

        $response->headers->set('Content-Length', (string) strlen($response->getContent()));

        return $response;
    }
}
