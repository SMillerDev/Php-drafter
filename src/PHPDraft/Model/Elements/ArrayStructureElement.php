<?php

declare(strict_types=1);

/**
 * This file contains the ArrayStructureElement.php.
 *
 * @package PHPDraft\Model\Elements
 *
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements;

/**
 * Class ArrayStructureElement.
 */
class ArrayStructureElement extends BasicStructureElement
{
    /**
     * Parse an array object.
     *
     * @param object|null $object       APIB Item to parse
     * @param array       $dependencies List of dependencies build
     *
     * @return self Self reference
     */
    public function parse(?object $object, array &$dependencies): StructureElement
    {
        $this->element = $object->element ?? 'array';

        $this->parse_common($object, $dependencies);

        if (!isset($object->content)) {
            $this->value = [];

            return $this;
        }

        foreach ($object->content as $sub_item) {
            $element = new ElementStructureElement();
            $element->parse($sub_item, $dependencies);
            $this->value[] = $element;
        }

        $this->deps = $dependencies;

        return $this;
    }

    /**
     * Provide HTML representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (is_string($this->value)) {
            $type = $this->get_element_as_html($this->element);

            return '<tr><td>' . $this->key . '</td><td>' . $type . '</td><td>' . $this->description . '</td></tr>';
        }

        $return = '';
        foreach ($this->value as $item) {
            $return .= $item->__toString();
        }

        return '<ul class="list-group mdl-list array-list">' . $return . '</ul>';
    }

    /**
     * Get a new instance of a class.
     *
     * @return self
     */
    protected function new_instance(): StructureElement
    {
        return new self();
    }
}
