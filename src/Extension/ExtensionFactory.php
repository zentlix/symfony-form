<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Extension;

use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Symfony\Form\Config\FormsConfig;
use Symfony\Component\Form\FormExtensionInterface;

/**
 * @psalm-import-type TExtension from FormsConfig
 */
final class ExtensionFactory
{
    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    /**
     * @psalm-param TExtension $extension
     */
    public function create(FormExtensionInterface|Autowire|string $extension): FormExtensionInterface
    {
        $ext = match (true) {
            \is_string($extension) => $this->factory->make($extension),
            $extension instanceof Autowire => $extension->resolve($this->factory),
            default => $extension
        };

        \assert($ext instanceof FormExtensionInterface);

        return $ext;
    }
}
