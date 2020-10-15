<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TwigOptimizer\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TwigFunction;

class GetAttributeOptimizerExtension extends AbstractExtension
{
    private $types = [];

    private $env;

    public function __construct($registerShutdownFunction = true)
    {
        if ($registerShutdownFunction) {
            register_shutdown_function([$this, 'recompileOptimizableTemplates']);
        }
    }

    /**
     * @return void
     */
    public function initRuntime(Environment $environment)
    {
        $this->env = $environment;
    }

    public function getNodeVisitors()
    {
        return [new GetAttributeOptimizer()];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('optimizer_twig_get_attribute', [$this, 'getAttribute']),
            new TwigFunction('isset', 'isset'),
        ];
    }

    public function getName()
    {
        return 'attr_optimizer';
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getAttribute(Template $template, $nodeId, $object, $item, $result)
    {
        $templateName = $template->getTemplateName();

        $this->types[$templateName][$nodeId] = ['attr' => (string)$item, 'class' => is_array($object) ? 'array' : (is_object($object) ? get_class($object) : false)];

        return $result;
    }

    /**
     * @return void
     */
    public function recompileOptimizableTemplates()
    {
        if (empty($this->env)) {
            return;
        }
        $cache = $this->env->getCache(false);

        foreach ($this->env->getExtension('attr_optimizer')->getTypes() as $name => $types) {
            $cls = $this->env->getTemplateClass($name);
            $content = $this->env->compileSource($this->env->getLoader()->getSource($name), $name);
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
