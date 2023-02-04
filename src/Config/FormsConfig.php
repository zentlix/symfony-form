<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Symfony\Form\Processor\ProcessorInterface;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @psalm-type TExtension = FormExtensionInterface|class-string<FormExtensionInterface>|Autowire<FormExtensionInterface>
 * @psalm-type TFormType = FormTypeInterface|class-string<FormTypeInterface>|Autowire<FormTypeInterface>
 * @psalm-type TProcessor = ProcessorInterface|class-string<ProcessorInterface>|Autowire<ProcessorInterface>
 *
 * @property array{
 *     theme: non-empty-string,
 *     form_types: TFormType[],
 *     extensions: TExtension[],
 *     processors: TProcessor[]
 * } $config
 */
final class FormsConfig extends InjectableConfig
{
    public const CONFIG = 'forms';

    protected array $config = [
        'theme' => 'forms:bootstrap_5_layout.twig',
        'form_types' => [],
        'extensions' => [],
        'processors' => [],
    ];

    /**
     * @return non-empty-string
     */
    public function getTheme(): string
    {
        return $this->config['theme'];
    }

    /**
     * @psalm-return TFormType[]
     */
    public function getFormTypes(): array
    {
        return $this->config['form_types'];
    }

    /**
     * @psalm-return TExtension[]
     */
    public function getExtensions(): array
    {
        return $this->config['extensions'];
    }

    /**
     * @psalm-return TProcessor[]
     */
    public function getProcessors(): array
    {
        return $this->config['processors'];
    }
}
