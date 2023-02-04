<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form;

use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

final class FormTypeRegistry implements FormTypeRegistryInterface
{
    public function __construct(
        private readonly FormFactoryBuilderInterface $builder
    ) {
    }

    public function add(FormTypeInterface $formType): void
    {
        $this->builder->addType($formType);
    }
}
