<?php

namespace gingerberry\api\v1\model;

class Slide
{
    public $id;
    public $presentation_id;
    public $title;

    public function __construct($id, $presentation_id, $title)
    {
        $this->id = $id;
        $this->presentation_id = $presentation_id;
        $this->title = $title;
    }
}
