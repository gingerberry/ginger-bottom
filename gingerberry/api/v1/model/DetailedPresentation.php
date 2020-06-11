<?php

namespace gingerberry\api\v1\model;

use gingerberry\api\v1\model\Presentation;


class DetailedPresentation extends Presentation
{
    public $slides;

    public function __construct($id, $name, $slides)
    {
        parent::__construct($id, $name);

        $this->slides = $slides;
    }
}
