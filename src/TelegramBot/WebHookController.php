<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

final class WebHookController extends AbstractController
{
    public function __construct(private WebHookHandler $webHookHandler)
    {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->getMethod() !== 'POST') {
            throw new MethodNotAllowedHttpException(['POST'], 'Method Not Allowed');
        }

        $responseMethod = $this->webHookHandler->run($request->getContent());
        $response = $this->json($responseMethod);
        $response->headers->set('Content-Length', (string) strlen($response->getContent()));

        return $response;
    }
}
