<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle\TelegramBot;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;

final class CommandMetadataProvider
{
    private array $controllerClassMap = [];

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
            foreach ($this->instantiateAttributes($controller) as $attrubute) {
                yield $attrubute;
            }
        }

        $this->controllerClassMap[] = [];
    }

    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function instantiateAttributes(string $controller): \Generator
    {
        [$class, $method] = \explode('::', $controller, 2);

        if (isset($this->controllerClassMap[$class])) {
            return;
        }

        $this->controllerClassMap[$class] = true;
        $reflClass = new \ReflectionClass($class);
        $reflMethod = $reflClass->getMethod($method);
        $attributes = $reflMethod->getAttributes(OnCommand::class);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
