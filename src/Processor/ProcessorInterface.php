<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Processor;

interface ProcessorInterface
{
    public function process(): void;
}
