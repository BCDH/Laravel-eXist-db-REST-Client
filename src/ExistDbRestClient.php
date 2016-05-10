<?php

namespace BCDH\ExistDbRestClient;

use GuzzleHttp\Client as GuzzleClient;

class ExistDbRestClient {
    /**
     * @var GuzzleClient $client
     */
    protected $client;

    private $options;

    public function __construct($options = null) {
        if (function_exists('config')) {
            $this->options = config("exist-db");
        } else {
            $this->options = $options;
        }

        $this->client = new GuzzleClient();
    }

    public function prepareQuery() {
        return new Query($this);
    }

    public function execute(Request $r) {
        return $this->client->request($r->method, $r->uri, $r->options);
    }

    public function getUser() {
        return $this->options['user'];
    }

    public function setUser($user) {
        $this->options['user'] = $user;
    }

    public function getPassword() {
        return $this->options['password'];
    }

    public function setPassword($user) {
        $this->options['password'] = $user;
    }

    public function getUri() {
        return $this->options['uri'];
    }

    public function setUri($uri) {
        $this->options['uri'] = $uri;
    }

    public function getProtocol() {
        return $this->options['protocol'];
    }

    public function setProtocol($protocol) {
        $this->options['protocol'] = $protocol;
    }

    public function getHost() {
        return $this->options['host'];
    }

    public function setHost($host) {
        $this->options['host'] = $host;
    }

    public function getPort() {
        return $this->options['port'];
    }

    public function setPort($port) {
        $this->options['port '] = $port;
    }

    public function getPath() {
        return $this->options['path'];
    }

    public function setPath($path) {
        $this->options['path'] = $path;
    }

    public function getCollection() {
        return $this->options['collection'];
    }

    public function setCollection($collection) {
        $this->options['collection'] = $collection;
    }

    public function getResource() {
        return $this->options['resource'];
    }

    public function setResource($resource) {
        $this->options['resource'] = $resource;
    }

    public function getHowMany() {
        return $this->options['howMany'];
    }

    public function setHowMany($howMany) {
        $this->options['howMany'] = $howMany;
    }

    public function getXsl() {
        return $this->options['xsl'];
    }

    public function setXsl($xsl) {
        $this->options['xsl'] = $xsl;
    }

    public function getIndent() {
        return $this->options['indent'];
    }

    public function setIndent($indent) {
        $this->options['indent'] = $indent;
    }

    public function getStart() {
        return $this->options['start'];
    }

    public function setStart($start) {
        $this->options['start'] = $start;
    }

    public function getWrap() {
        return $this->options['wrap'];
    }

    public function setWrap($wrap) {
        $this->options['wrap'] = $wrap;
    }
}