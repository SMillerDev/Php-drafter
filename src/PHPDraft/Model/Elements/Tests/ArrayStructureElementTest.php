<?php

/**
 * This file contains the ArrayStructureTest.php
 *
 * @package PHPDraft\Model\Elements
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPDraft\Model\Elements\ArrayStructureElement;
use PHPDraft\Model\Elements\ElementStructureElement;

/**
 * Class ArrayStructureTest
 * @covers \PHPDraft\Model\Elements\ArrayStructureElement
 */
class ArrayStructureElementTest extends LunrBaseTest
{

    /**
     * Set up tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->class      = new ArrayStructureElement();
        $this->reflection = new \ReflectionClass('PHPDraft\Model\Elements\ArrayStructureElement');
    }

    /**
     * Parse different objects
     *
     * @dataProvider parseObjectProvider
     *
     * @param string                $object   JSON Object
     * @param ArrayStructureElement $expected Expected Object output
     *
     * @covers \PHPDraft\Model\Elements\ArrayStructureElement::parse
     */
    public function testSuccessfulParse($object, $expected)
    {
        $dep = [];
        $obj = json_decode($object);
        $res = $this->class->parse($obj, $dep);
        $this->assertEquals($expected, $res);
    }

    /**
     * Provide objects to parse including expected outcome
     *
     * @return array
     */
    public function parseObjectProvider(): array
    {
        $return             = [];
        $base1              = new ArrayStructureElement();
        $base1->key         = null;
        $val1 = new ElementStructureElement();
        $val1->value = 'Swift';
        $val1->type = 'string';
        $val2 = new ElementStructureElement();
        $val2->value = 'Objective-C';
        $val2->type = 'string';
        $base1->value       = [$val1, $val2];
        $base1->status      = null;
        $base1->element     = 'array';
        $base1->type        = null;
        $base1->is_variable = false;
        $base1->description = null;
        $base1->deps        = [];

        $base2              = new ArrayStructureElement();
        $base2->key         = null;
        $val1 = new ElementStructureElement();
        $val1->value = 'item';
        $val1->type = 'string';
        $val2 = new ElementStructureElement();
        $val2->value = 'another item';
        $val2->type = 'string';
        $base2->value       = [$val1, $val2];
        $base2->status      = null;
        $base2->element     = 'array';
        $base2->type        = 'Some simple array';
        $base2->is_variable = false;
        $base2->description = null;
        $base2->deps        = ['Some simple array'];

        $base3              = new ArrayStructureElement();
        $base3->key = new ElementStructureElement();
        $base3->key->value = 'car_id_list';
        $base3->key->type = 'string';
        $val1 = new ElementStructureElement();
        $val1->value = 'car_id_list';
        $val1->type = 'string';
        $val2 = new ElementStructureElement();
        $val2->value = null;
        $val2->type = 'array';
        $base3->value       = [$val1, $val2];
        $base3->status      = 'optional';
        $base3->element     = 'member';
        $base3->type        = 'array';
        $base3->is_variable = false;
        $base3->description = "<p>List of car identifiers to retrieve</p>\n";
        $base3->deps        = [];

        $return['generic value type'] = [
            '{
              "element": "array",
              "content": [
                {
                  "element": "string",
                  "content": "Swift"
                },
                {
                  "element": "string",
                  "content": "Objective-C"
                }
              ]
            }',
            $base1,
        ];
        $return['custom value type'] = [
            '{
              "element": "array",
              "meta": {
                "id": {
                  "element": "string",
                  "content": "Some simple array"
                }
              },
              "content": [
                {
                  "element": "string",
                  "content": "item"
                },
                {
                  "element": "string",
                  "content": "another item"
                }
              ]
            }',
            $base2,
        ];
        $return['other'] = [
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
                    "key": {
                        "element": "string",
                        "content": "car_id_list"
                    },
                    "value": {
                        "element": "array"
                    }
                }
            }',
            $base3,
        ];

        return $return;
    }

    /**
     * Test setup of new instances
     */
    public function testNewInstance(): void
    {
        $method = $this->reflection->getMethod('new_instance');
        $method->setAccessible(true);
        $return = $method->invoke($this->class);
        $this->assertInstanceOf(ArrayStructureElement::class, $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithArray(): void
    {
        $val1 = new ElementStructureElement();
        $val1->type = 'string';
        $val1->value = 'stuff';
        $val2 = new ElementStructureElement();
        $val2->type = 'int';
        $val2->value = 'class';
        $val2->description = 'Description';
        $this->class->value = [$val1, $val2];
        $return = $this->class->__toString();
        $this->assertSame('<ul class="list-group mdl-list array-list"><li class="list-group-item mdl-list__item"><code>string</code> - <span class="example-value pull-right">stuff</span></li><li class="list-group-item mdl-list__item"><a class="code" title="int" href="#object-int">int</a> - <span class="description">Description</span> - <span class="example-value pull-right">class</span></li></ul>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testToStringWithComplexArray(): void
    {
        $val1 = new ElementStructureElement();
        $val1->type = 'Bike';
        $val1->value = 'type';
        $val2 = new ElementStructureElement();
        $val2->type = 'car';
        $val2->value = 'stuff';
        $val2->description = 'Description';
        $this->class->value = [$val1, $val2];
        $return = $this->class->__toString();
        $this->assertSame('<ul class="list-group mdl-list array-list"><li class="list-group-item mdl-list__item"><a class="code" title="Bike" href="#object-bike">Bike</a> - <span class="example-value pull-right">type</span></li><li class="list-group-item mdl-list__item"><a class="code" title="car" href="#object-car">car</a> - <span class="description">Description</span> - <span class="example-value pull-right">stuff</span></li></ul>', $return);
    }
}
