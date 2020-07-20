<?php

declare(strict_types=1);

/**
 * This file contains the ObjectStructureElement.php.
 *
 * @package PHPDraft\Model\Elements
 *
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements;

/**
 * Class ObjectStructureElement.
 */
class ObjectStructureElement extends BasicStructureElement
{

    private $object;

    /**
     * Unset object function.
     * @internal Only for tests
     */
    public function __clearForTest()
    {
        $this->object = null;
    }

    /**
     * Parse a JSON object to a data structure.
     *
     * @param object|null $object       An object to parse
     * @param array      $dependencies Dependencies of this object
     *
     * @return ObjectStructureElement self reference
     */
    public function parse(?object $object, array &$dependencies): StructureElement
    {
        $this->object = $object;
        if (is_null($object) || !isset($object->element) || !(isset($object->content) || isset($object->meta) )) {
            return $this;
        }

        $this->element = $object->element;
        $this->parse_common($object, $dependencies);

        if (isset($object->content->value->attributes->default)) {
            $default = $object->content->value->attributes->default->content;
            $this->default = $default->content ?? $default;
        }

        if (isset($object->content) && is_array($object->content)) {
            $this->parse_array_content($object, $dependencies);
            return $this;
        }

        if (in_array($this->type, ['object', 'array', 'enum'], true) || !in_array($this->type, self::DEFAULTS, true)) {
            $this->parse_value_structure($object, $dependencies);

            return $this;
        }

        if (isset($object->content->value->content)) {
            $this->value = $object->content->value->content;
        } elseif (isset($object->content->value->attributes->samples)) {
            $this->value = array_reduce($object->content->value->attributes->samples->content, function ($carry, $item) {
                if ($carry === null) {
                    return "$item->content ($item->element)";
                }
                return "$carry | $item->content ($item->element)";
            });
        } else {
            $this->value = null;
        }

        return $this;
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
     * Parse $this->value as a structure based on given content.
     *
     * @param object $object       APIB content
     * @param array  $dependencies Object dependencies
     *
     * @return void
     */
    protected function parse_value_structure(object $object, array &$dependencies)
    {
        if (isset($object->content->content) || in_array($this->element, ['boolean', 'string', 'number', 'ref'])) {
            return;
        }

        $value  = $object->content->value ?? $object;
        $type   = in_array($this->element, ['member']) ? $this->type : $this->element;
        $struct = $this->get_class($type);

        $this->value = [$struct->parse($value, $dependencies)];

        unset($struct);
        unset($value);
    }


    /**
     * Parse content formed as an array.
     *
     * @param object $object       APIB content
     * @param array  $dependencies Object dependencies
     *
     * @return void
     */
    protected function parse_array_content(object $object, array &$dependencies): void
    {
        foreach ($object->content as $value) {
            $type   = $this->element === 'member' ? $this->type : $this->element;
            $struct = $this->get_class($type);

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
        $options = array_merge(self::DEFAULTS, ['member', 'select', 'option', 'ref', 'T', 'hrefVariables']);
        if (!is_null($this->element) && !in_array($this->element, $options)) {
            $this->description = '<p>Inherits from <a href="#object-' . strtolower($this->element) . '">' . $this->element . '</a></p>' . $this->description;
        }

        if (is_array($this->value)) {
            $return = '';
            foreach ($this->value as $object) {
                if (is_string($object) || is_subclass_of(get_class($object), StructureElement::class)) {
                    $return .= $object;
                }
            }

            return "<table class=\"table table-striped mdl-data-table mdl-js-data-table \">$return</table>";
        }

        if ($this->ref !== null) {
            return '<p>Inherits from <a href="#object-' . strtolower($this->ref) . '">' . $this->ref . '</a></p>';
        }

        if (is_null($this->value) && is_null($this->default) && is_null($this->key) && $this->description !== null) {
            return '';
        }

        if (is_null($this->value) && is_null($this->default) && is_null($this->key) && is_null($this->description)) {
            return '{  }';
        }

        if (is_null($this->value) && is_null($this->default)) {
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

        return $this->construct_string_return($this->get_return_value());
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
        if ($this->type === null) {
            return $value;
        }

        $type     = $this->get_element_as_html($this->type);
        $variable = '';
        if ($this->is_variable) {
            $link_name = str_replace(' ', '-', strtolower($this->key->type));
            $variable = '<a class="variable-key" title="' . $this->key->type . '" href="#object-' . $link_name . '"><span class="fas fa-info variable-info" data-toggle="tooltip" data-placement="top" title="This is a variable key of type &quot;' . $this->key->type . '&quot;"></span></a>';
        }

        return '<tr>' .
            '<td>' . '<span>' . $this->key->value . '</span>' . $variable . '</td>' .
            '<td>' . $type . '</td>' .
            '<td> <span class="status">' . $this->status . '</span></td>' .
            '<td>' . $this->description . '</td>' .
            '<td>' . $this->get_return_value() . '</td>' .
            '</tr>';
    }
}
