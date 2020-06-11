<?php

namespace gingerberry;

class Test
{
    private $a = 5;
    private $b = 6;

    public function __construct()
    {
    }

    public function sum()
    {
        return $this->a + $this->b;
    }

    public function prod()
    {
        return $this->a * $this->b;
    }
}
