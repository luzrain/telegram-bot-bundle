<?php

declare(strict_types=1);

namespace Luzrain\TelegramBotBundle;

use Luzrain\TelegramBotBundle\Attribute\OnCommand;

final readonly class CommandMetadataProvider
{
    public function __construct(private array $controllers)
    {
    }

    /**
     * @return \Generator<OnCommand>
     */
    public function gelMetadataList(): \Generator
    {
        foreach ($this->controllers as $controller) {
            foreach ($this->instantiateAttributes($controller) as $attrubute) {
                yield $attrubute;
            }
        }
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
