<?php

namespace pallo\library\router;

/**
 * Data container for the result of a route action
 */
class RouterResult {

    /**
     * Matched route
     * @var Route
     */
    protected $route;

    /**
     * Allowed methods for the path
     * @var array
     */
    protected $allowedMethods;

    /**
     * Constructs a new router result
     * @return null
     */
    public function __construct() {
        $this->route = null;
        $this->allowedMethods = null;
    }

    /**
     * Checks if this result is empty
     * @return boolean
     */
    public function isEmpty() {
        return $this->route === null && $this->allowedMethods === null;
    }

    /**
     * Sets a route to this result
     * @param Route $route
     * @return null
     */
    public function setRoute(Route $route) {
        $this->route = $route;
    }

    /**
     * Gets the route of this result
     * @return Route|null
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * Sets the allowed methods to the result
     * @param array $allowedMethods
     * @return null
     */
    public function setAllowedMethods(array $allowedMethods = null) {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Gets the allowed methods for the route
     * @return array|null
     */
    public function getAllowedMethods() {
        return $this->allowedMethods;
    }

}