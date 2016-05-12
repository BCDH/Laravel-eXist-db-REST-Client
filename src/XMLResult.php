<?php

namespace BCDH\ExistDbRestClient;

use Sabre\Xml\Service;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use XSLTProcessor;

class XMLResult implements ResultInterface {
    /**
     * @var array|object|string
     */
    private $document;

    /**
     * @var string
     */
    private $rawResult;

    /**
     * @var Service Parser service
     * @link http://sabre.io/xml/reading/
     */
    private $service;

    function __construct($documentScalar, $parserService = null) {
        if ($parserService == null) {
            $this->service = new Service();
        } else {
            $this->service = $parserService;
        }

        $this->rawResult = $documentScalar;

        $this->document = $this->service->parse($documentScalar);
    }

    /**
     * XLS Transformations
     *
     * @param string $view Stylesheet file path
     * @param string|array|XmlSerializable $value Document to be transformed
     * @param string $rootTagName Root tag name to be created
     * @return string
     */
    public function transform($view, $value = null, $rootTagName = null) {
        if ($value == null) {
            $value = $this->document;
        }

        if ($rootTagName == null) {
            $rootTagName = $this->document[0]['name'];

            $writer = new Writer();
            $writer->openMemory();
            $writer->startDocument('1.0', 'UTF-8');

            $writer->write(
                array(
                    $rootTagName => $value
                )
            );

            $xml = $writer->outputMemory();
        } else {
            $xml = (string)$this->service->write($rootTagName, $value);
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $stylesheet = file_get_contents($view);
        $stylesheetDom = new \DOMDocument();
        $stylesheetDom->loadXML($stylesheet);

        $xsltProcessor = new XSLTProcessor();
        $xsltProcessor->registerPHPFunctions();
        $xsltProcessor->importStylesheet($stylesheetDom);
        return $xsltProcessor->transformToXml($dom);
    }

    /**
     * @return array|object|string
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getRawResult() {
        return $this->rawResult;
    }
}