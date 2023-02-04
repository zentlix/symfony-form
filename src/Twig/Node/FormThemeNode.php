<?php

/**
 * This code is extracted from package symfony/twig-bridge.
 *
 * @see       https://github.com/symfony/twig-bridge for the canonical source repository
 *
 * @copyright Copyright (c) 2004-present. (https://symfony.com)
 * @license   https://github.com/symfony/twig-bridge/blob/6.2/LICENSE
 */

declare(strict_types=1);

namespace Spiral\Symfony\Form\Twig\Node;

use Symfony\Component\Form\FormRenderer;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * @codeCoverageIgnore
 */
final class FormThemeNode extends Node
{
    public function __construct(Node $form, Node $resources, int $lineno, string $tag = null, bool $only = false)
    {
        parent::__construct(['form' => $form, 'resources' => $resources], ['only' => $only], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$this->env->getRuntime(')
            ->string(FormRenderer::class)
            ->raw(')->setTheme(')
            ->subcompile($this->getNode('form'))
            ->raw(', ')
            ->subcompile($this->getNode('resources'))
            ->raw(', ')
            ->raw(false === $this->getAttribute('only') ? 'true' : 'false')
            ->raw(");\n");
    }
}
