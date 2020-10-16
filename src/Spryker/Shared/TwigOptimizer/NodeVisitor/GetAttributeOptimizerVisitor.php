<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TwigOptimizer\NodeVisitor;

use ReflectionClass;
use Spryker\Shared\TwigOptimizer\Extension\GetAttributeOptimizerExtension;
use Spryker\Shared\TwigOptimizer\Node\Expression\ArrayAccessExpression;
use Spryker\Shared\TwigOptimizer\Node\Expression\Binary\InstanceOfBinary;
use Spryker\Shared\TwigOptimizer\Node\Expression\GetPropertyExpression;
use Twig\Environment;
use Twig\Node\Expression\ConditionalExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Expression\MethodCallExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\Template;

class GetAttributeOptimizerVisitor extends AbstractNodeVisitor
{
    private $index = 0;

    private $types = [];

    private $cache = [];

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function doEnterNode(Node $node, Environment $env)
    {
        if ($node instanceof ModuleNode) {
            $allTypes = $env->getExtension(GetAttributeOptimizerExtension::class)->getTypes();
            $templateName = $node->getTemplateName();

            if (isset($allTypes[$templateName])) {
                $this->types = $allTypes[$templateName];
            }
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    protected function doLeaveNode(Node $node, Environment $env)
    {
        if ($node instanceof GetAttrExpression) {
            if (
                $node->getAttribute('type') === Template::ARRAY_CALL ||
                ($node->getAttribute('type') !== Template::METHOD_CALL && isset($this->types[$this->index]) && $this->types[$this->index]['class'] === 'array')
            ) {
                $node = $this->getArrayAccessNode($node);
            } elseif (isset($this->types[$this->index])) {
                $type = $this->types[$this->index];

                if ($node->getAttribute('type') !== Template::METHOD_CALL && ($newType = $this->getTypeWithProperty($type['class'], $type['attr']))) {
                    $node = $this->getObjectPropertyNode($node, $newType);
                } elseif (($newType = $this->getTypeWithMethod($type['class'], $type['attr']))) {
                    if ($newType['class'] == 'array') {
                        $node = $this->getArrayAccessNode($node);
                    } else {
                        $node = $this->getMethodCallNode($node, $newType);
                    }
                }
            } else {
                $node = $this->getRecordGetAttributeCallsNode($node);
            }

            $this->index++;
        } elseif ($node instanceof ModuleNode) {
            $this->types = [];
            $this->index = 0;
        }

        return $node;
    }

    private function getTypeWithProperty($class, $propertyName)
    {
        if (empty($class)) {
            return null;
        }

        $refClass = $this->getReflectionClass($class);

        if ($refClass->hasProperty($propertyName)) {
            $prop = $refClass->getProperty($propertyName);

            if ($prop->isPublic()) {
                return ['class' => $prop->class, 'attr' => $propertyName];
            }
        }

        return null;
    }

    private function getTypeWithMethod($class, $methodName)
    {
        if (empty($class) || $class == 'array' || is_a($class, 'Twig_Template', true)) {
            return null;
        }

        $refClass = $this->getReflectionClass($class);

        if (
            ($refClass->hasMethod($methodName) && ($method = $refClass->getMethod($methodName)) && $method->isPublic()) ||
            ($refClass->hasMethod('get' . $methodName) && ($method = $refClass->getMethod('get' . $methodName)) && $method->isPublic()) ||
            ($refClass->hasMethod('is' . $methodName) && ($method = $refClass->getMethod('is' . $methodName)) && $method->isPublic()) ||
            ($refClass->hasMethod('has' . $methodName) && ($method = $refClass->getMethod('has' . $methodName)) && $method->isPublic())
        ) {
            return ['class' => $method->class, 'attr' => $method->getName()];
        } elseif ($refClass->hasMethod('__call') && ($method = $refClass->getMethod('__call')) && $method->isPublic()) {
            return ['class' => $method->class, 'attr' => $methodName];
        } elseif ($refClass->implementsInterface('ArrayAccess')) {
            return ['class' => 'array', 'attr' => $methodName];
        }

        return false;
    }

    /**
     * @param string $class
     *
     * @return \ReflectionClass
     */
    private function getReflectionClass($class)
    {
        if (!isset($this->cache[$class])) {
            $this->cache[$class] = new ReflectionClass($class);
        }

        return $this->cache[$class];
    }

    private function getObjectPropertyNode($node, $type)
    {
        $nameNode = clone $node->getNode('node');
        $nameNode->setAttribute('ignore_strict_check', true);

        $testExpr = new InstanceOfBinary(
            $nameNode,
            $type['class'],
            $node->getTemplateLine()
        );

        if ($node->getAttribute('is_defined_test')) {
            $attrNode = new ConstantExpression(true, $node->getTemplateLine());
        } else {
            $attrNode = new GetPropertyExpression(
                clone $node->getNode('node'),
                $type['attr'],
                $node->getTemplateLine()
            );
        }

        if ($attrNode) {
            return new ConditionalExpression(
                $testExpr,
                $attrNode,
                $node,
                $node->getTemplateLine()
            );
        } else {
            return $node;
        }
    }

    private function getMethodCallNode($node, $type)
    {
        $nameNode = clone $node->getNode('node');
        $nameNode->setAttribute('ignore_strict_check', true);
        $testExpr = new InstanceOfBinary(
            $nameNode,
            $type['class'],
            $node->getTemplateLine()
        );

        if ($node->getAttribute('is_defined_test')) {
            $attrNode = new ConstantExpression(true, $node->getTemplateLine());
        } else {
            $attrNode = new MethodCallExpression(
                clone $node->getNode('node'),
                $type['attr'],
                $node->getNode('arguments'),
                $node->getTemplateLine()
            );
        }

        if ($attrNode) {
            return new ConditionalExpression(
                $testExpr,
                $attrNode,
                $node,
                $node->getTemplateLine()
            );
        } else {
            return $node;
        }
    }

    private function getArrayAccessNode(Node $node)
    {
        $originalAttributeNode = $node->getNode('attribute');

        $attrNode = new ArrayAccessExpression(
            clone $node->getNode('node'),
            $originalAttributeNode,
            $node->getTemplateLine()
        );

        if ($node->getAttribute('is_defined_test')) {
            $simpleAttrNode = new ConstantExpression(true, $node->getTemplateLine());
        } elseif ($originalAttributeNode instanceof FunctionExpression && $originalAttributeNode->getAttribute('name') === 'optimizer_twig_get_attribute') {
            $realGetAttr = $originalAttributeNode->getNode('arguments')->getNode(4);

            $node->setNode('attribute', $realGetAttr);

            $simpleAttrNode = clone $attrNode;
            $simpleAttrNode->setNode('name', $realGetAttr);
        } else {
            $simpleAttrNode = $attrNode;
        }

        $testExpr = new FunctionExpression(
            'isset',
            new Node([$attrNode], [], $node->getTemplateLine()),
            $node->getTemplateLine()
        );

        return new ConditionalExpression(
            $testExpr,
            $simpleAttrNode,
            $node,
            $node->getTemplateLine()
        );
    }

    private function getRecordGetAttributeCallsNode(Node $node)
    {
        $nameNode = clone $node->getNode('node');
        $nameNode->setAttribute('ignore_strict_check', true);

        return new FunctionExpression(
            'optimizer_twig_get_attribute',
            new Node(
                [
                    new NameExpression('_self', $node->getTemplateLine()),
                    new ConstantExpression($this->index, $node->getTemplateLine()),
                    $nameNode,
                    $node->getNode('attribute'),
                    $node,
                ],
                [],
                $node->getTemplateLine()
            ),
            $node->getTemplateLine()
        );
    }
}
