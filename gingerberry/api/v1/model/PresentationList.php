<?php

namespace gingerberry\api\v1\model;

class PresentationList
{
    public $presentations;

    public function __construct($presentations)
    {
        $this->presentations = $presentations;
    }
}
