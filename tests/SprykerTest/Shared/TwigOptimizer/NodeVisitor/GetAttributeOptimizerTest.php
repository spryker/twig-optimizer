<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\TwigOptimizer\NodeVisitor;

use Codeception\Test\Unit;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group TwigOptimizer
 * @group NodeVisitor
 * @group GetAttributeOptimizerTest
 * Add your own group annotations below this line
 */
class GetAttributeOptimizerTest extends Unit
{
    /**
     * @dataProvider getTests
     *
     * @return void
     */
    public function testGetAttributeOptimizer($data, $template, $output, $compiledCode, $optimizedCompiledCode, $differentData, $differentOutput)
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), ['cache' => false, 'autoescape' => false, 'strict_variables' => false]);
        $twig->addExtension(new GetAttributeOptimizer(false));

        $moduleNode = $twig->parse($twig->tokenize($twig->getLoader()->getSourceContext('template')));

        $actualCompiledCode = $twig->compile($moduleNode->getNode('body'));

        $this->assertEquals($compiledCode, $actualCompiledCode);

        $actual = $twig->render('template', $data);

        $this->assertEquals($output, $actual);

        $optimizedModuleNode = $twig->parse($twig->tokenize($twig->getLoader()->getSourceContext('template')));

        $actualOptimizedCompiledCode = $twig->compile($optimizedModuleNode->getNode('body'));

        $this->assertEquals($optimizedCompiledCode, $actualOptimizedCompiledCode);

        $actualDifferentData = $twig->render('template', $differentData);

        $this->assertEquals($differentOutput, $actualDifferentData);
    }

    public function getTests()
    {
        return [
            [
                ['foo' => new DataHolder('foobar')],
                '{{ foo.bar }}',
                'foobar',
                <<<'EOF'
// line 1
echo $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["foo"]) ? $context["foo"] : null), "bar", $this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array()));

EOF, <<<'EOF'
// line 1
echo ((((isset($context["foo"]) ? $context["foo"] : null) instanceof dataHolder)) ? ($context["foo"]->getBar()) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array())));

EOF, ['foo' => ['bar' => 'foobar']],
                'foobar',
            ],
            [
                ['foo' => new DataHolderChild('foobar')],
                '{{ foo.bar }}',
                'foobar',
                <<<'EOF'
// line 1
echo $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["foo"]) ? $context["foo"] : null), "bar", $this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array()));

EOF, <<<'EOF'
// line 1
echo ((((isset($context["foo"]) ? $context["foo"] : null) instanceof dataHolder)) ? ($context["foo"]->getBar()) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array())));

EOF, ['foo' => ['bar' => 'foobar']],
                'foobar',
            ],
            [
                ['foo' => ['bar' => 'foobar']],
                '{{ foo.bar }}',
                'foobar',
                <<<'EOF'
// line 1
echo $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["foo"]) ? $context["foo"] : null), "bar", $this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array()));

EOF, <<<'EOF'
// line 1
echo ((isset($context["foo"]["bar"])) ? ($context["foo"]["bar"]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array())));

EOF, ['foo' => new DataHolderChild('foobar')],
                'foobar',
            ],
            [
                ['foo' => ['bar' => 'foobar']],
                '{{ foo.bar }}',
                'foobar',
                <<<'EOF'
// line 1
echo $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["foo"]) ? $context["foo"] : null), "bar", $this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array()));

EOF, <<<'EOF'
// line 1
echo ((isset($context["foo"]["bar"])) ? ($context["foo"]["bar"]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "bar", array())));

EOF, [],
                '',
            ],
            [
                ['foo' => new DataHolder('foobar', 'obprop')],
                '{{ foo.prop }}',
                'obprop',
                <<<'EOF'
// line 1
echo $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["foo"]) ? $context["foo"] : null), "prop", $this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "prop", array()));

EOF, <<<'EOF'
// line 1
echo ((((isset($context["foo"]) ? $context["foo"] : null) instanceof dataHolder)) ? ($context["foo"]->prop) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), "prop", array())));

EOF, [],
                '',
            ],
            [
                ['foo' => ['bar' => 'foobar'], 'attr' => ['index' => 'bar']],
                '{{ foo[attr.index] }}',
                'foobar',
                <<<'EOF'
// line 1
echo ((isset($context["foo"][$this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["attr"]) ? $context["attr"] : null), "index", $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))])) ? ($context["foo"][$this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()), array(), "array")));

EOF, <<<'EOF'
// line 1
echo ((isset($context["foo"][((isset($context["attr"]["index"])) ? ($context["attr"]["index"]) : ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())))])) ? ($context["foo"][((isset($context["attr"]["index"])) ? ($context["attr"]["index"]) : ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())))]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), ((isset($context["attr"]["index"])) ? ($context["attr"]["index"]) : ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))), array(), "array")));

EOF, [],
                '',
            ],
            [
                ['attr' => ['index' => 'bar']],
                '{{ foo[attr.index]|default(0) }}',
                '0',
                <<<'EOF'
// line 1
echo ((((isset($context["foo"][$this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["attr"]) ? $context["attr"] : null), "index", $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))])) ? (true) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), $this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["attr"]) ? $context["attr"] : null), "index", $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())), array(), "array", true, true)))) ? (_twig_default_filter(((isset($context["foo"][$this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 2, (isset($context["attr"]) ? $context["attr"] : null), "index", $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))])) ? ($context["foo"][$this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()), array(), "array"))), 0)) : (0));

EOF, <<<'EOF'
// line 1
echo ((((isset($context["foo"][((isset($context["attr"]["index"])) ? ($context["attr"]["index"]) : ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())))])) ? (true) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), ((isset($context["attr"]["index"])) ? ($context["attr"]["index"]) : ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))), array(), "array", true, true)))) ? (_twig_default_filter(((isset($context["foo"][$this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 2, (isset($context["attr"]) ? $context["attr"] : null), "index", $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()))])) ? ($context["foo"][$this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array())]) : ($this->getAttribute((isset($context["foo"]) ? $context["foo"] : null), $this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "index", array()), array(), "array"))), 0)) : (0));

EOF, ['foo' => ['bar' => 'foobar'], 'attr' => ['index' => 'bar']],
                'foobar',
            ],
            [
                ['attr' => ['index' => 'bar']],
                '{{ nothing.field|default("def") }}',
                'def',
                <<<'EOF'
// line 1
echo (($this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 0, (isset($context["nothing"]) ? $context["nothing"] : null), "field", $this->getAttribute((isset($context["nothing"]) ? $context["nothing"] : null), "field", array(), "any", true, true))) ? (_twig_default_filter($this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 1, (isset($context["nothing"]) ? $context["nothing"] : null), "field", $this->getAttribute((isset($context["nothing"]) ? $context["nothing"] : null), "field", array())), "def")) : ("def"));

EOF, <<<'EOF'
// line 1
echo (($this->getAttribute((isset($context["nothing"]) ? $context["nothing"] : null), "field", array(), "any", true, true)) ? (_twig_default_filter($this->env->getExtension('Twig_Optimizations_Extension_GetAttributeOptimizer')->getAttribute($this, 1, (isset($context["nothing"]) ? $context["nothing"] : null), "field", $this->getAttribute((isset($context["nothing"]) ? $context["nothing"] : null), "field", array())), "def")) : ("def"));

EOF, ['nothing' => ['field' => 'foobar'], 'attr' => ['index' => 'bar']],
                'foobar',
            ],
        ];
    }
}

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group TwigOptimizer
 * @group NodeVisitor
 * @group DataHolder
 * Add your own group annotations below this line
 */
class DataHolder
{
    private $bar;

    public $prop;

    public function __construct($bar, $prop = '')
    {
        $this->bar = $bar;
        $this->prop = $prop;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group TwigOptimizer
 * @group NodeVisitor
 * @group DataHolderChild
 * Add your own group annotations below this line
 */
class DataHolderChild extends DataHolder
{
}
