<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TwigOptimizer\Node\Expression;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;

class ArrayAccessExpression extends AbstractExpression
{
    public function __construct(AbstractExpression $node, Node $name, $lineno)
    {
        parent::__construct(['node' => $node, 'name' => $name], ['safe' => false], $lineno);

        if ($node instanceof NameExpression) {
            $node->setAttribute('always_defined', true);
        }
    }

    /**
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        $compiler
            ->subcompile($this->getNode('node'))
            ->raw('[')
            ->subcompile($this->getNode('name'))
            ->raw(']');
    }
}
