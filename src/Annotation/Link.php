<?php

namespace Imiskuf\BasicApiBundle\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Link
{
    public string $name;

    public string $route;

    public array $parameters = [];

    public function __construct(string $name, string $route, array $parameters = [])
    {
        $this->name = $name;
        $this->route = $route;
        $this->parameters = $parameters;
    }
}
