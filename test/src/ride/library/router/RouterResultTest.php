<?php

namespace ride\library\router;

use PHPUnit\Framework\TestCase;

class RouterResultTest extends TestCase {

    /**
     * @var ride\library\router\RouterResult
     */
    protected $result;

    protected function setUp() {
        $this->result = new RouterResult();
    }

    public function testResult() {
        $this->assertTrue($this->result->isEmpty());
        $this->assertNull($this->result->getRoute());
        $this->assertNull($this->result->getAllowedMethods());

        $this->result->setAllowedMethods(array('POST'));

        $this->assertEquals(array('POST'), $this->result->getAllowedMethods());
        $this->assertFalse($this->result->isEmpty());

        $this->result->setAllowedMethods(null);

        $this->assertNull($this->result->getAllowedMethods());
        $this->assertTrue($this->result->isEmpty());

        $route = new Route('/', 'function');

        $this->result->setRoute($route);

        $this->assertEquals($route, $this->result->getRoute());
        $this->assertFalse($this->result->isEmpty());
    }

}