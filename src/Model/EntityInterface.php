<?php

namespace Imiskuf\BasicApiBundle\Model;

interface EntityInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;
}