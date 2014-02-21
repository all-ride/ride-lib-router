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
        $this->setArguments(null);
        $this->setPredefinedArguments(null);
        $this->setLocale(null);
        $this->setBaseUrl(null);
    }

    /**
     * Gets a string representation of this route
     * @return string
     */
    public function __toString() {
        $string = $this->path . ' ';

        if (is_array($this->callback) && count($this->callback) == 2 && isset($this->callback[0])) {
            $string .= $this->callback[0] . (is_string($this->callback[0]) ? '::' : '->') . $this->callback[1];
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
     * @throws ride\ZiboException when the path is empty or invalid
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

        $this->path = '/' . trim($path, '/');
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

        $path = $this->path;

        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }

        return explode('/', $path);
    }

    /**
     * Gets the full URL for this route
     * @param string $baseUrl Base URL of the system
     * @param array $arguments Array with the argument name as key and the
     * argument as value. The argument should be a scalar value which will be
     * url encoded
     * @return string Generated URL
     */
    public function getUrl($baseUrl, array $arguments = null) {
        if ($this->baseUrl) {
            $baseUrl = $this->baseUrl;
        }

        $path = $baseUrl;

        $tokens = $this->getPathTokens();
        foreach ($tokens as $index => $token) {
            $argumentName = substr($token, 1, -1);
            $isArgument = $token == '%' . $argumentName . '%';

            if ($isArgument) {
                if (isset($arguments[$argumentName])) {
                    if (!is_scalar($arguments[$argumentName])) {
                        throw new RouterException('Could not get the URL of route ' . $this->path . ': argument ' . $argumentName . ' is not a scalar value');
                    }

                    $path .= '/' . urlencode($arguments[$argumentName]);
                } else {
                    throw new RouterException('Could not get the URL of route ' . $this->path . ': argument ' . $argumentName . ' is not set');
                }
            } else {
                $path .= '/' . $token;
            }
        }

        return $path;
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
     * @throws ride\library\router\exception\RouterException
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
     * @throws ride\library\router\exception\RouterException
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

}