<?php

declare(strict_types=1);

/**
 * Basic structure element
 */

namespace PHPDraft\Model\Elements;

use Michelf\MarkdownExtra;

abstract class BasicStructureElement implements StructureElement
{
    /**
     * Object key.
     *
     * @var ElementStructureElement|null
     */
    public $key;
    /**
     * Object JSON type.
     *
     * @var string|null
     */
    public $type;
    /**
     * Object description.
     *
     * @var string|null
     */
    public $description;
    /**
     * Type of element.
     *
     * @var string|null
     */
    public $element = null;
    /**
     * Object default.
     *
     * @var mixed
     */
    public $default = null;
    /**
     * Object value.
     *
     * @var mixed
     */
    public $value = null;
    /**
     * Object status (required|optional).
     *
     * @var string|null
     */
    public $status = '';
    /**
     * Parent structure.
     *
     * @var string|null
     */
    public $ref;
    /**
     * Is variable.
     *
     * @var boolean
     */
    public $is_variable;
    /**
     * List of object dependencies.
     *
     * @var string[]|null
     */
    public $deps;

    /**
     * Parse a JSON object to a structure.
     *
     * @param object $object       An object to parse
     * @param array  $dependencies Dependencies of this object
     *
     * @return StructureElement self reference
     */
    abstract public function parse(?object $object, array &$dependencies): StructureElement;

    /**
     * Print a string representation.
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Get a new instance of a class.
     *
     * @return self
     */
    abstract protected function new_instance(): StructureElement;

    /**
     * Parse common fields to give context.
     *
     * @param object $object       APIB object
     * @param array  $dependencies Object dependencies
     *
     * @return void
     */
    protected function parse_common(object $object, array &$dependencies): void
    {
        $this->key = null;
        if (isset($object->content->key)) {
            $key = new ElementStructureElement();
            $key->parse($object->content->key, $dependencies);
            $this->key = $key;
        }

        $this->type = $object->content->value->element
            ?? $object->meta->title->content
            ?? $object->meta->id->content
            ?? null;
        $this->description  = null;
        if (isset($object->meta->description->content)) {
            $this->description = htmlentities($object->meta->description->content);
        } elseif (isset($object->meta->description)) {
            $this->description = htmlentities($object->meta->description);
        }
        if ($this->description !== null) {
            $encoded           = htmlentities($this->description, ENT_COMPAT, null, false);
            $this->description = MarkdownExtra::defaultTransform($encoded);
        }

        $this->ref = null;
        if ($this->element === 'ref') {
            $this->ref = $object->content;
        }

        $this->is_variable = $object->attributes->variable->content ?? false;

        $this->status  = null;
        if (isset($object->attributes->typeAttributes->content)) {
            $data = array_map(function ($item) {
                return $item->content;
            }, $object->attributes->typeAttributes->content);
            $this->status = join(', ', $data);
        } elseif (isset($object->attributes->typeAttributes)) {
            $this->status = join(', ', $object->attributes->typeAttributes);
        }

        if (!in_array($this->type, self::DEFAULTS) && $this->type !== null) {
            $dependencies[] = $this->type;
        }

        if (isset($object->attributes->default)) {
            $this->default = $object->attributes->default->content->content;
        }
    }

    /**
     * Get a string representation of the value.
     *
     * @param bool $flat get a flat representation of the item.
     *
     * @return string
     */
    public function string_value($flat = false)
    {
        if (is_array($this->value) && $this->value !== []) {
            if (is_subclass_of($this->value[0], StructureElement::class) && $flat === false) {
                return $this->value[0]->__toString();
            }

            return $this->value[0];
        }

        if (is_subclass_of($this->value, BasicStructureElement::class) && $flat === true) {
            return is_array($this->value->value) ? array_keys($this->value->value)[0] : $this->value->value;
        }
        return $this->value;
    }

    /**
     * Represent the element in HTML.
     *
     * @param string $element Element name
     *
     * @return string HTML string
     */
    protected function get_element_as_html($element): string
    {
        if (in_array($element, self::DEFAULTS)) {
            return '<code>' . $element . '</code>';
        }

        $link_name = str_replace(' ', '-', strtolower($element));
        return '<a class="code" title="' . $element . '" href="#object-' . $link_name . '">' . $element . '</a>';
    }

    /**
     * Get the value representation.
     *
     * @return string
     */
    protected function get_return_value(): string
    {
        if (is_object($this->value)) {
            return $this->value->__toString();
        }
        $value = $this->value;
        if (is_bool($this->value)) {
            $value = ($this->value) ? 'true' : 'false';
        }

        $default = $this->default;
        if (is_bool($this->default)) {
            $default = ($this->default) ? 'true' : 'false';
        }

        $return_val = "<span class=\"example-value pull-right\">$value</span>";

        if (!is_null($default)) {
            $return_val = "Default: <span class='example-value pull-right'>$default</span>";
            $return_val .= is_null($value) ? '' : "<br/>Example: $return_val";
        }

        return $return_val;
    }

    /**
     * Get what element to parse with.
     *
     * @param string $element The string to parse.
     *
     * @return BasicStructureElement The element to parse to
     */
    public function get_class(string $element): BasicStructureElement
    {
        switch ($element) {
            default:
            case 'object':
                $struct = $this->new_instance();
                break;
            case 'array':
                $struct = new ArrayStructureElement();
                break;
            case 'enum':
                $struct = new EnumStructureElement();
                break;
        }

        return $struct;
    }
}
