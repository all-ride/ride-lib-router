<?php

namespace ride\library\router;

use ride\library\router\exception\RouterException;

/**
 * Container for routes and aliases
 */
class RouteContainer {

    /**
     * Routes in this container
     * @var array
     */
    protected $routes;

    /**
     * Aliases in this container
     * @var array
     */
    protected $aliases;

    /**
     * Source for the routes and aliases
     * @var string
     */
    protected $source;

    /**
     * Constructs a new route container
     * @return null
     */
    public function __construct($source = null) {
        $this->setSource($source);

        $this->routes = array();
        $this->aliases = array();
    }

    /**
     * Sets the source for this route
     * @param string $source Source of this route, for internal use
     * @return null
     */
    public function setSource($source) {
        $this->source = $source;
    }

    /**
     * Gets the source of this route
     * @return string
     */
    public function getSource() {
        return $this->source;
    }

    /**
     * Sets all the routes and aliases of the provided container to this
     * container
     * @param RouteContainer $container
     * @return null
     */
    public function setRouteContainer(RouteContainer $container) {
        $routes = $container->getRoutes();
        foreach ($routes as $route) {
            $this->setRoute($route);
        }

        $aliases = $container->getAliases();
        foreach ($aliases as $alias) {
            $this->setAlias($alias);
        }
    }

    /**
     * Creates a new route
     * @param string $path Path of the route
     * @param string|array $callback Callback to the action of this route
     * @param string $id Id of this route
     * @param string|array|null $allowedMethods Allowed methods for this route
     * @return \ride\library\router\Route
     */
    public function createRoute($path, $callback, $id = null, $methods = null) {
        $route = new Route($path, $callback, $id, $methods);
        $route->setSource($this->source);

        return $route;
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

    public function addRoute(Route $route) {
        $this->setRoute($route);
    }

    /**
     * Sets a route to this container
     * @param Route $route Route to add
     * @return null
     */
    public function setRoute(Route $route) {
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
     * @param Route $route Instance of the route
     * @return null
     */
    public function unsetRoute(Route $route) {
        if (isset($this->routes[$route->getId()])) {
            unset($this->routes[$route->getId()]);
        }
    }

    /**
     * Creates an alias instance
     * @param string $path
     * @param string $alias
     * @param boolean $isForced
     * @return Alias
     */
    public function createAlias($path, $alias, $isForced = false) {
        $alias = new Alias($path, $alias);
        $alias->setIsForced($isForced);
        $alias->setSource($this->source);

        return $alias;
    }

    /**
     * Gets an alias from this container
     * @param string $path Path of the alias
     * @return RouteAlias|null
     */
    public function getAliasByPath($path) {
        if (isset($this->aliases['path'][$path])) {
            return $this->aliases['path'][$path];
        }

        return null;
    }

    /**
     * Gets an alias from this container
     * @param string $path Actual alias of the alias
     * @return RouteAlias|null
     */
    public function getAliasByAlias($alias) {
        if (isset($this->aliases['alias'][$alias])) {
            return $this->aliases['alias'][$alias];
        }

        return null;
    }

    /**
     * Gets all the aliases from this container
     * @return array
     */
    public function getAliases() {
        if (!isset($this->aliases['path'])) {
            return array();
        }

        return $this->aliases['path'];
    }

    /**
     * Sets an alias to this container
     * @param Alias $alias Alias to add
     * @return null
     */
    public function setAlias(Alias $alias) {
        $this->aliases['path'][$alias->getPath()] = $alias;
        $this->aliases['alias'][$alias->getAlias()] = $alias;
    }

    /**
     * Removes an alias from this container
     * @param Alias $alias Instance of the alias
     * @return null
     */
    public function unsetAlias(Alias $alias) {
        if (isset($this->aliases['path'][$alias->getPath()])) {
            unset($this->aliases['path'][$alias->getPath()]);
        }
        if (isset($this->aliases['path'][$alias->getAlias()])) {
            unset($this->aliases['alias'][$alias->getAlias()]);
        }
    }

    /**
     * Gets the full URL for a route
     * @param string $baseUrl Base URL of the system
     * @param string $id Id of the route
     * @param array $arguments Array with the argument name as key and the
     * argument as value. The argument should be a scalar value which will be
     * url encoded
     * @param array $queryParameters Array with the query parameter name as key
     * and the parameter as value.
     * @return Url Instance of the URL
     */
    public function getUrl($baseUrl, $id, array $arguments = null, array $queryParameters = null, $querySeparator = '&') {
        $route = $this->getRouteById($id);
        if (!$route) {
            throw new RouterException('Could not get the URL for route ' . $id . ': no route found for the provided id');
        }

        return $route->getUrl($baseUrl, $arguments, $queryParameters);
    }

    /**
     * Gets the alias for the provided URL
     * @param Url $url Instance of the URL
     * @return string
     */
    public function getUrlAlias(Url $url) {
        $baseUrl = $url->getBaseUrl();

        $path = str_replace($baseUrl, '', $url->getUrl());

        if (isset($this->aliases['path'][$path]) && $this->aliases['path'][$path]->isForced()) {
            $path = $this->aliases['path'][$path]->getAlias();
        }

        return $baseUrl . $path;
    }

}
