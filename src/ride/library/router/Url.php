<?php

namespace ride\library\router;

use ride\library\router\exception\RouterException;

/**
 * Modifiable URL
 */
class Url {

    /**
     * Base URL of the system
     * @var string
     */
    private $baseUrl;

    /**
     * Path for the system
     * @var string
     */
    private $path;

    /**
     * Array with the arguments for the path
     * @var array
     */
    private $arguments;

    /**
     * Array with the query parameters
     * @array
     */
    private $queryParameters;

    /**
     * Constructs a new URL
     * @param string $baseUrl
     * @param string $path
     * @param array $arguments
     * @param array $queryParameters
     * @return null
     */
    public function __construct($baseUrl, $path = null, array $arguments = null, array $queryParameters = null) {
        $this->setBaseUrl($baseUrl);
        $this->setPath($path);
        $this->setArguments($arguments);
        $this->setQueryParameters($queryParameters);
    }

    /**
     * Gets a string representation of this URL
     * @return string
     */
    public function __toString() {
        return $this->baseUrl . $this->parsePath() . $this->parseQuery();
    }

    /**
     * Sets the base URL
     * @param string $baseUrl
     * @return null
     */
    protected function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Gets the base URL
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * Sets the path of this URL
     * @param string $path Path of the URL
     * @return null
     */
    protected function setPath($path) {
        $this->path = '/' . trim($path, '/');
    }

    /**
     * Gets the path of this URL
     * @return string
     */
    public function getPath() {
        return $path;
    }

    /**
     * Sets path parameters
     * @param array $parameters Array with key-value pairs to set as path
     * parameters
     * @return null
     */
    protected function setArguments(array $arguments = null) {
        if ($arguments === null) {
            $this->arguments = array();

            return;
        }

        foreach ($arguments as $name => $value) {
            $this->setArgument($name, $value);
        }
    }

    /**
     * Sets a path parameter
     * @param string $name Name of the parameter
     * @param string $value Value of the parameter
     * @return null
     */
    public function setArgument($name, $value) {
        $this->arguments[$name] = $value;
    }

    /**
     * Gets a path parameter
     * @param string $name Name of the parameter
     * @return mixed
     */
    public function getArgument($name) {
        if (!isset($this->arguments[$name])) {
            return null;
        }

        return $this->arguments[$name];
    }

    /**
     * Gets the path parameters
     * @return array|null
     */
    public function getArguments() {
        return $this->arguments;
    }


    /**
     * Sets query parameters
     * @param array $queryParameters Array with key-value pairs to set as query
     * parameters
     * @return null
     */
    protected function setQueryParameters(array $queryParameters = null) {
        if ($queryParameters === null) {
            $this->queryParameters = array();

            return;
        }

        foreach ($queryParameters as $name => $value) {
            $this->setQueryParameter($name, $value);
        }
    }

    /**
     * Sets a query parameter
     * @param string name Name of the parameter
     * @param mixed $value Value of the parameter
     * @return null
     */
    public function setQueryParameter($name, $value) {
        if ($value !== null) {
            $this->queryParameters[$name] = $value;
        } elseif (isset($this->queryParameters[$name])) {
            unset($this->queryParameters[$name]);
        }
    }

    /**
     * Gets a query parameter
     * @param string $name Name of the parameter
     * @return mixed Value of the parameter or null if not set
     */
    public function getQueryParameter($name) {
        if (!isset($this->queryParameters[$name])) {
            return null;
        }

        return $this->queryParameters[$name];
    }

    /**
     * Gets all the query parameters of this URL
     * @return array|null
     */
    public function getQueryParameters() {
        return $this->queryParameters;
    }

    /**
     * Parses the arguments in the path
     * @param array $arguments
     * @return string
     */
    protected function parsePath() {
        $path = '';

        $tokens = $this->getPathTokens();
        foreach ($tokens as $index => $token) {
            $name = substr($token, 1, -1);
            if ($token != '%' . $name . '%' || !isset($this->arguments[$name])) {
                $path .= '/' . $token;
            } else {
                if (!is_scalar($this->arguments[$name])) {
                    throw new RouterException('Could not parse path ' . $this->path . ': argument ' . $name . ' is not a scalar value');
                }

                $path .= '/' . urlencode($this->arguments[$name]);
            }
        }

        return $path;
    }

    /**
     * Tokenizes a path
     * @param string $path
     * @return array Array with the tokens of the path
     */
    protected function getPathTokens() {
        if ($this->path === '/') {
            return array();
        }

        // trim first /
        $path = substr($this->path, 1);

        return explode('/', $path);
    }

    /**
     * Parses the query for a URL
     * @param array $queryParameters Array with the query parameters
     * @param string $querySeparator String used to separate the parameters
     * @return string
     */
    protected function parseQuery($querySeparator = '&') {
        if (!$this->queryParameters) {
            return '';
        }

        ksort($this->queryParameters);

        return '?' . $this->parseQueryArray($this->queryParameters, $querySeparator);
    }

    /**
     * Parses a query parameter array value
     * @param array $queryParameters Array value
     * @param string $querySeparator String used to separate the parameters
     * @param string $prefix Prefix for the names of the parameters
     * @return string
     */
    protected function parseQueryArray(array $queryParameters, $querySeparator, $prefix = null) {
        $result = array();

        foreach ($queryParameters as $name => $value) {
            if ($prefix) {
                $key = $prefix . '[' . $name . ']';
            } else {
                $key = $name;
            }

            if (is_array($value)) {
                $result[] = $this->parseQueryArray($value, $querySeparator, $key);
            } else {
                $result[] = $key . '=' . urlencode($value);
            }
        }

        return implode($querySeparator, $result);
    }

}
