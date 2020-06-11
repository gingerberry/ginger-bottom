<?php

namespace gingerberry\router;

use gingerberry\router\IRequest;

class Router
{
    private $request;
    private $supportedHttpMethods = array(
        "GET",
        "POST"
    );

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function __destruct()
    {
        $this->resolve();
    }

    public function __call($name, $args)
    {
        list($route, $method) = $args;

        if (!\in_array(\strtoupper($name), $this->supportedHttpMethods)) {
            $this->invalidMethodHandler();
        }

        $this->{\strtolower($name)}[$this->formatRoute($route)] = $method;
    }

    public function resolve()
    {
        $methodDict = $this->{\strtolower($this->request->requestMethod)};
        $formatedRoute = $this->formatRoute($this->request->requestUri);

        $method = null;

        foreach ($methodDict as $key => $value) {
            if (\preg_match($key, $formatedRoute)) {
                $method = $methodDict[$key];
            }
        }
        
        if (\is_null($method)) {
            $this->defaultRequestHandler();
            return;
        }

        echo \call_user_func_array($method, array($this->request));
    }

    private function formatRoute($route)
    {
        $result = \rtrim($route, '/');
        if ($result == '') {
            return '/';
        }

        return $result . '/';
    }

    private function invalidMethodHandler()
    {
        \header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }

    private function defaultRequestHandler()
    {
        \header("{$this->request->serverProtocol} 404 Not Found");
    }
}
