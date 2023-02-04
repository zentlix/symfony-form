<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Processor;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Symfony\Form\Attribute\FormType;
use Spiral\Symfony\Form\FormTypeRegistryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;
use Symfony\Component\Form\FormTypeInterface;

final class AttributeProcessor implements TokenizationListenerInterface, ProcessorInterface
{
    /** @var \ReflectionClass[] */
    private array $formTypes = [];
    private bool $collected = false;

    public function __construct(
        TokenizerListenerRegistryInterface $listenerRegistry,
        private readonly ReaderInterface $reader,
        private readonly FactoryInterface $factory,
        private readonly FormTypeRegistryInterface $registry
    ) {
        $listenerRegistry->addListener($this);
    }

    public function process(): void
    {
        if (!$this->collected) {
            throw new \RuntimeException(sprintf('Tokenizer did not finalize %s listener.', self::class));
        }

        foreach ($this->formTypes as $ref) {
            $formType = $this->factory->make($ref->getName());

            \assert($formType instanceof FormTypeInterface);
            $this->registry->add($formType);
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        $attr = $this->reader->firstClassMetadata($class, FormType::class);

        if ($attr instanceof FormType) {
            $this->formTypes[] = $class;
        }
    }

    public function finalize(): void
    {
        $this->collected = true;
    }
}
