<?php

namespace ride\library\router;

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase {

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

    public function testGetBaseUrl() {
        $url = new Url('http://localhost', '/data/%id%/');

        $this->assertSame('http://localhost', $url->getBaseUrl());
    }

    public function testGetPath() {
        $url = new Url('http://localhost', '/data/%id%/');

        $this->assertSame('/data/%id%', $url->getPath());
    }

    public function testGetPathOnParsedPath() {
        $url = new Url('http://localhost', '/data/123/');

        $this->assertSame('/data/123', $url->getPath(true));
    }

    public function testGetArgument() {
        $url = new Url('http://localhost', '/data/123/');
        $url->setArgument('arg_name', 'arg_value');

        $this->assertSame('arg_value', $url->getArgument('arg_name'));
    }

    public function testGetArgumentShouldReturnDefaultValue() {
        $url = new Url('http://localhost', '/data/123/');

        $this->assertNull($url->getArgument('arg_name'));    
    }

    public function testGetArguments() {
        $url = new Url('http://localhost', '/data/123/');
        $url->setArgument('arg_name', 'arg_value');

        $this->assertSame(array('arg_name' => 'arg_value'), $url->getArguments());
    }

    public function testGetQueryParameter() {
        $url = new Url('http://localhost', '/data/123/');
        $url->setQueryParameter('arg_name', 'arg_value');

        $this->assertSame('arg_value', $url->getQueryParameter('arg_name'));
    }

    public function testGetQueryParameterShouldReturnNull() {
        $url = new Url('http://localhost', '/data/123/');

        $this->assertNull($url->getQueryParameter('arg_name'));
    }

    public function testGetQueryParameters() {
        $url = new Url('http://localhost', '/data/123/');
        $url->setQueryParameter('arg_name1', 'arg_value1');
        $url->setQueryParameter('arg_name2', 'arg_value2');

        $this->assertSame(array('arg_name1' => 'arg_value1', 'arg_name2' => 'arg_value2'), $url->getQueryParameters());
    }

}
