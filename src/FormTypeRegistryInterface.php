<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form;

use Symfony\Component\Form\FormTypeInterface;

interface FormTypeRegistryInterface
{
    public function add(FormTypeInterface $formType): void;
}
