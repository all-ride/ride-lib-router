<?php

namespace ride\library\router;

/**
 * Modifiable URL
 */
class Url {

    /**
     * Constructs a new URL
     * @param string $baseUrl
     * @param string $path
     * @param array $pathParameters
     * @param array $queryParameters
     * @return null
     */
    public function __construct($baseUrl, $path = '/', array $pathParameters = null, array $queryParameters = null) {
        $this->setBaseUrl($baseUrl);
        $this->setPath($path);
        $this->baseUrl = $baseUrl;
        $this->path = $path;
        $this->pathParameters = $pathParameters;
        $this->queryParameters = $queryParameters;
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
     * Sets a path parameter
     * @param string $name Name of the parameter
     * @param string $value Value of the parameter
     * @return null
    public function setPathParameter($name, $value) {
        $this->pathParameters[$name] = $value;
    }

    /**
     * Gets a path parameter
     * @param string $name Name of the parameter
     * @return mixed
    public function getPathParameter($name) {
        if (!isset($this->pathParameters[$name])) {
            return null;
        }

        return $this->pathParameters[$name];
    }

    /**
     * Gets the path parameters
     * @return array|null
     */
    public function getPathParameters() {
        return $this->pathParameters;
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
            if ($token != '%' . $name . '%') {
                $path .= '/' . $token;

                continue;
            }

            if (isset($this->pathParameters[$name])) {
                if (!is_scalar($this->pathParameters[$name])) {
                    throw new RouterException('Could not parse path ' . $this->path . ': argument ' . $name . ' is not a scalar value');
                }

                $path .= '/' . urlencode($this->pathParameters[$name]);
            } else {
                throw new RouterException('Could not parse path ' . $this->path . ': argument ' . $name . ' is not set');
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

        return '?' . $this->parseQueryArray($queryParameters, $querySeparator);
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
