<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Processor;

use Spiral\Core\FactoryInterface;
use Spiral\Symfony\Form\Config\FormsConfig;
use Spiral\Symfony\Form\FormTypeRegistryInterface;
use Spiral\Symfony\Form\WireTrait;
use Symfony\Component\Form\FormTypeInterface;

final class ConfigProcessor implements ProcessorInterface
{
    use WireTrait;

    public function __construct(
        private readonly FormsConfig $config,
        private readonly FactoryInterface $factory,
        private readonly FormTypeRegistryInterface $registry
    ) {
    }

    public function process(): void
    {
        foreach ($this->config->getFormTypes() as $formType) {
            $formType = $this->wire($formType, $this->factory);

            \assert($formType instanceof FormTypeInterface);
            $this->registry->add($formType);
        }
    }
}
