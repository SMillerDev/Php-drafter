<?php

/**
 * This file contains the BasicStructureElementTest.php
 *
 * @package PHPDraft\Model\Elements
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPDraft\Model\Elements\ArrayStructureElement;
use PHPDraft\Model\Elements\BasicStructureElement;
use PHPDraft\Model\Elements\ElementStructureElement;
use PHPDraft\Model\Elements\ObjectStructureElement;
use ReflectionClass;

/**
 * Class BasicStructureElementTest
 * @covers \PHPDraft\Model\Elements\BasicStructureElement
 */
class BasicStructureElementTest extends LunrBaseTest
{

    /**
     * Set up tests
     *
     * @return void
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        $this->class      = $this->getMockBuilder('\PHPDraft\Model\Elements\BasicStructureElement')
                                 ->disableOriginalConstructor()
                                 ->getMockForAbstractClass();
        $this->reflection = new ReflectionClass($this->class);
    }

    /**
     * Test if the value the class is initialized with is correct
     */
    public function testSetupCorrectly(): void
    {
        $property = $this->reflection->getProperty('element');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->class));
    }

    /**
     * Test if the value the class is initialized with is correct
     *
     * @dataProvider stringValueProvider
     *
     * @param mixed  $value        Value to set to the class
     * @param string $string_value Expected string representation
     */
    public function testStringValue($value, $string_value)
    {
        $this->set_reflection_property_value('value', $value);
        $this->set_reflection_property_value('element', $value);

        $return = $this->class->string_value();

        $this->assertSame($string_value, $return);
    }

    public function stringValueProvider(): array
    {
        $return = [];

        $return['string'] = ['hello', 'hello'];
        $return['int'] = [1, 1];
        $return['bool'] = [true, true];
        $return['array'] = [[1], 1];

        $obj        = new ArrayStructureElement();
        $obj->value = 'hello';
        $return[]   = [[$obj], 'hello'];

        return $return;
    }

    /**
     * Test if the value the class is initialized with is correct
     */
    public function testParseCommonDeps(): void
    {
        $dep = [];

        $json = '{"meta":{},"attributes":{},"content":{"key":{"element": "string", "content":"key"}, "value":{"element":"cat"}}}';

        $answer = new ObjectStructureElement();
        $answer->key = new ElementStructureElement();
        $answer->key->type = 'string';
        $answer->key->value = 'key';
        $answer->type = 'cat';
        $answer->description = null;

        $method = $this->reflection->getMethod('parse_common');
        $method->setAccessible(true);
        $method->invokeArgs($this->class, [json_decode($json), &$dep]);

        $this->assertEquals($answer->key, $this->class->key);
        $this->assertEquals($answer->type, $this->class->type);
        $this->assertEquals($answer->description, $this->class->description);
        $this->assertEquals($answer->status, $this->class->status);
        $this->assertEquals(['cat'], $dep);
    }

    /**
     * Test if the value the class is initialized with is correct
     *
     * @dataProvider parseValueProvider
     *
     * @param mixed                 $value          Value to set to the class
     * @param BasicStructureElement $expected_value Expected string representation
     */
    public function testParseCommon($value, $expected_value)
    {
        $dep = [];
        $method = $this->reflection->getMethod('parse_common');
        $method->setAccessible(true);
        $method->invokeArgs($this->class, [$value, &$dep]);

        $this->assertEquals($expected_value->key, $this->class->key);
        $this->assertEquals($expected_value->type, $this->class->type);
        $this->assertEquals($expected_value->description, $this->class->description);
        $this->assertEquals($expected_value->status, $this->class->status);
        $this->assertEquals([], $dep);
    }

    public function parseValueProvider(): array
    {
        $return = [];

        $json = '{"meta":{},"attributes":{},"content":{"key":{"element": "string", "content":"key"}, "value":{"element":"string"}}}';
        $obj = json_decode($json);

        $answer = new ObjectStructureElement();
        $answer->key = new ElementStructureElement();
        $answer->key->type = 'string';
        $answer->key->value = 'key';
        $answer->type = 'string';
        $answer->description = PHP_EOL;

        $return[] = [$obj, $answer];

        $obj2 = clone $obj;
        $obj2->attributes->typeAttributes = [1, 2];
        $answer->status = '1, 2';

        $return[] = [$obj2, $answer];

        $obj3 = clone $obj;
        $obj3->meta->description = '__hello__';
        $answer->description = '<p><strong>hello</strong></p>' . PHP_EOL;

        $return[] = [$obj3, $answer];

        return $return;
    }
}
