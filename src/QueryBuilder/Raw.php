<?php


namespace Sre\QueryBuilder;


class Raw
{
    private $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }
}