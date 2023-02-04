<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Symfony\Form\Config\FormsConfig;
use Spiral\Symfony\Form\Twig\Extension\FormExtension;
use Spiral\Twig\Bootloader\TwigBootloader as TwigBridgeBootloader;
use Spiral\Twig\TwigEngine;
use Spiral\Views\Bootloader\ViewsBootloader;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

final class TwigBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TwigBridgeBootloader::class,
    ];

    public function init(ViewsBootloader $views, DirectoriesInterface $dirs): void
    {
        $views->addDirectory(
            'forms',
            rtrim($dirs->get('vendor'), '/').'/zentlix/symfony-form/views/twig'
        );
    }

    public function boot(AbstractKernel $kernel, TwigBridgeBootloader $twig): void
    {
        $twig->addExtension(new FormExtension());

        $kernel->booted(function (ViewsInterface $views, FormsConfig $config) {
            $this->registerTwigRuntimeLoader($views, $config);
        });
    }

    private function registerTwigRuntimeLoader(ViewsInterface $views, FormsConfig $config): void
    {
        if (!$views instanceof ViewManager) {
            return;
        }

        foreach ($views->getEngines() as $engine) {
            if ($engine instanceof TwigEngine) {
                $twig = $engine->getEnvironment($views->getContext());
                $formEngine = new TwigRendererEngine([$config->getTheme()], $twig);
                $twig->addRuntimeLoader(new FactoryRuntimeLoader([
                    FormRenderer::class => static fn (): FormRenderer => new FormRenderer($formEngine),
                ]));
            }
        }
    }
}
