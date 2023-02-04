<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Extension;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Symfony\Form\Config\FormsConfig;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Validator\Validation;

/**
 * @psalm-import-type TExtension from FormsConfig
 */
final class DefaultExtensionsRegistry implements DefaultExtensionsRegistryInterface
{
    private array $extensions = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ExtensionFactory $factory
    ) {
        $this->initDefaultExtensions();
    }

    /**
     * @psalm-param TExtension $extension
     */
    public function add(FormExtensionInterface|Autowire|string $extension): void
    {
        $this->extensions[] = $this->factory->create($extension);
    }

    /**
     * @return FormExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    private function initDefaultExtensions(): void
    {
        $this->extensions[] = new CoreExtension();
        $this->extensions[] = new HttpFoundationExtension();
        $this->addValidatorExtension();
    }

    private function addValidatorExtension(): void
    {
        if (!class_exists(Validation::class)) {
            return;
        }

        $builder = Validation::createValidatorBuilder()->enableAnnotationMapping();

        if ($this->container->has(TranslatorInterface::class)) {
            $builder->setTranslator($this->container->get(TranslatorInterface::class));
        }

        $this->extensions[] = new ValidatorExtension(
            validator: $builder->getValidator(),
            translator: $this->container->has(TranslatorInterface::class)
                ? $this->container->get(TranslatorInterface::class)
                : null
        );
    }
}
