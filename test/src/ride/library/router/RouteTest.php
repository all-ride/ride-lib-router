<?php

namespace ride\library\router;

use \PHPUnit_Framework_TestCase;

class RouteTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerConstruct
     */
    public function testConstruct($expectedPath, $path) {
        $callback = 'callback';

        $route = new Route($path, $callback);

        $this->assertEquals($route->getPath(), $expectedPath);
        $this->assertEquals($callback, $route->getCallback());
        $this->assertNull($route->getId());
        $this->assertNull($route->getAllowedMethods());
        $this->assertEmpty($route->getArguments());
        $this->assertEmpty($route->getPredefinedArguments());
        $this->assertFalse($route->isDynamic());
        $this->assertNull($route->getLocale());
        $this->assertNull($route->getBaseUrl());

        $id = 'id';
        $allowedMethod = 'GET';

        $route = new Route($path, $callback, $id, $allowedMethod);

        $this->assertEquals($id, $route->getId());
        $this->assertEquals(array($allowedMethod => true), $route->getAllowedMethods());
    }

    public function providerConstruct() {
        return array(
            array('/path', 'path'),
            array('///admin', '///admin'),
        );
    }

    /**
     * @dataProvider providerSetPathThrowsExceptionWhenThePathIsInvalid
     * @expectedException ride\library\router\exception\RouterException
     */
    public function testSetPathThrowsExceptionWhenThePathIsInvalid($path) {
        new Route($path, 'callback');
    }

    public function providerSetPathThrowsExceptionWhenThePathIsInvalid() {
        return array(
            array(null), // no value
            array(''), // empty path
            array('&é"(§è!'), // invalid string
            array($this), // object
            array(array()), // array
        );
    }

    /**
     * @dataProvider providerSetIdThrowsExceptionWhenTheIdIsInvalid
     * @expectedException ride\library\router\exception\RouterException
     */
    public function testSetIdThrowsExceptionWhenTheIdIsInvalid($id) {
        new Route('/', 'callback', $id);
    }

    public function providerSetIdThrowsExceptionWhenTheIdIsInvalid() {
        return array(
            array(''), // empty id
            array($this), // object
            array(array()), // array
        );
    }

    public function testArguments() {
        $route = new Route('/path', 'callback');

        $this->assertEquals(array(), $route->getArguments());

        $arguments = array('var1' => 'value1');
        $route->setArguments($arguments);

        $this->assertEquals($arguments, $route->getArguments());
    }

    public function testPredefinedArguments() {
        $route = new Route('/path', 'callback');

        $this->assertEquals(array(), $route->getPredefinedArguments());

        $arguments = array('var1' => 'value1');
        $route->setPredefinedArguments($arguments);

        $this->assertEquals($arguments, $route->getPredefinedArguments());
    }

    public function testAllowedMethods() {
        $route = new Route('/path', 'callback');

        $this->assertNull($route->getAllowedMethods());
        $this->assertTrue($route->isMethodAllowed('ANY'));

        $route->setAllowedMethods(array('POST', 'GET'));
        $this->assertEquals(array('POST' => true, 'GET' => true), $route->getAllowedMethods());
        $this->assertTrue($route->isMethodAllowed('GET'));
        $this->assertTrue($route->isMethodAllowed('POST'));
        $this->assertFalse($route->isMethodAllowed('ANY-OTHER'));
    }

    /**
     * @dataProvider providerSetAllowedMethodsThrowsExceptionWhenInvalidMethodsProvided
     * @expectedException ride\library\router\exception\RouterException
     */
    public function testSetAllowedMethodsThrowsExceptionWhenInvalidMethodsProvided($allowedMethods) {
        $route = new Route('/', 'callback');
        $route->setAllowedMethods($allowedMethods);
    }

    public function providerSetAllowedMethodsThrowsExceptionWhenInvalidMethodsProvided() {
        return array(
            array($this), // object
            array(array($this)), // array
        );
    }

    public function testGetUrl() {
        $path = '/path/%var1%/to/%var2%/%var3%';
        $route = new Route($path, 'callback');
        $baseUrl = 'http://localhost';

        $url = $route->getUrl($baseUrl, array('var1' => 1, 'var2' => 'test', 'var3' => 'A encode test'));

        $this->assertEquals($baseUrl . '/path/1/to/test/A+encode+test', $url);

        $baseUrl2 = 'http://server';

        $route->setBaseUrl($baseUrl2);

        $url = $route->getUrl($baseUrl, array('var1' => 1, 'var2' => 'test', 'var3' => 'A encode test'));

        $this->assertEquals($baseUrl2 . '/path/1/to/test/A+encode+test', $url);
    }

    public function testToString() {
        $route = new Route('/path', 'callback');

        $this->assertEquals('/path callback() s[*]', (string) $route);

        $route = new Route('/path/%var1%', 'callback');
        $route->setArguments(array('%var1%' => 'value1'));

        $this->assertEquals('/path/%var1% callback(\'value1\') s[*]', (string) $route);

        $route = new Route('/path/%var1%', 'callback');
        $route->setPredefinedArguments(array('%var1%' => 'value1'));

        $this->assertEquals('/path/%var1% callback(\'value1\') s[*]', (string) $route);

        $route = new Route('/path', array('Class', 'method'), 'id', array('POST', 'GET'));
        $route->setIsDynamic(true);

        $this->assertEquals('/path Class::method() d[GET|POST]', (string) $route);
    }

    public function testPermissions() {
        $route = new Route('/path', 'callback');

        $this->assertNull($route->getPermissions());

        $permission = 'permission';

        $route->setPermissions($permission);

        $this->assertEquals($route->getPermissions(), array($permission));

        $permissions = array(
            'permission1',
            'permission2',
        );

        $route->setPermissions($permissions);

        $this->assertEquals($route->getPermissions(), $permissions);

        $route->setPermissions(null);

        $this->assertNull($route->getPermissions());
    }

}
