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
     * @return \Generator<OnCommand>
     */
    public function gelMetadataList(): \Generator
    {
        foreach ($this->controllersMap as ['controller' => $controller]) {
            $command = $this->instantiateAttribute($controller);
            if ($command !== null) {
                yield $command;
            }
        }
    }

    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function instantiateAttribute(string $controller): OnCommand|null
    {
        [$class, $method] = \explode('::', $controller, 2);
        $reflClass = new \ReflectionClass($class);
        $reflMethod = $reflClass->getMethod($method);
        $reflAttribute = $reflMethod->getAttributes(OnCommand::class)[0] ?? null;

        return $reflAttribute?->newInstance();
    }
}
