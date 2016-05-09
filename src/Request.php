<?php namespace BCDH\ExistDbRestClient;

class Request {
    public $method;
    public $uri;
    public $options;

    public function __construct($method, $uri, $options) {
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
    }
}