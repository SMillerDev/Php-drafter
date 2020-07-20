<?php

declare(strict_types=1);

/**
 * This file contains the Transition.
 *
 * @package PHPDraft\Model
 *
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model;

use PHPDraft\Model\Elements\BasicStructureElement;
use PHPDraft\Model\Elements\ObjectStructureElement;
use PHPDraft\Model\Elements\StructureElement;
use QL\UriTemplate\UriTemplate;
use stdClass;

class Transition extends HierarchyElement
{
    /**
     * HTTP method used.
     *
     * @var string
     */
    public $method;

    /**
     * URI.
     *
     * @var string
     */
    public $href;

    /**
     * URL variables.
     *
     * @var StructureElement[]
     */
    public $url_variables = [];

    /**
     * Data variables.
     *
     * @var StructureElement|null
     */
    public $data_variables = null;

    /**
     * The request.
     *
     * @var HTTPRequest[]
     */
    public $requests = [];

    /**
     * The responses.
     *
     * @var HTTPResponse[]
     */
    public $responses = [];

    /**
     * Structures used (if any).
     *
     * @var StructureElement[]
     */
    public $structures = [];

    /**
     * Transition constructor.
     *
     * @param \PHPDraft\Model\Resource $parent A reference to the parent object
     */
    public function __construct(\PHPDraft\Model\Resource &$parent)
    {
        $this->parent = $parent;
    }

    /**
     * Fill class values based on JSON object.
     *
     * @param stdClass $object JSON object
     *
     * @return $this self-reference
     */
    public function parse(stdClass $object): self
    {
        parent::parse($object);

        $this->href = (isset($object->attributes->href)) ? $object->attributes->href : $this->parent->href;
        $this->href = $this->href->content ?? $this->href;

        if (isset($object->attributes->hrefVariables)) {
            $deps                = [];
            foreach ($object->attributes->hrefVariables->content as $variable) {
                $struct                = (new ObjectStructureElement())->get_class($variable->element);
                $this->url_variables[] = $struct->parse($variable, $deps);
            }
        }

        if (isset($object->attributes->data)) {
            $deps                 = [];
            $struct               = (new ObjectStructureElement())->get_class($object->element);
            $this->data_variables = $struct->parse($object->attributes->data->content, $deps);
        }

        if (!is_array($object->content)) {
            return $this;
        }
        foreach ($object->content as $transition_item) {
            if (!isset($transition_item->content)) {
                continue;
            }
            $list = [];
            foreach ($transition_item->content as $item) {
                $value = null;
                if (!in_array($item->element, ['httpRequest', 'httpResponse'])) {
                    continue;
                }
                switch ($item->element) {
                    case 'httpRequest':
                        $value = new HTTPRequest($this);
                        $list  = &$this->requests;
                        break;
                    case 'httpResponse':
                        $value = new HTTPResponse($this);
                        $list  = &$this->responses;
                        break;
                    default:
                        continue 2;
                }
                $value->parse($item);

                if ($list === []) {
                    $list[] = $value;
                    continue;
                }
                $add = true;
                foreach ($list as $existing_value) {
                    if ($existing_value->is_equal_to($value)) {
                        $add = false;
                    }
                }
                if ($add) {
                    $list[] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Build a URL based on the URL variables given.
     *
     * @param string $base_url the URL to which the URL variables apply
     * @param bool   $clean    Get the URL without HTML
     *
     * @throws \QL\UriTemplate\Exception
     *
     * @return string a HTML representation of the transition URL
     */
    public function build_url(string $base_url = '', bool $clean = false): string
    {
        $url = $this->overlap_urls($this->parent->href ?? '', $this->href);
        if ($url === false) {
            $url = $this->parent->href . $this->href;
        }
        $tpl  = new UriTemplate($url);
        $vars = [];
        if ($this->parent->url_variables !== []) {
            foreach ($this->parent->url_variables as $item) {
                if (!is_subclass_of($item, BasicStructureElement::class)) {
                    continue;
                }
                $item_value              = $item->string_value(true);
                $vars[$item->key->value] = $item_value;
            }
        }
        unset($item, $item_value);
        if ($this->url_variables !== []) {
            foreach ($this->url_variables as $item) {
                if (!is_subclass_of($item, BasicStructureElement::class)) {
                    continue;
                }
                $item_value              = $item->string_value(true);
                $vars[$item->key->value] = $item_value;
            }
        }
        unset($item, $item_value);
        $url = $tpl->expand($vars);

        if ($clean) {
            return strip_tags($base_url . $url);
        }

        return $base_url . $url;
    }

    /**
     * Overlap the URLS to get one consistent URL.
     *
     * @param string $str1 First part
     * @param string $str2 Second part
     *
     * @return bool|string
     *
     * @see http://stackoverflow.com/questions/2945446/built-in-function-to-combine-overlapping-string-sequences-in-php
     */
    private function overlap_urls(string $str1, string $str2)
    {
        if ($overlap = $this->find_overlap($str1, $str2)) {
            $overlap = $overlap[count($overlap) - 1];
            $str1    = substr($str1, 0, -strlen($overlap));
            $str2    = substr($str2, strlen($overlap));

            return $str1 . $overlap . $str2;
        }

        return false;
    }

    /**
     * Find overlap in strings.
     *
     * @param string $str1 First part
     * @param string $str2 Second part
     *
     * @return array|bool
     */
    private function find_overlap(string $str1, string $str2)
    {
        $return = [];
        $sl1    = strlen($str1);
        $sl2    = strlen($str2);
        $max    = $sl1 > $sl2 ? $sl2 : $sl1;
        $i      = 1;
        while ($i <= $max) {
            $s1 = substr($str1, -$i);
            $s2 = substr($str2, 0, $i);
            if ($s1 == $s2) {
                $return[] = $s1;
            }
            $i++;
        }
        if (!empty($return)) {
            return $return;
        }

        return false;
    }

    /**
     * Get the HTTP method of the child request.
     *
     * @param int $request Request to get the method for
     *
     * @return string HTTP Method
     */
    public function get_method(int $request = 0): string
    {
        return $this->requests[$request]->method ?? 'NONE';
    }

    /**
     * Generate a cURL request to run the transition.
     *
     * @param string $base_url   base URL of the server
     * @param array  $additional additional arguments to pass
     * @param int    $key        number of the request to generate for
     *
     * @return string A cURL CLI command
     */
    public function get_curl_command(string $base_url, array $additional = [], int $key = 0): string
    {
        if (!isset($this->requests[$key])) {
            return '';
        }

        return $this->requests[$key]->get_curl_command($base_url, $additional);
    }
}
