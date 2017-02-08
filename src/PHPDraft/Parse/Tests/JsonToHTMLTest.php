<?php
/**
 * This file contains the JsonToHTMLTest.php
 *
 * @package PHPDraft\Parse
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Parse\Tests;

use PHPDraft\Core\BaseTest;
use PHPDraft\Parse\JsonToHTML;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class JsonToHTMLTest
 * @covers PHPDraft\Parse\JsonToHTML
 */
class JsonToHTMLTest extends BaseTest
{
    /**
     * Test Class
     * @var JsonToHTML
     */
    protected $class;

    /**
     * Test reflection
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->class      = new JsonToHTML(json_decode(file_get_contents(TEST_STATICS . '/drafter/json')));
        $this->reflection = new ReflectionClass('PHPDraft\Parse\JsonToHTML');
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        unset($this->class);
        unset($this->reflection);
    }

    /**
     * Tests if the constructor sets the property correctly
     */
    public function testSetupCorrectly()
    {
        $property = $this->reflection->getProperty('object');
        $property->setAccessible(TRUE);
        $this->assertEquals(json_decode(file_get_contents(TEST_STATICS . '/drafter/json')), $property->getValue($this->class));
    }

}
