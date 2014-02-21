<?php

namespace ride\library\router;

use \PHPUnit_Framework_TestCase;

class GenericRouterTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $container = new RouteContainer();

        $router = new GenericRouter($container);

        $this->assertEquals($container, $router->getRouteContainer());
        $this->assertNull($router->getDefaultCallback());
    }

    public function testRoute() {
        $container = new RouteContainer();
        $router = new GenericRouter($container);
        $method = 'GET';
        $path = '/path';

        $result = $router->route($method, $path);
        $this->assertTrue($result->isEmpty());

        $route = new Route($path, 'callback');
        $container->addRoute(new Route('/', 'callback'));
        $container->addRoute($route);

        $result = $router->route($method, $path);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route, $result->getRoute());
        $this->assertNull($result->getAllowedMethods());

        $result = $router->route($method, $path . '?foo=bar');
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route, $result->getRoute());
        $this->assertNull($result->getAllowedMethods());
    }

    public function testRouteWithArguments() {
        $container = new RouteContainer();
        $router = new GenericRouter($container);
        $method = 'GET';
        $path1 = '/path1/%var1%';
        $path2 = '/path2/%var1%/test';
        $path3 = '/path3/%var1%/sme/%var2%/%var3%';
        $path4 = '/path2/sme/test';

        $route1 = new Route($path1, 'callback');
        $route2 = new Route($path2, 'callback');
        $route3 = new Route($path3, 'callback');
        $route4 = new Route($path4, 'callback');
        $container->addRoute($route1);
        $container->addRoute($route4);
        $container->addRoute($route2);
        $container->addRoute($route3);

        // test route 1
        $result = $router->route($method, '/');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path1');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path1/foo/bar');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path1/foo');
        $route1->setArguments(array('var1' => 'foo'));
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route1, $result->getRoute());

        // test route 2
        $result = $router->route($method, '/path2/foo/bar');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path2/foo/test/bar');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path2/foo/test');
        $route2->setArguments(array('var1' => 'foo'));
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route2, $result->getRoute());

        // test route 3
        $result = $router->route($method, '/path3/foo/sme');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path3/foo/sme/bar/sme/foo');
        $this->assertTrue($result->isEmpty());

        $result = $router->route($method, '/path3/foo/sme/bar/test');
        $arguments = array(
                'var1' => 'foo',
                'var2' => 'bar',
                'var3' => 'test',
        );
        $route3->setArguments($arguments);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route3, $result->getRoute());

        // test route 4
        $result = $router->route($method, '/path2/sme/test');
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route4, $result->getRoute());
    }

    public function testRouteWithDynamicArguments() {
        $container = new RouteContainer();
        $router = new GenericRouter($container);
        $method = 'GET';
        $path = '/path';

        $route = new Route($path, 'callback');
        $route->setIsDynamic(true);
        $container->addRoute($route);

        $result = $router->route($method, $path);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route, $result->getRoute());

        $result = $router->route($method, $path . '/value1/value2');
        $this->assertFalse($result->isEmpty());
        $route->setArguments(array('value1', 'value2'));
        $this->assertEquals($route, $result->getRoute());

        $path2 = $path . '/to';

        $route = new Route($path2, 'callback');
        $route->setIsDynamic(true);
        $container->addRoute($route);

        $result = $router->route($method, $path2);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route, $result->getRoute());

        $result = $router->route($method, $path2 . '/value1/value2');
        $this->assertFalse($result->isEmpty());
        $route->setArguments(array('value1', 'value2'));
        $this->assertEquals($route, $result->getRoute());

        $path3 = $path . '/%var1%/to';

        $route = new Route($path3, 'callback');
        $route->setIsDynamic(true);
        $container->addRoute($route);

        $result = $router->route($method, $path . '/value1/to/value2');
        $this->assertFalse($result->isEmpty());
        $route->setArguments(array('var1' => 'value1', 'value2'));
        $this->assertEquals($route, $result->getRoute());
    }

    public function testRouteWithAllowedMethod() {
        $container = new RouteContainer();
        $router = new GenericRouter($container);
        $method1 = 'GET';
        $method2 = 'POST';
        $path = '/path';

        $result = $router->route($method1, $path);
        $this->assertTrue($result->isEmpty());

        $route1 = new Route($path, 'callback', null, $method1);
        $route2 = new Route($path, 'callback', null, $method2);
        $container->addRoute($route1);
        $container->addRoute($route2);

        $result = $router->route('PUT', $path);
        $this->assertFalse($result->isEmpty());
        $this->assertNull($result->getRoute());
        $this->assertEquals(array($method1 => true, $method2 => true), $result->getAllowedMethods());

        $result = $router->route($method2, $path);
        $this->assertFalse($result->isEmpty());
        $this->assertNull($result->getAllowedMethods());
        $this->assertEquals($route2, $result->getRoute());
    }

    public function testRouteWithBaseUrl() {
        $container = new RouteContainer();
        $router = new GenericRouter($container);
        $method = 'GET';
        $path = '/path';
        $url1 = 'http://localhost/test';
        $url2 = 'http://some.server/test';
        $url3 = 'http://other.server/test';

        $route1 = new Route($path, 'callback');
        $route1->setBaseUrl($url1);
        $route2 = new Route($path, 'callback');
        $route2->setBaseUrl($url2);
        $container->addRoute($route1);
        $container->addRoute($route2);

        $result = $router->route($method, $path, $url1);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route1, $result->getRoute());

        $result = $router->route($method, $path, $url2);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route2, $result->getRoute());

        $result = $router->route($method, $path, $url3);
        $this->assertTrue($result->isEmpty());

        $route3 = new Route($path, 'callback');
        $container->addRoute($route3);

        $result = $router->route($method, $path, $url3);
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($route3, $result->getRoute());
    }

    public function testDefaultCallback() {
        $callback = 'function';
        $method = 'GET';
        $container = new RouteContainer();
        $router = new GenericRouter($container);

        $result = $router->route($method, '/');
        $this->assertTrue($result->isEmpty());

        $router->setDefaultCallback($callback);

        $result = $router->route($method, '/');
        $this->assertFalse($result->isEmpty());
        $this->assertEquals($callback, $result->getRoute()->getCallback());

    }

}