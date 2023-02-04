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

use Twig\Compiler;
use Twig\Node\Expression\FunctionExpression;

/**
 * @codeCoverageIgnore
 */
final class RenderBlockNode extends FunctionExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
        $arguments = iterator_to_array($this->getNode('arguments'));
        $compiler->write('$this->env->getRuntime(\'Symfony\Component\Form\FormRenderer\')->renderBlock(');

        if (isset($arguments[0])) {
            $compiler->subcompile($arguments[0]);
            $compiler->raw(', \''.$this->getAttribute('name').'\'');

            if (isset($arguments[1])) {
                $compiler->raw(', ');
                $compiler->subcompile($arguments[1]);
            }
        }

        $compiler->raw(')');
    }
}
