<?php

namespace ride\library\router;

use \PHPUnit_Framework_TestCase;

class RouteContainerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var RouteContainer
     */
    protected $container;

    public function setUp() {
        $this->container = new RouteContainer();
    }

    public function testSetRoute() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/path', 'callback', 'id');

        $this->container->setRoute($route);

        $this->assertEquals(array('id' => $route), $this->container->getRoutes());
    }

    public function testSetRouteContainer() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/path', 'callback', 'id');
        $route2 = new Route('/path2', 'callback2', 'id2');

        $this->container->setRoute($route);
        $this->container->setRoute($route2);

        $container = new RouteContainer();
        $container->setRouteContainer($this->container);

        $this->assertEquals(array('id' => $route, 'id2' => $route2), $container->getRoutes());
    }

    public function testSetAndUnsetRoute() {
        $this->assertEmpty($this->container->getRoutes());

        $route = new Route('/path', 'callback', 'id');

        $this->container->setRoute(new Route('/path', 'callback'));
        $this->container->setRoute($route);

        $this->assertEquals(2, count($this->container->getRoutes()));
        $this->assertEquals($route, $this->container->getRouteById('id'));

        $this->container->unsetRoute($route);

        $this->assertEquals(1, count($this->container->getRoutes()));
        $this->assertNull($this->container->getRouteById('id'));
    }

    public function testSetAndUnsetAlias() {
        $this->assertEmpty($this->container->getAliases());

        $alias = new Alias('/path', '/alias');

        $this->container->setAlias(new Alias('/path2', '/alias2'));
        $this->container->setAlias($alias);

        $this->assertEquals(2, count($this->container->getAliases()));
        $this->assertEquals($alias, $this->container->getAliasByPath('/path'));

        $this->container->unsetAlias($alias);

        $this->assertEquals(1, count($this->container->getAliases()));
        $this->assertNull($this->container->getAliasByPath('/path'));
    }

}
