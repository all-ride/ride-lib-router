<?php

namespace pallo\library\router;

/**
 * Data container for a set of Route objects
 */
class RouteContainer {

    /**
     * Routes in this container
     * @var array
     */
    protected $routes;

    /**
     * Constructs a new route container
     * @return null
     */
    public function __construct() {
        $this->routes = array();
    }

    /**
     * Adds a route to this container
     * @param Route $route The route to add
     * @return null
     */
    public function addRoute(Route $route) {
        $id = $route->getId();
        if ($id) {
            $this->routes[$id] = $route;
        } else {
            $id = 'i' . count($this->routes);

            $route->setId($id);

            $this->routes[$id] = $route;
        }
    }

    /**
     * Removes a route from this container
     * @param string $id The id of the route
     * @return null
     */
    public function removeRouteById($id) {
        if (isset($this->routes[$id])) {
            unset($this->routes[$id]);
        }
    }

    /**
     * Removes a route from this container
     * @param string $path The path of the route
     * @return null
     */
    public function removeRouteByPath($path) {
        foreach ($this->routes as $id => $route) {
            if ($route->getPath() == $path) {
                unset($this->routes[$id]);
            }
        }
    }

    /**
     * Gets a route by id
     * @param string $id The id of the route
     * @return Route|null
     */
    public function getRouteById($id) {
        if (!isset($this->routes[$id])) {
            return null;
        }

        return $this->routes[$id];
    }

    /**
     * Gets a route by path
     * @param string $path The path of the route
     * @return Route|null
     */
    public function getRouteByPath($path) {
        foreach ($this->routes as $id => $route) {
            if ($route->getPath() == $path) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Gets the routes from this container
     * @return array Array with the path of the route as key and an instance of
     * Route as value
     */
    public function getRoutes() {
        return $this->routes;
    }

}