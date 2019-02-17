<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * Lazy loads the runtime implementations for a Twig element.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class Twig_FactoryRuntimeLoader implements RuntimeLoaderInterface
{
    private $map;

    /**
     * @param array $map An array where keys are class names and values factory callables
     */
    public function __construct($map = [])
    {
        $this->map = $map;
    }

    public function load($class)
    {
        if (isset($this->map[$class])) {
            $runtimeFactory = $this->map[$class];

            return $runtimeFactory();
        }
    }
}

class_alias('Twig_FactoryRuntimeLoader', 'Twig\RuntimeLoader\FactoryRuntimeLoader', false);
