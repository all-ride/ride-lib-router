<?php

namespace pallo\library\router;

use pallo\library\router\exception\RouterException;

/**
 * Abstract router implementation
 */
abstract class AbstractRouter implements Router {

    /**
     * A route container with the defined routes
     * @var RouteContainer
     */
    protected $routeContainer;

    /**
     * Default callback
     * @var null|string|array
     */
    protected $defaultCallback;

    /**
     * Construct a new router
     * @param RouteContainer $routerContainer
     * @return null
     */
    public function __construct(RouteContainer $routeContainer) {
    	$this->setRouteContainer($routeContainer);
        $this->defaultCallback = null;
    }

    /**
     * Sets the route container
     * @param RouteContainer $routeContainer
     * @return null
     */
    public function setRouteContainer(RouteContainer $routeContainer) {
    	$this->routeContainer = $routeContainer;
    }

    /**
     * Gets the route container
     * @return RouteContainer
     */
    public function getRouteContainer() {
        return $this->routeContainer;
    }

    /**
     * Sets the default action of this router
     * @param null|string|array $defaultCallback Callback to the default action
     * @return null
     */
    public function setDefaultCallback($defaultCallback) {
        $this->defaultCallback = $defaultCallback;
    }

    /**
     * Gets the default default action of this router
     * @return null|string|array Callback of the default action
     */
    public function getDefaultCallback() {
    	return $this->defaultCallback;
    }

    /**
     * Routes the request path to a Route object
     * @param string $method Method of the request
     * @param string $path Path of the request
     * @param string $baseUrl Base URL of the request
     * @return RouterResult
     */
    public function route($method, $path, $baseUrl = null) {
        $path = $this->processPath($path);

        $result = $this->getRouteFromPath($method, $path, $baseUrl);
        if (!$result->isEmpty()) {
        	return $result;
        }

        if ($this->defaultCallback && $path == '/') {
            $route = new Route($path, $this->defaultCallback);

            $result->setRoute($route);
        }

        return $result;
    }

    /**
     * Gets a route from the route definitions for the requested path
     * @param string $method Method of the request
     * @param string $path Path of the request without trailing / and query
     * string
     * @param string $baseUrl Base URL of the request
     * @return RouterResult
     */
    abstract protected function getRouteFromPath($method, $path, $baseUrl = null);

    /**
     * Removes the query arguments from the provided path
     * @param string $path Path of the request
     * @return string Path without the query arguments
     */
    protected function processPath($path) {
    	// remove query string
        $positionQuestion = strpos($path, '?');
        if ($positionQuestion !== false) {
            $path = substr($path, 0, $positionQuestion);
        }

    	// remove trailing slash
        if ($path != '/') {
        	if (substr($path, -1, 1) != '/') {
            	$path = rtrim($path, '/');
        	}
        }

        return $path;
    }

}