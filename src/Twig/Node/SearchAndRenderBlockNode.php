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
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;

/**
 * @codeCoverageIgnore
 */
final class SearchAndRenderBlockNode extends FunctionExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
        $compiler->raw('$this->env->getRuntime(\'Symfony\Component\Form\FormRenderer\')->searchAndRenderBlock(');

        preg_match('/_([^_]+)$/', $this->getAttribute('name'), $matches);

        $arguments = iterator_to_array($this->getNode('arguments'));
        $blockNameSuffix = $matches[1];

        if (isset($arguments[0])) {
            $compiler->subcompile($arguments[0]);
            $compiler->raw(', \''.$blockNameSuffix.'\'');

            if (isset($arguments[1])) {
                if ('label' === $blockNameSuffix) {
                    // The "label" function expects the label in the second and
                    // the variables in the third argument
                    $label = $arguments[1];
                    $variables = $arguments[2] ?? null;
                    $lineno = $label->getTemplateLine();

                    if ($label instanceof ConstantExpression) {
                        // If the label argument is given as a constant, we can either
                        // strip it away if it is empty, or integrate it into the array
                        // of variables at compile time.
                        $labelIsExpression = false;

                        // Only insert the label into the array if it is not empty
                        if (!twig_test_empty($label->getAttribute('value'))) {
                            $originalVariables = $variables;
                            $variables = new ArrayExpression([], $lineno);
                            $labelKey = new ConstantExpression('label', $lineno);

                            if (null !== $originalVariables) {
                                foreach ($originalVariables->getKeyValuePairs() as $pair) {
                                    // Don't copy the original label attribute over if it exists
                                    if ((string) $labelKey !== (string) $pair['key']) {
                                        $variables->addElement($pair['value'], $pair['key']);
                                    }
                                }
                            }

                            // Insert the label argument into the array
                            $variables->addElement($label, $labelKey);
                        }
                    } else {
                        // The label argument is not a constant, but some kind of
                        // expression. This expression needs to be evaluated at runtime.
                        // Depending on the result (whether it is null or not), the
                        // label in the arguments should take precedence over the label
                        // in the attributes or not.
                        $labelIsExpression = true;
                    }
                } else {
                    // All other functions than "label" expect the variables
                    // in the second argument
                    $label = null;
                    $variables = $arguments[1];
                    $labelIsExpression = false;
                }

                if (null !== $variables || $labelIsExpression) {
                    $compiler->raw(', ');

                    if (null !== $variables) {
                        $compiler->subcompile($variables);
                    }

                    if ($labelIsExpression) {
                        if (null !== $variables) {
                            $compiler->raw(' + ');
                        }

                        // Check at runtime whether the label is empty.
                        // If not, add it to the array at runtime.
                        $compiler->raw('(twig_test_empty($_label_ = ');
                        if (null !== $label) {
                            $compiler->subcompile($label);
                        }
                        $compiler->raw(') ? [] : ["label" => $_label_])');
                    }
                }
            }
        }

        $compiler->raw(')');
    }
}
