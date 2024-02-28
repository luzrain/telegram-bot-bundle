<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Luzrain\TelegramBotApi\Exception\TelegramTypeException;
use Luzrain\TelegramBotApi\Type\Update;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

final readonly class WebHookController
{
    public function __construct(
        private UpdateHandler $updateHandler,
        private string|null $secretToken,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->getMethod() !== 'POST') {
            throw new MethodNotAllowedHttpException(['POST'], 'Method Not Allowed');
        }

        if ($this->secretToken !== null && $request->headers->get('X-Telegram-Bot-Api-Secret-Token') !== $this->secretToken) {
            throw new AccessDeniedHttpException('Access denied');
        }

        try {
            $update = Update::fromJson($request->getContent());
        } catch (TelegramTypeException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $response = new JsonResponse($this->updateHandler->handle($update));
        $response->headers->set('Content-Length', (string) \strlen((string) $response->getContent()));

        return $response;
    }
}
