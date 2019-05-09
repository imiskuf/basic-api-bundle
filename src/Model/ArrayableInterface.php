<?php

namespace BasicApi\Model;

interface ArrayableInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
