<?php

namespace pallo\library\router;

use \PHPUnit_Framework_TestCase;

class RouteContainerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var RouteContainer
     */
    protected $container;

    public function setUp() {
        $this->container = new RouteContainer();
    }

    public function testAddRoute() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/path', 'callback', 'id');

        $this->container->addRoute($route);

        $this->assertEquals(array('id' => $route), $this->container->getRoutes());
    }

    public function testGetAndRemoveRouteById() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/path', 'callback', 'id');

        $this->container->addRoute(new Route('/path', 'callback'));
        $this->container->addRoute($route);

        $this->assertEquals(2, count($this->container->getRoutes()));
        $this->assertEquals($route, $this->container->getRouteById('id'));

        $this->container->removeRouteById('id');

        $this->assertEquals(1, count($this->container->getRoutes()));
        $this->assertNull($this->container->getRouteById('id'));
    }

    public function testGetAndRemoveRouteByPath() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/to', 'callback', 'id');

        $this->container->addRoute(new Route('/path', 'callback'));
        $this->container->addRoute($route);

        $this->assertEquals(2, count($this->container->getRoutes()));
        $this->assertEquals($route, $this->container->getRouteByPath('/to'));

        $this->container->removeRouteByPath('/to');

        $this->assertEquals(1, count($this->container->getRoutes()));
        $this->assertNull($this->container->getRouteByPath('/to'));
    }

}