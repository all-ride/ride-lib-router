<?php

namespace ride\library\router;

/**
 * Alias for a route or path
 */
class Alias {

    /**
     * Actual path
     * @var string
     */
    protected $path;

    /**
     * Aliased path
     * @var string
     */
    protected $alias;

    /**
     * Flag to see if the alias should be forced
     * @var boolean
     */
    protected $isForced;

    /**
     * Source of this alias
     * @var string
     */
    protected $source;

    /**
     * Constructs a new alias
     * @param string $path
     * @patam string $alias
     * @return null
     */
    public function __construct($path, $alias) {
        $this->setPath($path);
        $this->setAlias($alias);
    }

    /**
     * Sets the path for the alias
     * @param string $path
     * @return null
     */
    public function setPath($path) {
        if (empty($path)) {
            throw new RouterException('Could not set path of this route alias: provided path is empty');
        }

        $this->path = $path;
    }

    /**
     * Gets the path to alias
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Sets the alias for the path
     * @param string $alias
     * @return null
     */
    public function setAlias($alias) {
        if (empty($alias)) {
            throw new RouterException('Could not set alias of this route alias: provided alias is empty');
        }

        $this->alias = $alias;
    }

    /**
     * Gets the alias for the path
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * Sets whether this path should be forced to the alias
     * @param boolean $isForced
     * @return null
     */
    public function setIsForced($isForced) {
        $this->isForced = $isForced;
    }

    /**
     * Checks if this path should be forced to the alias
     * @return boolean
     */
    public function isForced() {
        return $this->isForced;
    }

    /**
     * Sets the source for this alias
     * @param string $source Source of this route, for internal use
     * @return null
     */
    public function setSource($source) {
        $this->source = $source;
    }

    /**
     * Gets the source of this alias
     * @return string
     */
    public function getSource() {
        return $this->source;
    }

}
