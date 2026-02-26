<?php

namespace Imiskuf\BasicApiBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
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

    public function __construct(array $data = [], string $name = null, string $route = null, array $parameters = [])
    {
        $this->name = $data['name'] ?? $name;
        $this->route = $data['route'] ?? $route;
        $this->parameters = $data['parameters'] ?? $parameters;
    }
}
