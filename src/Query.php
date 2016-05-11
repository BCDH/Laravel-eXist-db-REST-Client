<?php

namespace BCDH\ExistDbRestClient;

use Sabre\Xml\Service;

class Query {
    /** @var ExistDbRestClient */
    protected $client;
    protected $variables;
    protected $params;
    protected $query;
    protected $storedQuery;
    protected $body;
    protected $collection;
    protected $resource;
    protected $binaryContent;

    public function __construct($client) {
        $this->client = $client;
        $this->variables = array();
        $this->params = array();
        $this->binaryContent = false;
    }

    /**
     * Append given parameter to request url
     *
     * @param $variableName
     * @param $value
     */
    public function bindParam($variableName, $value) {
        $this->params[$variableName] = $value;
    }

    /**
     * Format xsl query with given variable
     *
     * @param $variableName
     * @param $value
     */
    public function bindVariable($variableName, $value) {
        $this->variables[$variableName] = $value;
    }

    /**
     * Set request query
     *
     * @param string $query
     */
    public function setQuery($query) {
        $this->query = $query;
    }

    /**
     * @param string $storedQuery
     */
    public function setStoredQuery($storedQuery) {
        $this->storedQuery = $storedQuery;
    }

    /**
     * Set request collection
     *
     * @param string $collection
     */
    public function setCollection($collection) {
        $this->collection = ltrim(rtrim($collection, '/'), '/');
    }

    /**
     * Set request resource
     *
     * @param string $resource
     */
    public function setResource($resource) {
        $this->resource = ltrim(rtrim($resource, '/'), '/');
    }

    /**
     * Set boolean flag, that request body is not application/xml
     *
     * @param bool $binaryContent
     */
    public function setBinaryContent($binaryContent) {
        $this->binaryContent = $binaryContent;
    }

    /**
     * @return bool
     */
    public function isBinaryContent() {
        return $this->binaryContent;
    }

    /**
     * Set request body
     *
     * @param string $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * Make request options
     *
     * @param string $method Request HTTP method
     * @return array
     */
    private function makeOptions($method) {
        $options = array();
        $options['auth'] = array(
            $this->client->getUser(),
            $this->client->getPassword()
        );

        if($this->isBinaryContent() != false) {
            $options['headers'] = array(
                "Content-Type" => "application/xml"
            );
        }

        if (strtolower($method) == "get") {
            $options['query'] = $this->generateParams();
        } else {
            $options['query'] = $this->params;
        }

        $formattedQuery = $this->getFormattedQuery();
        if($formattedQuery != "") {
            $options['query']['_query'] = $formattedQuery;
        }
        $options['query']['_variables'] = array();

        if ($this->body != null) {
            $options['body'] = $this->body;
        }

        return $options;
    }

    /**
     * Execute query
     *
     * @param string $method
     * @param Service $parserService
     * @return XMLResult|null
     */
    private function execute($method, $parserService = null) {
        $options = $this->makeOptions($method);
        $wrap = !isset($options['query']['_wrap']) ? false : (strtolower($options['query']['_wrap']) != "yes");
        $url = $this->generateUrl();

        $request = new Request($method, $url, $options);
        $response = $this->client->execute($request);
        return $this->parseResponse($response, $wrap, $parserService);
    }

    private function generateParams() {
        $defaultParams = array(
            "_howmany" => $this->client->getHowMany(),
            "_xsl" => $this->client->getXsl(),
            "_indent" => $this->client->getIndent(),
            "_start" => $this->client->getStart(),
            "_wrap" => $this->client->getWrap(),
        );

        return array_merge($defaultParams, $this->params);
    }

    /**
     * TODO: simple draft
     * Format query with set variables
     *
     * @return string
     */
    public function getFormattedQuery() {
        $query = $this->query;

        $replace = array();
        $replaceBy = array();

        foreach ($this->variables as $variableName => $variableValue) {
            $replace[] = '$' . $variableName;
            if (!is_string($variableValue)) {
                $replaceBy[] = $variableValue;
            } else {
                $replaceBy[] = '"' . addslashes($variableValue) . '"';
            }
        }

        return str_replace($replace, $replaceBy, $query);
    }

    /**
     * Generate request url in format
     * $protocol://$host:$port/$path/$collection/$resource/$storedQuery/
     *
     * @return string
     */
    private function generateUrl() {
        if ($this->client->getUri() != null) {
            $url = $this->client->getUri();
        } else {
            $url = $this->client->getProtocol() . "://" . $this->client->getHost() . ":" .
                $this->client->getPort() . "/" . $this->client->getPath() . "/";
        }

        if($this->collection != null) {
            $url .= $this->collection . "/";
        }

        if($this->resource != null) {
            $url .= $this->resource . "/";
        }

        if($this->storedQuery != null) {
            $url .= $this->storedQuery . "/";
        }

        return $url;
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $response
     * @param boolean $wrapString Wrap string if _wrap was disabled
     * @param Service $parserService
     * @return XMLResult|null
     */
    private function parseResponse($response, $wrapString, $parserService) {
        $responseXml = (string) $response->getBody()->getContents();

        if ($responseXml == '') {
            return null;
        }

        if ($wrapString) {
            $responseXml = '<exist:result xmlns:exist="http://exist.sourceforge.net/NS/exist">' . $responseXml . '</exist:result>';
        }

        return new XMLResult($responseXml, $parserService);
    }

    /**
     * Send query as GET request
     *
     * @param Service $parserService
     * @return XMLResult|null
     */
    public function get($parserService = null) {
        return $this->execute("GET", $parserService);
    }

    /**
     * Send query as POST request
     *
     * @param Service $parserService
     * @return XMLResult|null
     */
    public function post($parserService = null) {
        return $this->execute("POST", $parserService);
    }

    /**
     * Send query as DELETE request
     *
     * @param Service $parserService
     * @return XMLResult|null
     */
    public function delete($parserService = null) {
        return $this->execute("DELETE", $parserService);
    }

    /**
     * Send query as PUT request
     *
     * @param Service $parserService
     * @return XMLResult|null
     */
    public function put($parserService = null) {
        return $this->execute("PUT", $parserService);
    }
}