<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;

final class CommandMetadataProvider
{
    public function __construct(
        private array $controllersMap,
    ) {
    }

    /**
     * @return list<OnCommand>
     */
    public function gelMetadataList(): array
    {
        $list = [];
        foreach ($this->controllersMap as ['controller' => $controller]) {
            $command = $this->instantiateAttribute($controller);
            if ($command !== null) {
                $list[] = $command;
            }
        }

        return $list;
    }

    private function instantiateAttribute(string $controller): OnCommand|null
    {
        [$class, $method] = explode('::', $controller, 2);
        $reflClass = new \ReflectionClass($class);
        $reflMethod = $reflClass->getMethod($method);
        $reflAttribute = $reflMethod->getAttributes(OnCommand::class)[0] ?? null;

        return $reflAttribute?->newInstance();
    }
}
