<?php

namespace Imiskuf\BasicApiBundle\Model;

interface ArrayableInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
