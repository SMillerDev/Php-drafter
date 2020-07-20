<?php

/**
 * This file contains the RequestBodyElementTest.php
 *
 * @package PHPDraft\Model\Elements
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPDraft\Model\Elements\ArrayStructureElement;
use PHPDraft\Model\Elements\ElementStructureElement;
use PHPDraft\Model\Elements\EnumStructureElement;
use PHPDraft\Model\Elements\ObjectStructureElement;
use PHPDraft\Model\Elements\RequestBodyElement;

/**
 * Class RequestBodyElementTest
 * @covers \PHPDraft\Model\Elements\RequestBodyElement
 */
class RequestBodyElementTest extends LunrBaseTest
{

    /**
     * Set up tests
     * @return void
     */
    public function setUp(): void
    {
        $this->class      = new RequestBodyElement();
        $this->reflection = new \ReflectionClass('PHPDraft\Model\Elements\RequestBodyElement');
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
        $this->assertInstanceOf(RequestBodyElement::class, $return);
    }

    /**
     * Test setup of new instances
     */
    public function testPrintBasic(): void
    {
        $this->class->key = 'key';
        $return = $this->class->print_request();
        $this->assertSame('key=<span>?</span>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testPrintBasicArray(): void
    {
        $this->class->key = 'key';
        $this->class->value = 'value';
        $c1 = clone $this->class;
        $c2 = clone $this->class;
        $this->class->value = [ $c1, $c2 ];
        $return = $this->class->print_request();
        $this->assertSame('<code class="request-body">key=<span>value</span>&key=<span>value</span></code>', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testPrintJson(): void
    {
        $this->class->key = 'key';
        $return = $this->class->print_request('application/json');
        $this->assertSame('{"key":"?"}', $return);
    }

    /**
     * Test setup of new instances
     */
    public function testPrintJsonArray(): void
    {
        $this->class->key = 'key';
        $this->class->value = 'value';
        $c1 = clone $this->class;
        $c2 = clone $this->class;
        $this->class->value = [ $c1, $c2 ];
        $return = $this->class->print_request('application/json');
        $this->assertSame("<code class=\"request-body\">{\"key\":\"value\"}\n{\"key\":\"value\"}</code>", $return);
    }

    /**
     * Parse different objects
     *
     * @dataProvider parseObjectProvider
     *
     * @param string                 $object   JSON Object
     * @param ObjectStructureElement $expected Expected Object output
     *
     * @covers       \PHPDraft\Model\Elements\ObjectStructureElement::parse
     */
    public function testSuccessfulParse($object, $expected)
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
        $base1              = new RequestBodyElement();
        $base1->key         = new ElementStructureElement();
        $base1->key->type   = 'string';
        $base1->key->value  = 'name';
        $base1->value       = 'P10';
        $base1->status      = 'optional';
        $base1->element     = 'member';
        $base1->type        = 'string';
        $base1->is_variable = false;
        $base1->description = "<p>desc1</p>\n";

        $base2              = new RequestBodyElement();
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
        $base3->value       = 'test1 (string) | test2 (string) | test3 (string)';

        $base4              = clone $base2;
        $base4->value       = null;

        $return[] = [
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
        $return[] = [
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
        $return[] = [
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
                            {"samples": {"content": [{"element": "string", "content": "test1"}, {"element": "string", "content": "test2"}, {"element": "string", "content": "test3"}]}}
                    }
                }
            }',
            $base3,
        ];
        $return[] = [
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
     */
    public function testArrayContentEnumContentParse(): void
    {
        $deps = [];
        $object = '{"element":"enum","content": [{"element":"enum"}]}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        foreach ($return->value as $item) {
            $this->assertInstanceOf(EnumStructureElement::class, $item);
        }
    }

    /**
     * Test the setup of new instances
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
     */
    public function testValueStructureEnumContentParse(): void
    {
        $deps = [];
        $object = '{"element":"enum","content": {"element":"enum","key":{"element":"string","content":"key"},"value":{"element":"enum"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(EnumStructureElement::class, $value);
        }
    }

    /**
     * Test the setup of new instances
     */
    public function testValueStructureArrayContentParse(): void
    {
        $deps = [];
        $object = '{"element":"array","content": {"element":"array","key":{"element":"string","content":"key"},"value":{"element":"array"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(ArrayStructureElement::class, $value);
        }
    }

    /**
     * Test the setup of new instances
     */
    public function testValueStructureObjectContentParse(): void
    {
        $deps = [];
        $object = '{"element":"object","content": {"element":"object","key":{"element":"string","content":"key"},"value":{"element":"object"}}}';

        $return = $this->class->parse(json_decode($object), $deps);
        $this->assertInstanceOf(ObjectStructureElement::class, $return);
        $this->assertIsArray($return->value);
        foreach ($return->value as $value) {
            $this->assertInstanceOf(ObjectStructureElement::class, $value);
        }
    }
}
