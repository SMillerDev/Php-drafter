<?php

/**
 * This file contains the EnumStructureElementTest.php
 *
 * @package PHPDraft\Model\Elements
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPDraft\Model\Elements\ElementStructureElement;
use PHPDraft\Model\Elements\EnumStructureElement;

/**
 * Class EnumStructureElementTest
 * @covers \PHPDraft\Model\Elements\EnumStructureElement
 */
class EnumStructureElementTest extends LunrBaseTest
{
    /**
     * Set up tests
     * @return void
     */
    public function setUp(): void
    {
        $this->class      = new EnumStructureElement();
        $this->reflection = new \ReflectionClass('PHPDraft\Model\Elements\EnumStructureElement');
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
     * Test setup of new instances
     */
    public function testNewInstance(): void
    {
        $method = $this->reflection->getMethod('new_instance');
        $method->setAccessible(true);
        $return = $method->invoke($this->class);
        $this->assertInstanceOf(EnumStructureElement::class, $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithArray(): void
    {
        $value1 = new ElementStructureElement();
        $value1->value = 'hello';
        $value1->type  = 'string';
        $value2 = new ElementStructureElement();
        $value2->value = 'test';
        $value2->type  = 'int';
        $this->class->value = [$value1, $value2];
        $return = $this->class->__toString();
        $this->assertSame('<ul class="list-group mdl-list"><li class="list-group-item mdl-list__item"><code>string</code> - <span class="example-value pull-right">hello</span></li><li class="list-group-item mdl-list__item"><a class="code" title="int" href="#object-int">int</a> - <span class="example-value pull-right">test</span></li></ul>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithString(): void
    {
        $this->class->value = 'hello';
        $this->class->key = new ElementStructureElement();
        $this->class->key->type = 'string';
        $this->class->key->value = 'key';
        $this->class->element = 'string';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td>key</td><td><code>string</code></td><td></td></tr>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithStringComplex(): void
    {
        $this->class->value = 'hello';
        $this->class->key = new ElementStructureElement();
        $this->class->key->type = 'string';
        $this->class->key->value = 'key';
        $this->class->element = 'Car';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td>key</td><td><a class="code" title="Car" href="#object-car">Car</a></td><td></td></tr>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithComplexArray(): void
    {
        $value1 = new ElementStructureElement();
        $value1->value = 'hello';
        $value1->type  = 'bike';
        $value2 = new ElementStructureElement();
        $value2->value = 'test';
        $value2->type  = 'Car';
        $this->class->value = [$value1, $value2];
        $return = $this->class->__toString();
        $this->assertSame('<ul class="list-group mdl-list"><li class="list-group-item mdl-list__item"><a class="code" title="bike" href="#object-bike">bike</a> - <span class="example-value pull-right">hello</span></li><li class="list-group-item mdl-list__item"><a class="code" title="Car" href="#object-car">Car</a> - <span class="example-value pull-right">test</span></li></ul>', $return);
    }

    /**
     * Parse different objects
     *
     * @dataProvider parseObjectProvider
     *
     * @param string               $object   JSON Object
     * @param EnumStructureElement $expected Expected Object output
     *
     * @covers \PHPDraft\Model\Elements\EnumStructureElement::parse
     */
    public function testSuccesfulParse($object, $expected)
    {
        $dep = [];
        $res = $this->class->parse(json_decode($object), $dep);
        $this->assertEquals($expected, $res);
    }

    /**
     * Provide objects to parse including expected outcome
     *
     * @return array
     */
    public function parseObjectProvider(): array
    {
        $value1 = new ElementStructureElement();
        $value1->value = 'item';
        $value1->type  = 'string';
        $value2 = new ElementStructureElement();
        $value2->value = 'another item';
        $value2->type  = 'string';

        $return             = [];
        $base1              = new EnumStructureElement();
        $base1->key         = null;
        $base1->value       = [ $value1, $value2 ];
        $base1->status      = null;
        $base1->element     = 'enum';
        $base1->type        = 'Some simple enum';
        $base1->is_variable = false;
        $base1->description = null;
        $base1->deps        = ['Some simple enum'];

        $base2              = new EnumStructureElement();
        $base2->key         = new ElementStructureElement();
        $base2->key->type   = 'string';
        $base2->key->value  = 'car_id_list';
        $base2->value       = 'car_id_list';
        $base2->default     = 'world';
        $base2->status      = null;
        $base2->element     = 'enum';
        $base2->type        = 'string';
        $base2->description = null;
        $base2->is_variable = false;
        $base2->deps        = [];

        $base3              = new EnumStructureElement();
        $base3->key         = new ElementStructureElement();
        $base3->key->type   = 'number';
        $base3->key->value  = '5';
        $base3->value       = '5';
        $base3->status      = 'optional';
        $base3->element     = 'member';
        $base3->type        = 'number';
        $base3->description = "<p>List of car identifiers to retrieve</p>\n";
        $base3->is_variable = false;
        $base3->deps        = [];

        $return['base enum'] = [
            '{
                "element":"enum",
                "meta":{
                    "id":{
                        "element":"string",
                        "content":"Some simple enum"
                    }
                },
                "attributes":{
                    "enumerations":{
                        "element":"array",
                        "content":[
                            {
                                "element":"string",
                                "attributes":{
                                    "typeAttributes":{
                                        "element":"array",
                                        "content":[
                                            {
                                                "element":"string",
                                                "content":"fixed"
                                            }
                                        ]
                                    }
                                },
                                "content":"item"
                            },
                            {
                                "element":"string",
                                "attributes":{
                                    "typeAttributes":{
                                        "element":"array","content":[
                                            {
                                                "element":"string",
                                                "content":"fixed"
                                            }
                                        ]
                                    }
                                },
                                "content":"another item"
                            }
                        ]
                    }
                }
            }',
            $base1,
        ];
        $return['enum with default'] = [
            '{
              "element": "enum",
              "attributes": {
                "default": {
                  "element": "enum",
                  "content": {
                    "element": "string",
                    "content": "world"
                  }
                },
                "enumerations": {
                  "element": "array",
                  "content": [
                    {
                      "element": "string",
                      "content": "hello"
                    },
                    {
                      "element": "string",
                      "content": "world"
                    },
                    {
                      "element": "string",
                      "content": "tests"
                    }
                  ]
                }
              },
              "content": {
                "element": "string",
                "content": "car_id_list"
              }
            }',
            $base2,
        ];
        $return['basic enum'] = [
            '{
                "element": "member",
                "meta": {
                    "description": "List of car identifiers to retrieve"
                },
                "attributes": {
                    "typeAttributes": [
                        "optional"
                    ]
                },
                "content": {
                    "element": "number",
                    "content": "5"
                }
            }',
            $base3,
        ];

        return $return;
    }
}
