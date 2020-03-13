<?php

/**
 * This file contains the ObjectStructureElement.php.
 *
 * @package PHPDraft\Model\Elements
 *
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements;

use stdClass;

/**
 * Class ObjectStructureElement.
 */
class ObjectStructureElement extends BasicStructureElement
{

    /**
     * Object key is variable.
     *
     * @var boolean
     */
    public $variable_key = false;

    /**
     * Parse a JSON object to a data structure.
     *
     * @param mixed|null $object       An object to parse
     * @param array      $dependencies Dependencies of this object
     *
     * @return ObjectStructureElement self reference
     */
    public function parse($object, array &$dependencies): StructureElement
    {
        if (empty($object) || !isset($object->element) || !(isset($object->content) || isset($object->meta) )) {
            return $this;
        }

        if (!isset($object->content) && isset($object->meta)) {
            $this->element = $object->element;
            $this->parse_common($object, $dependencies);
            return $this;
        }

        $this->element = $object->element;

        if (isset($object->content) && is_array($object->content)) {
            $this->parse_array_content($object, $dependencies);

            return $this;
        }

        $this->parse_common($object, $dependencies);

        if (in_array($this->type, ['object', 'array', 'enum'], true) || !in_array($this->type, self::DEFAULTS, true)) {
            $this->parse_value_structure($object, $dependencies);

            return $this;
        }

        if (isset($object->content->value->content)) {
            $this->value = $object->content->value->content;
        } elseif (isset($object->content->value->attributes->samples)) {
            $this->value = join(' | ', $object->content->value->attributes->samples);
        } else {
            $this->value = null;
        }

        return $this;
    }

    /**
     * Parse $this->value as a structure based on given content.
     *
     * @param mixed $object       APIB content
     * @param array $dependencies Object dependencies
     *
     * @return void
     */
    protected function parse_value_structure($object, array &$dependencies)
    {
        switch ($this->type) {
            case 'array':
                $struct      = new ArrayStructureElement();
                $this->value = $struct->parse($object, $dependencies);
                break;
            case 'enum':
                $struct      = new EnumStructureElement();
                $this->value = $struct->parse($object, $dependencies);
                break;
            case 'object':
            default:
                $value  = $object->content->value->content ?? null;
                $struct = $this->new_instance();

                $this->value = $struct->parse($value, $dependencies);
                break;
        }

        unset($struct);
        unset($value);
    }

    /**
     * Get a new instance of a class.
     *
     * @return ObjectStructureElement
     */
    protected function new_instance(): StructureElement
    {
        return new self();
    }

    /**
     * Parse content formed as an array.
     *
     * @param mixed $object       APIB content
     * @param array $dependencies Object dependencies
     *
     * @return void
     */
    protected function parse_array_content($object, array &$dependencies): void
    {
        foreach ($object->content as $value) {
            if ($this->element === 'enum') {
                $struct = new EnumStructureElement();
            } else {
                $struct = $this->new_instance();
            }

            $this->value[] = $struct->parse($value, $dependencies);
            unset($struct);
        }

        unset($value);
    }

    /**
     * Print a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->value === null && $this->key === null) {
            return '<span class="example-value pull-right">{  }</span>';
        }

        if (is_array($this->value)) {
            $return = '<table class="table table-striped mdl-data-table mdl-js-data-table ">';
            foreach ($this->value as $object) {
                if (is_string($object) || is_subclass_of(get_class($object), BasicStructureElement::class)) {
                    $return .= $object;
                }
            }

            $return .= '</table>';

            return $return;
        }

        if (is_null($this->value)) {
            return $this->construct_string_return('');
        }

        if (is_object($this->value) && (self::class === get_class($this->value) || RequestBodyElement::class === get_class($this->value))) {
            return $this->construct_string_return('<div class="sub-struct">' . $this->value . '</div>');
        }

        if (is_object($this->value) && (ArrayStructureElement::class === get_class($this->value))) {
            return $this->construct_string_return('<div class="array-struct">' . $this->value . '</div>');
        }

        if (is_object($this->value) && (EnumStructureElement::class === get_class($this->value))) {
            return $this->construct_string_return('<div class="enum-struct">' . $this->value . '</div>');
        }

        $value = '<span class="example-value pull-right">';
        if (is_bool($this->value)) {
            $value .= ($this->value) ? 'true' : 'false';
        } else {
            $value .= $this->value;
        }

        $value .= '</span>';

        return $this->construct_string_return($value);
    }

    /**
     * Create an HTML return.
     *
     * @param string $value value to display
     *
     * @return string
     */
    protected function construct_string_return(string $value): string
    {
        if (!in_array($this->type, self::DEFAULTS)) {
            $type = '<a class="code" href="#object-' . str_replace(
                ' ',
                '-',
                strtolower($this->type)
            ) . '">' . $this->type . '</a>';
        } else {
            $type = '<code>' . $this->type . '</code>';
        }

        $key_info = '';
        if ($this->variable_key === true) {
            $key_info = '<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="right" title="This is a variable property">info</button>';
        }

        $return =
            '<tr>' .
            '<td>' . '<span>' . $this->key . '</span>' . $key_info . '</td>' .
            '<td>' . $type . '</td>' .
            '<td> <span class="status">' . $this->status . '</span></td>' .
            '<td>' . $this->description . '</td>' .
            '<td>' . $value . '</td>' .
            '</tr>';

        return $return;
    }
}
