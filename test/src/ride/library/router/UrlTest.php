<?php

namespace ride\library\router;

use \PHPUnit_Framework_TestCase;

class UrlTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerToString
     */
    public function testToString($expected, $baseUrl, $path, $pathParameters, $queryParameters) {
        $url = new Url($baseUrl, $path, $pathParameters, $queryParameters);

        $this->assertEquals($expected, (string) $url);
    }

    public function providerToString() {
        return array(
            array('http://localhost', 'http://localhost', null, null, null),
            array('http://localhost', 'http://localhost', '/', null, null),
            array('http://example.com/path/to', 'http://example.com', '/path/to', null, null),
            array('http://example.com/data/1/edit', 'http://example.com', '/data/%id%/%action%', array('id' => 1, 'action' => 'edit'), null),
            array('http://example.com/data/1/edit?format=json', 'http://example.com', '/data/%id%/%action%', array('id' => 1, 'action' => 'edit'), array('format' => 'json')),
            array('http://example.com/data?filter[type]=general&filter[published]=1', 'http://example.com', '/data', null, array('filter' => array('type' => 'general', 'published' => 1))),
            array('http://example.com/data/%id%/%action%', 'http://example.com', '/data/%id%/%action%', array('id' => 1), null),
        );
    }

    /**
     * @dataProvider providerGetUrlThrowsExceptionWhenInvalidArgumentsProvided
     * @expectedException ride\library\router\exception\RouterException
     */
    public function testGetUrlThrowsExceptionWhenInvalidArgumentsProvided($arguments) {
        $url = new Url('http://localhost', '/path/%var1%/to/%var2%', $arguments);
        $url->getUrl();
    }

    public function providerGetUrlThrowsExceptionWhenInvalidArgumentsProvided() {
        return array(
            array(array('var1' => 'var1')), // expected argument not set
            array(array('var1' => $this, 'var2' => 'var2')), // invalid value
        );
    }

    public function testSetParameter() {
        $url = new Url('http://localhost', '/data/%id%/');

        $this->assertEquals('http://localhost/data/%id%', (string) $url);

        $url->setArgument('id', 3);

        $this->assertEquals('http://localhost/data/3', (string) $url);

        $url->setQueryParameter('query', 'test with some spaces');

        $this->assertEquals('http://localhost/data/3?query=test+with+some+spaces', (string) $url);

        $url->setQueryParameter('query', null);

        $this->assertEquals('http://localhost/data/3', (string) $url);
    }

}
