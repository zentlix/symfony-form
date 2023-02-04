<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Symfony\Form\Processor\ProcessorInterface;

final class FormTypeProcessorRegistry implements ProcessorInterface, SingletonInterface
{
    /** @var ProcessorInterface[] */
    private array $processors = [];
    private bool $processed = false;

    public function addProcessor(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    public function process(): void
    {
        if ($this->processed) {
            return;
        }

        foreach ($this->processors as $processor) {
            $processor->process();
        }

        $this->processed = true;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
