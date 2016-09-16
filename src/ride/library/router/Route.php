<?php

namespace ride\library\router;

use ride\library\router\exception\RouterException;

/**
 * Data container for the definition of a route
 */
class Route {

    /**
     * Base URL to the system
     * @var string
     */
    protected $baseUrl;

    /**
     * URL path to the controller
     * @var string
     */
    protected $path;

    /**
     * Flag to see if this route has dynamic arguments
     * @var boolean
     */
    protected $isDynamic;

    /**
     * Callback for this route
     * @var string|array
     */
    protected $callback;

    /**
     * Path arguments for the action method
     * @var array|null
     */
    protected $arguments;

    /**
     * Predefined arguments for the callback
     * @var array|null
     */
    protected $predefinedArguments;

    /**
     * Allowed HTTP methods for this route
     * @var array|null
     */
    protected $allowedMethods;

    /**
     * Id of this route
     * @var string
     */
    protected $id;

    /**
     * Locale code for this route
     * @var string
     */
    protected $locale;

    /**
     * Permissions needed to access this route
     * @var array
     */
    protected $permissions;

    /**
     * Source of this route
     * @var string
     */
    protected $source;

    /**
     * Constructs a new route
     * @param string $path URL path to the controller
     * @param string|array $callback Callback to the action of this route
     * @param string $id Id of this route
     * @param string|array|null $allowedMethods Allowed methods for this route
     * @return null
     */
    public function __construct($path, $callback, $id = null, $allowedMethods = null) {
        $this->setPath($path);
        $this->setCallback($callback);
        $this->setId($id);
        $this->setAllowedMethods($allowedMethods);
        $this->setIsDynamic(false);
    }

    /**
     * Gets a string representation of this route
     * @return string
     */
    public function __toString() {
        $string = $this->path . ' ';

        if (is_array($this->callback) && count($this->callback) == 2 && isset($this->callback[0])) {
            if (is_object($this->callback[0])) {
                $string .= get_class($this->callback[0]) . '->';
            } else {
                $string .= $this->callback[0] . '::';
            }

            $string .= $this->callback[1];
        } else {
            $string .= (string) $this->callback;
        }

        $arguments = array();
        if ($this->predefinedArguments) {
            foreach ($this->predefinedArguments as $name => $value) {
                $arguments[$name] = var_export($value, true);
            }
        }
        if ($this->arguments) {
            foreach ($this->arguments as $name => $value) {
                $arguments[$name] = var_export($value, true);
            }
        }

        if ($arguments) {
            $string .= '(' . implode(', ', $arguments) . ')';
        } else {
            $string .= '()';
        }

        $string .= ' ' . ($this->isDynamic ? 'd' : 's');

        if ($this->allowedMethods) {
            $string .= '[' . implode('|', array_keys($this->allowedMethods)) . ']';
        } else {
            $string .= '[*]';
        }

        return $string;
    }

    /**
     * Sets the URL path
     * @param string $path
     * @return null
     * @throws \ride\library\router\exception\RouterException when the path is empty or invalid
     */
    protected function setPath($path) {
        if (!is_string($path) || $path === '') {
            throw new RouterException('Could not set the path of the route : path is empty or not a string.');
        }

        $regexHttpSegment = '(([a-zA-Z0-9]|[$+_.-]|%|[!*\'(),])|(%[0-9A-Fa-f][0-9A-Fa-f])|[;:@&=])*';
        $regexHttpPath = '/^' . $regexHttpSegment . '(\\/' . $regexHttpSegment . ')*$/';

        if (!preg_match($regexHttpPath, $path)) {
            throw new RouterException('Could not set the path of the route: ' . $path . ' is not a valid HTTP path');
        }

        if ($path !== '/') {
            if (substr($path, 0, 1) !== '/') {
                $path = '/' . $path;
            }

            if (substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }
        }

        $this->path = $path;
    }

    /**
     * Gets the URL path
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Tokenizes a path
     * @param string $path
     * @return array Array with the tokens of the path
     */
    public function getPathTokens() {
        if ($this->path === '/') {
            return array();
        }

        // trim first /
        $path = substr($this->path, 1);

        return explode('/', $path);
    }

    /**
     * Gets the full URL for this route
     * @param string $baseUrl Base URL of the system
     * @param array $arguments Array with the argument name as key and the
     * argument as value. The argument should be a scalar value which will be
     * url encoded
     * @param array $queryParameters Array with the query parameters
     * @return Url Instance of the URL
     */
    public function getUrl($baseUrl, array $arguments = null, array $queryParameters = null) {
        if ($this->baseUrl) {
            $baseUrl = $this->baseUrl;
        }

        return new Url($baseUrl, $this->path, $arguments, $queryParameters);
    }

    /**
     * Sets the dynamic parameters flag
     * @param boolean $isDynamic
     * @return null
     */
    public function setIsDynamic($isDynamic) {
        $this->isDynamic = $isDynamic;
    }

    /**
     * Gets the dynamic parameters flag
     * @return boolean
     */
    public function isDynamic() {
        return $this->isDynamic;
    }

    /**
     * Sets the callback of this route
     * @param string $callback string|array Callback to the action of the route
     * @return null
     */
    public function setCallback($callback) {
        $this->callback = $callback;
    }

    /**
     * Gets the callback for this route
     * @return string|array
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Sets the id of this route
     * @param string $id The id of this route
     * @throws \ride\library\router\exception\RouterException
     */
    public function setId($id = null) {
        if ($id !== null && (!is_string($id) || $id == '')) {
            throw new RouterException('Could not set the id of route ' . $this->path . ': id is empty or not a string');
        }

        $this->id = $id;
    }

    /**
     * Gets the id of this route
     * @return string|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the allowed methods for this route
     * @param null|string|array $allowedMethods The allowed methods of this route
     * @return null
     * @throws \ride\library\router\exception\RouterException
     */
    public function setAllowedMethods($allowedMethods = null) {
        if (empty($allowedMethods)) {
            $this->allowedMethods = null;

            return;
        }

        if (!is_array($allowedMethods)) {
            $allowedMethods = array($allowedMethods);
        }

        $this->allowedMethods = array();

        foreach ($allowedMethods as $index => $allowedMethod) {
            if (!is_string($allowedMethod) || $allowedMethod == '') {
                throw new RouterException('Could not set the allowed methods of route ' . $this->path . ': invalid method provided');
            }

            $this->allowedMethods[strtoupper(trim($allowedMethod))] = true;
        }

        ksort($this->allowedMethods);
    }

    /**
     * Gets the allowed methods of this route
     * @return array|null
     */
    public function getAllowedMethods() {
        return $this->allowedMethods;
    }

    /**
     * Checks if the provided method is allowed
     * @param string $method The request method
     * @return boolean
     */
    public function isMethodAllowed($method) {
        if ($this->allowedMethods === null) {
            return true;
        }

        return isset($this->allowedMethods[strtoupper($method)]);
    }

    /**
     * Sets the arguments for the callback
     * @param array $arguments
     * @return null
     */
    public function setArguments(array $arguments = null) {
        $this->arguments = $arguments;
    }

    /**
     * Gets the arguments for the action
     * @return array Arguments for the callback
     */
    public function getArguments() {
        if ($this->arguments === null) {
            return array();
        }

        return $this->arguments;
    }

    /**
     * Gets an argument for the action
     * @param string $name Name of the argument
     * @param mixed $default Default value to return when the argument is not
     * set
     * @return mixed Argument value if set, provided default value otherwise
     */
    public function getArgument($name, $default = null) {
        if (!isset($this->arguments[$name])) {
            return $default;
        }

        return $this->arguments[$name];
    }

    /**
     * Sets the predefined arguments for the callback
     * @param array $arguments
     * @return null
     */
    public function setPredefinedArguments(array $arguments = null) {
        $this->predefinedArguments = $arguments;
    }

    /**
     * Gets the predefined arguments for the action method
     * @return array Arguments for the callback
     */
    public function getPredefinedArguments() {
        if ($this->predefinedArguments === null) {
            return array();
        }

        return $this->predefinedArguments;
    }

    /**
     * Sets the base URL for this route
     * @param string|null $baseUrl URL pointing to the system
     * @return null
     */
    public function setBaseUrl($baseUrl = null) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Gets the base URL of this route
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * Sets the permissions for this route
     * @param string|array $permissions Code of a permission or permissions
     * @return null
     */
    public function setPermissions($permissions = null) {
        if ($permissions !== null) {
            if (!is_array($permissions)) {
                $permissions = array($permissions);
            }

            foreach ($permissions as $permission) {
                if (!is_string($permission)) {
                    throw new RouterException('Could not set the permissions of this route: permission code should be an array');
                }
            }
        }

        $this->permissions = $permissions;
    }

    /**
     * Gets the permissions of this route
     * @return array Array with permission codes
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * Sets the locale for this route
     * @param string|null $locale Locale code of the current locale, null for
     * automatic selection
     * @return null
     */
    public function setLocale($locale = null) {
        $this->locale = $locale;
    }

    /**
     * Gets the locale of this route
     * @return string
     */
    public function getLocale() {
        return $this->locale;
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

}
