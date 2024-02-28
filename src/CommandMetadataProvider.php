<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

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
            if (isset($this->controllerClassMap[$controller])) {
                return;
            }
            $this->controllerClassMap[$controller] = true;
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
        $reflClass = new \ReflectionClass($class);
        $reflMethod = $reflClass->getMethod($method);
        $attributes = $reflMethod->getAttributes(OnCommand::class);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
