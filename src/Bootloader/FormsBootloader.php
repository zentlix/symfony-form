<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Symfony\Form\Config\FormsConfig;
use Spiral\Symfony\Form\Extension\DefaultExtensionsRegistry;
use Spiral\Symfony\Form\Extension\DefaultExtensionsRegistryInterface;
use Spiral\Symfony\Form\Extension\ExtensionFactory;
use Spiral\Symfony\Form\FormTypeProcessorRegistry;
use Spiral\Symfony\Form\FormTypeRegistry;
use Spiral\Symfony\Form\FormTypeRegistryInterface;
use Spiral\Symfony\Form\HttpFoundation\SpiralRequestHandler;
use Spiral\Symfony\Form\Processor\AttributeProcessor;
use Spiral\Symfony\Form\Processor\ConfigProcessor;
use Spiral\Symfony\Form\Processor\ProcessorInterface;
use Spiral\Symfony\Form\WireTrait;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\RequestHandlerInterface;

final class FormsBootloader extends Bootloader
{
    use WireTrait;

    protected const DEPENDENCIES = [
        TokenizerListenerBootloader::class,
    ];

    protected const SINGLETONS = [
        FormFactoryInterface::class => [FormFactoryBuilderInterface::class, 'getFormFactory'],
        FormFactoryBuilderInterface::class => [self::class, 'initFormFactoryBuilder'],
        DefaultExtensionsRegistryInterface::class => DefaultExtensionsRegistry::class,
        FormTypeRegistryInterface::class => FormTypeRegistry::class,
    ];

    protected const BINDINGS = [
        RequestHandlerInterface::class => SpiralRequestHandler::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->initConfig();
    }

    public function boot(FormsConfig $config, FactoryInterface $factory, AbstractKernel $kernel, FormTypeProcessorRegistry $registry): void
    {
        $this->registerFormTypeProcessors($config, $factory, $kernel, $registry);
    }

    private function initConfig(): void
    {
        $this->config->setDefaults(
            FormsConfig::CONFIG,
            [
                'theme' => 'forms:bootstrap_5_layout.twig',
                'form_types' => [],
                'extensions' => [],
                'processors' => [
                    AttributeProcessor::class,
                    ConfigProcessor::class,
                ],
            ]
        );
    }

    private function initFormFactoryBuilder(
        FormsConfig $config,
        ExtensionFactory $factory,
        DefaultExtensionsRegistryInterface $registry
    ): FormFactoryBuilder {
        $builder = new FormFactoryBuilder();

        foreach ($this->getExtensions($config, $factory, $registry) as $extension) {
            $builder->addExtension($extension);
        }

        return $builder;
    }

    /**
     * @return FormExtensionInterface[]
     */
    private function getExtensions(
        FormsConfig $config,
        ExtensionFactory $factory,
        DefaultExtensionsRegistryInterface $registry
    ): array {
        $extensions = [];
        foreach ($config->getExtensions() as $extension) {
            $extensions[] = $factory->create($extension);
        }

        if ([] !== $extensions) {
            return $extensions;
        }

        return $registry->getExtensions();
    }

    private function registerFormTypeProcessors(FormsConfig $config, FactoryInterface $factory, AbstractKernel $kernel, FormTypeProcessorRegistry $registry): void
    {
        foreach ($config->getProcessors() as $processor) {
            $processor = $this->wire($processor, $factory);

            \assert($processor instanceof ProcessorInterface);
            $registry->addProcessor($processor);
        }

        $kernel->bootstrapped(static function (FormTypeProcessorRegistry $registry): void {
            $registry->process();
        });
    }
}
