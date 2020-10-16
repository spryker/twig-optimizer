<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TwigOptimizer\Extension;

use Spryker\Shared\TwigOptimizer\NodeVisitor\GetAttributeOptimizerVisitor;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TwigFunction;

class GetAttributeOptimizerExtension extends AbstractExtension
{
    private $types = [];

    private $twig;

    /**
     * @param Environment $twig
     * @param bool $registerShutdownFunction
     */
    public function __construct(Environment $twig, $registerShutdownFunction = true)
    {
        $this->twig = $twig;
        if ($registerShutdownFunction) {
            register_shutdown_function([$this, 'recompileOptimizableTemplates']);
        }
    }

    public function getNodeVisitors()
    {
        return [new GetAttributeOptimizerVisitor()];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('optimizer_twig_get_attribute', [$this, 'getAttribute']),
            new TwigFunction('isset', 'isset'),
        ];
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getAttribute($templateName, $nodeId, $object, $item, $result)
    {
        $this->types[$templateName][$nodeId] = ['attr' => (string)$item, 'class' => is_array($object) ? 'array' : (is_object($object) ? get_class($object) : false)];

        return $result;
    }

    /**
     * @return void
     */
    public function recompileOptimizableTemplates()
    {
        if (empty($this->twig)) {
            return;
        }

        $cache = $this->twig->getCache(false);
        $loader = $this->twig->getLoader();

        foreach ($this->twig->getExtension(static::class)->getTypes() as $name => $types) {
            $cls = $this->twig->getTemplateClass($name);
            $loader = $this->twig->getLoader();
            $content = $this->twig->compileSource($this->twig->getLoader()->getSource($name), $name);

            $key = $cache->generateKey($name, $cls);
            $cache->write($key, $content);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($key, true);
            } elseif (function_exists('apc_compile_file')) {
                apc_compile_file($key);
            }
        }
    }
}
