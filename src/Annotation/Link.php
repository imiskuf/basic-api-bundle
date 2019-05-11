<?php

namespace Imiskuf\BasicApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Link
{
    /**
     * @Required()
     */
    public $name;

    /**
     * @Required()
     */
    public $route;

    /**
     * @var array
     */
    public $parameters = [];
}
