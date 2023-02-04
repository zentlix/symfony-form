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

namespace Spiral\Symfony\Form\Twig\TokenParser;

use Spiral\Symfony\Form\Twig\Node\FormThemeNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @codeCoverageIgnore
 */
final class FormThemeTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $form = $this->parser->getExpressionParser()->parseExpression();
        $only = false;

        if ($this->parser->getStream()->test(Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();
            $resources = $this->parser->getExpressionParser()->parseExpression();

            if ($this->parser->getStream()->nextIf(Token::NAME_TYPE, 'only')) {
                $only = true;
            }
        } else {
            $resources = new ArrayExpression([], $stream->getCurrent()->getLine());
            do {
                $resources->addElement($this->parser->getExpressionParser()->parseExpression());
            } while (!$stream->test(Token::BLOCK_END_TYPE));
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new FormThemeNode($form, $resources, $lineno, $this->getTag(), $only);
    }

    public function getTag(): string
    {
        return 'form_theme';
    }
}
