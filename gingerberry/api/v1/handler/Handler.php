<?php

namespace gingerberry\api\v1\handler;

abstract class Handler
{
    abstract public function discoverEndpoints();

    protected function setCORSHeaders()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials : true");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }
}
