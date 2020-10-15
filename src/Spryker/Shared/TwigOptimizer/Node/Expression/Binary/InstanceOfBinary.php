<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TwigOptimizer\Node\Expression\Binary;

use Twig\Compiler;
use Twig\Node\Expression\Binary\AbstractBinary;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;

class InstanceOfBinary extends AbstractBinary
{
    public function __construct(Node $left, $right, $lineno)
    {
        if (is_string($right)) {
            $right = new ConstantExpression($right, $lineno);
        }
        parent::__construct($left, $right, $lineno);
    }

    /**
     * @param \Twig\Compiler $compiler
     *
     * @return void
     */
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ');
        $this->operator($compiler);
        $compiler
            ->raw(' ');
        $right = $this->getNode('right');
        if ($right instanceof ConstantExpression) {
            $compiler->raw($right->getAttribute('value'));
        } else {
            $compiler->subcompile($this->getNode('right'));
        }
        $compiler->raw(')');
    }

    /**
     * @param \Twig\Compiler $compiler
     *
     * @return \Twig\Compiler
     */
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('instanceof');
    }
}
