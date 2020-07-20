<?php

/**
 * This file contains the TransitionTest.php
 *
 * @package PHPDraft\Model
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Tests;

use PHPDraft\Model\Transition;
use ReflectionClass;

/**
 * Class TransitionTest
 * @covers \PHPDraft\Model\Transition
 */
class TransitionTest extends HierarchyElementChildTest
{

    /**
     * Set up
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->parent     = $this->createMock('\PHPDraft\Model\Resource');
        $this->class      = new Transition($this->parent);
        $this->reflection = new ReflectionClass('PHPDraft\Model\Transition');
    }

    /**
     * Tear down
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->reflection);
        unset($this->parent);
    }

    /**
     * Test if the value the class is initialized with is correct
     */
    public function testSetupCorrectly(): void
    {
        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalled(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"attributes":{"href":"something"}, "content":[]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIssetHrefVariables(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $json = '{"attributes":{"href":"something", "hrefVariables":{"content": [{"element": "member", "key": "hello", "value":"world"}]}}, "content":[]}';
        $obj = json_decode($json);
        $this->class->parse($obj);

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIssetData(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsNotArrayContent(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":""}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentNoChild(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":{}}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentWrongChild(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":[{"element":"test"}]}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentRequest(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":[{"element":"httpRequest", "attributes":{"method":"TEST"}, "content":{}}]}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentResponse(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":[{"element":"httpResponse", "content":[], "attributes":{"statusCode":"1000", "headers":{"content":[]}}}]}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentDefault(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":[{"element":"Cow", "content":[], "attributes":{"statusCode":"1000", "headers":{"content":[]}}}]}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic parse functions
     */
    public function testParseIsCalledIsArrayContentRequestList(): void
    {
        $this->set_reflection_property_value('parent', $this->parent);

        $mock_req = $this->getMockBuilder('\PHPDraft\Model\HTTPRequest')
                         ->disableOriginalConstructor()
                         ->getMock();
        $mock_req->expects($this->once())
                 ->method('is_equal_to')
                 ->will($this->returnValue(true));
        $requests = [$mock_req];

        $this->set_reflection_property_value('requests', $requests);

        $obj = '{"element": "dataStructure", "attributes":{"href":"something", "data":{"content":{"hello":"world"}}}, "content":[{"element":"123", "content":[{"element":"httpRequest", "attributes":{"method":"TEST"}}]}]}';

        $this->class->parse(json_decode($obj));

        $this->assertSame($this->parent, $this->get_reflection_property_value('parent'));
        $this->assertSame('something', $this->get_reflection_property_value('href'));
    }

    /**
     * Test basic get_curl_command functions
     */
    public function testGetCurlCommandNoKey(): void
    {
        $return = $this->class->get_curl_command('https://ur.l');

        $this->assertSame('', $return);
    }

    /**
     * Test basic get_curl_command functions
     */
    public function testGetCurlCommandKey(): void
    {
        $mock_req = $this->getMockBuilder('\PHPDraft\Model\HTTPRequest')
                         ->disableOriginalConstructor()
                         ->getMock();
        $mock_req->expects($this->once())
                 ->method('get_curl_command')
                 ->with('https://ur.l', [])
                 ->will($this->returnValue('curl_command'));

        $this->set_reflection_property_value('requests', [$mock_req]);

        $return = $this->class->get_curl_command('https://ur.l');

        $this->assertSame('curl_command', $return);
    }

    /**
     * Test basic get_method functions
     */
    public function testGetMethodNotSet(): void
    {
        $return = $this->class->get_method();

        $this->assertSame('NONE', $return);
    }

    /**
     * Test basic get_method functions
     */
    public function testGetMethodSet(): void
    {
        $mock_req = $this->getMockBuilder('\PHPDraft\Model\HTTPRequest')
                         ->disableOriginalConstructor()
                         ->getMock();

        $mock_req->method = 'TEST';

        $this->set_reflection_property_value('requests', [$mock_req]);

        $return = $this->class->get_method();

        $this->assertSame('TEST', $return);
    }

    /**
     * Test basic build_url functions
     */
    public function testBuildURLBase(): void
    {
        $parent = $this->getMockBuilder('\PHPDraft\Model\Resource')
                       ->disableOriginalConstructor()
                       ->getMock();

        $parent->href = '/base';

        $this->set_reflection_property_value('parent', $parent);
        $this->set_reflection_property_value('href', '/url');

        $return = $this->class->build_url();

        $this->assertSame('/base/url', $return);
    }

    /**
     * Test basic build_url functions
     */
    public function testBuildURLOverlap(): void
    {
        $parent = $this->getMockBuilder('\PHPDraft\Model\Resource')
                       ->disableOriginalConstructor()
                       ->getMock();

        $parent->href = '/url';

        $this->set_reflection_property_value('parent', $this->parent);
        $this->set_reflection_property_value('href', '/url/level');

        $return = $this->class->build_url();

        $this->assertSame('/url/level', $return);
    }

    /**
     * Test basic build_url functions
     */
    public function testBuildURLClean(): void
    {
        $parent = $this->getMockBuilder('\PHPDraft\Model\Resource')
                       ->disableOriginalConstructor()
                       ->getMock();

        $parent->href = '/url';

        $this->set_reflection_property_value('parent', $this->parent);
        $this->set_reflection_property_value('href', '/url/level');

        $this->mock_function('strip_tags', function () {
            return "STRIPPED";
        });

        $return = $this->class->build_url('', true);

        $this->unmock_function('strip_tags');

        $this->assertSame('STRIPPED', $return);
    }

    /**
     * Test basic build_url functions
     */
    public function testBuildURLVars(): void
    {
        $parent = $this->getMockBuilder('\PHPDraft\Model\Resource')
                       ->disableOriginalConstructor()
                       ->getMock();

        $parent->href = '/url';

        $this->set_reflection_property_value('parent', $this->parent);

        $var1 = $this->getMockBuilder('\PHPDraft\Model\Elements\ObjectStructureElement')
                     ->disableOriginalConstructor()
                     ->getMock();

        $key1 = $this->getMockBuilder('\PHPDraft\Model\Elements\ElementStructureElement')
                     ->disableOriginalConstructor()
                     ->getMock();

        $key1->value = 'KEY';

        $var1->expects($this->once())
             ->method('string_value')
             ->will($this->returnValue('STRING'));
        $var1->key = $key1;

        $this->set_reflection_property_value('url_variables', [$var1]);
        $this->set_reflection_property_value('href', '/url/level');

        $return = $this->class->build_url();

        $this->assertSame('/url/level', $return);
    }
}
