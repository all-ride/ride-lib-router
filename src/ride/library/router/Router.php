<?php

namespace ride\library\router;

/**
 * A router maps a URL path to a callback
 */
interface Router {

    /**
     * Routes the request to a Route object
     * @param string $method Method of the request
     * @param string $path Path of the request
     * @param string $baseUrl Base URL of the request
     * @return RouterResult
     */
    public function route($method, $path, $baseUrl = null);

    /**
     * Gets the route container
     * @return RouteContainer A route container
     */
    public function getRouteContainer();

}