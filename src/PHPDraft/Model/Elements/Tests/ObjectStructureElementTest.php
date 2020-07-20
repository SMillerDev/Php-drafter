<?php

/**
 * This file contains the ObjectStructureElementTest.php
 *
 * @package PHPDraft\Model
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPDraft\Model\Elements\ArrayStructureElement;
use PHPDraft\Model\Elements\ElementStructureElement;
use PHPDraft\Model\Elements\EnumStructureElement;
use PHPDraft\Model\Elements\ObjectStructureElement;
use ReflectionClass;

/**
 * Class ObjectStructureElementTest
 *
 * @covers \PHPDraft\Model\Elements\ObjectStructureElement
 */
class ObjectStructureElementTest extends LunrBaseTest
{

    /**
     * Set up tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->class      = new ObjectStructureElement();
        $this->reflection = new ReflectionClass('PHPDraft\Model\Elements\ObjectStructureElement');
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::new_instance
     */
    public function testNewInstance(): void
    {
        $method = $this->reflection->getMethod('new_instance');
        $method->setAccessible(true);
        $return = $method->invoke($this->class);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
    }

    /**
     * Parse different objects
     *
     * @dataProvider parseObjectProvider
     *
     * @param string                 $object   JSON Object
     * @param ObjectStructureElement $expected Expected Object output
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testSuccesfulParse($object, $expected)
    {
        $dep = [];
        $res = $this->class->parse(json_decode($object), $dep);
        $res->__clearForTest();
        $this->assertEquals($expected, $res);
        $this->assertSame($expected->value, $res->value);
        $this->assertSame($expected->element, $res->element);
        $this->assertSame($expected->type, $res->type);
    }

    /**
     * Provide objects to parse including expected outcome
     *
     * @return array
     */
    public function parseObjectProvider(): array
    {
        $return             = [];
        $base1              = new ObjectStructureElement();
        $base1->key         = new ElementStructureElement();
        $base1->key->type   = 'string';
        $base1->key->value  = 'name';
        $base1->value       = 'P10';
        $base1->status      = 'optional';
        $base1->element     = 'member';
        $base1->type        = 'string';
        $base1->is_variable = false;
        $base1->description = "<p>desc1</p>\n";

        $base2              = new ObjectStructureElement();
        $base2->key         = new ElementStructureElement();
        $base2->key->type   = 'string';
        $base2->key->value  = 'Auth2';
        $base2->value       = 'something';
        $base2->status      = 'required';
        $base2->element     = 'member';
        $base2->type        = 'string';
        $base2->is_variable = false;
        $base2->description = "<p>desc2</p>\n";

        $base3              = clone $base2;
        $base3->value       = 'test1 (string) | test2 (int) | test3 (Cow)';

        $base4              = clone $base2;
        $base4->value       = null;

        $return['optional status & basic element'] = [
            '{
                "element": "member",
                "meta": {
                    "description": "desc1"
                },
                "attributes": {
                    "typeAttributes": [ "optional" ]
                },
                "content": {
                    "key": {
                        "element": "string",
                        "content": "name"
                    },
                    "value": {
                        "element": "string",
                        "content": "P10"
                    }
                }
            }',
            $base1,
        ];
        $return['required status & custom key'] = [
            '{
                "element": "member",
                "meta": {
                    "description": "desc2"
                },
                "attributes": {
                    "typeAttributes": [ "required" ]
                },
                "content": {
                    "key": {
                        "element": "string",
                        "content": "Auth2"
                    },
                    "value": {
                        "element": "string",
                        "content": "something"
                    }
                }
            }',
            $base2,
        ];
        $return['sample values'] = [
            '{
                "element": "member",
                "meta": {
                    "description": "desc2"
                },
                "attributes": {
                    "typeAttributes": [ "required" ]
                },
                "content": {
                    "key": {
                        "element": "string",
                        "content": "Auth2"
                    },
                    "value": {
                        "element": "string",
                        "attributes": 
                            {"samples": {"content": [{"element": "string", "content": "test1"}, {"element": "int", "content": "test2"}, {"element": "Cow", "content": "test3"}]}}
                    }
                }
            }',
            $base3,
        ];
        $return['no value content'] = [
            '{
                "element": "member",
                "meta": {
                    "description": "desc2"
                },
                "attributes": {
                    "typeAttributes": [ "required" ]
                },
                "content": {
                    "key": {
                        "element": "string",
                        "content": "Auth2"
                    },
                    "value": {
                        "element": "string"
                    }
                }
            }',
            $base4,
        ];

        return $return;
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testEmptyParse(): void
    {
        $deps = [];
        $return = $this->class->parse(null, $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $object = new \stdClass();
        $object->key = 'key';
        $return = $this->class->parse($object, $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testArrayContentEnumContentParse(): void
    {
        $deps = [];
        $object = '{"element":"hrefVariables","content":[{"element":"member","meta":{"description":{"element":"string","content":"Info on things"},"title":{"element":"string","content":"string"}},"attributes":{"typeAttributes":{"element":"array","content":[{"element":"string","content":"required"}]}},"content":{"key":{"element":"string","content":"things"},"value":{"element":"string","content":"1"}}},{"element":"member","meta":{"description":{"element":"string","content":"The id of the Post.\nSome additional info\n"},"title":{"element":"string","content":"string"}},"attributes":{"typeAttributes":{"element":"array","content":[{"element":"string","content":"optional"}]}},"content":{"key":{"element":"string","content":"post_id"},"value":{"element":"string","attributes":{"default":{"element":"string","content":"0"}},"content":"1"}}},{"element":"member","meta":{"description":{"element":"string","content":"Some stuff info\nSome additional info\n"},"title":{"element":"string","content":"string"}},"attributes":{"typeAttributes":{"element":"array","content":[{"element":"string","content":"optional"}]}},"content":{"key":{"element":"string","content":"stuff"},"value":{"element":"enum","attributes":{"default":{"element":"enum","content":{"element":"string","content":"world"}},"enumerations":{"element":"array","content":[{"element":"string","content":"hello"},{"element":"string","content":"world"},{"element":"string","content":"tests"}]}},"content":{"element":"string","content":"hello"}}}}]}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        foreach ($return->value as $item) {
            $this->assertInstanceOf(ObjectStructureElement::class, $item);
        }
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testArrayContentObjectContentParse(): void
    {
        $deps = [];
        $object = '{"element":"object","content": [{"hello":"world"}]}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        foreach ($return->value as $item) {
            $this->assertInstanceOf(ObjectStructureElement::class, $item);
        }
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testValueStructureEnumContentParse(): void
    {
        $deps = [];
        $object = '{"element":"enum","content": {"key":{"element": "string", "content":"key"},"value":{"element":"enum"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(EnumStructureElement::class, $value);
        }

    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testValueStructureArrayContentParse(): void
    {
        $deps = [];
        $object = '{"element":"array","content": {"element":"array","value": {"element": "array"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(ArrayStructureElement::class, $value);
        }
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testValueStructureObjectContentParse(): void
    {
        $deps = [];
        $object = '{"element":"object","content": {"key":{"element": "string", "content":"key"},"value":{"element":"object"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(ObjectStructureElement::class, $value);
        }
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testValueStructureObjectContentParseContent(): void
    {
        $this->markTestSkipped('failing');
        $deps = [];
        $object = '{
              "element": "dataStructure",
              "content": {
                "element": "Person",
                "meta": {
                  "id": {
                    "element": "string",
                    "content": "User"
                  }
                },
                "content": [
                  {
                    "element": "member",
                    "content": {
                      "key": {
                        "element": "string",
                        "content": "attributes"
                      },
                      "value": {
                        "element": "Attributes"
                      }
                    }
                  }
                ]
              }
            }';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertInstanceOf(ObjectStructureElement::class, $return->value);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringBasic(): void
    {
        $return = $this->class->__toString();
        $this->assertSame('<span class="example-value pull-right">{  }</span>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringArray(): void
    {
        $this->class->value = ['hello'];
        $return = $this->class->__toString();
        $this->assertSame('<table class="table table-striped mdl-data-table mdl-js-data-table ">hello</table>', $return);

        $val = new ArrayStructureElement();
        $val->element = 'things';
        $val->value = 'stuff';
        $this->class->value = [$val];
        $return = $this->class->__toString();
        $this->assertSame('<table class="table table-striped mdl-data-table mdl-js-data-table "><tr><td></td><td><a class="code" title="things" href="#object-things">things</a></td><td></td></tr></table>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringNullValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->type = 'mixed';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><a class="code" title="mixed" href="#object-mixed">mixed</a></td><td> <span class="status"></span></td><td></td><td></td></tr>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringObjectValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = new ObjectStructureElement();
        $this->class->type = 'object';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><code>object</code></td><td> <span class="status"></span></td><td></td><td><div class="sub-struct"><span class="example-value pull-right">{  }</span></div></td></tr>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringArrayValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = new ArrayStructureElement();
        $this->class->value->element = 'value';
        $this->class->value->value = 'value';
        $this->class->type = 'array';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><code>array</code></td><td> <span class="status"></span></td><td></td><td><div class="array-struct"><tr><td></td><td><a class="code" title="value" href="#object-value">value</a></td><td></td></tr></div></td></tr>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringEnumValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = new EnumStructureElement();
        $this->class->value->element = 'value';
        $this->class->value->value = 'value';
        $this->class->value->key = new ElementStructureElement();
        $this->class->value->key->type = 'string';
        $this->class->value->key->value = 'key';
        $this->class->value->type = 'enum';
        $return = $this->class->__toString();
        $this->assertSame('<div class="enum-struct"><tr><td>key</td><td><a class="code" title="value" href="#object-value">value</a></td><td></td></tr></div>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringBoolValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = true;
        $this->class->type = 'boolean';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><code>boolean</code></td><td> <span class="status"></span></td><td></td><td><span class="example-value pull-right">true</span></td></tr>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringOtherValue(): void
    {
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = 'world';
        $this->class->type = 'Cow';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><a class="code" title="Cow" href="#object-cow">Cow</a></td><td> <span class="status"></span></td><td></td><td><span class="example-value pull-right">world</span></td></tr>', $return);
    }

    /**
     * Test the setup of new instances
     *
     * @covers \PHPDraft\Model\Elements\ObjectStructureElement::__toString
     */
    public function testToStringOtherValueTypeKnown(): void
    {
        $this->class->type = 'string';
        $this->class->key = new ElementStructureElement();
        $this->class->key->value = 'hello';
        $this->class->value = 'world';
        $return = $this->class->__toString();
        $this->assertSame('<tr><td><span>hello</span></td><td><code>string</code></td><td> <span class="status"></span></td><td></td><td><span class="example-value pull-right">world</span></td></tr>', $return);
    }
}
