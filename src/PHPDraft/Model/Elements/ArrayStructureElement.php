<?php
/**
 * Created by PhpStorm.
 * User: smillernl
 * Date: 2-9-16
 * Time: 15:21
 */

namespace PHPDraft\Model\Elements;

use PHPDraft\Model\StructureElement;

class ArrayStructureElement extends DataStructureElement implements StructureElement
{

    /**
     * Type of objects in the array
     *
     * @var
     */
    public $type_of;

    public function parse($item, &$dependencies)
    {
        $this->element = (isset($item->element)) ? $item->element : 'array';
        $this->value   = (isset($item->content)) ? $item->content : null;

        if (isset($item->content)) {
            foreach ($item->content as $key => $sub_item) {
                $this->type[$key] = $sub_item->element;
                switch ($sub_item->element) {
                    case 'array':
                        $value             = new ArrayStructureElement();
                        $this->value[$key] = $value->parse($sub_item, $dependencies);
                        break;
                    case 'object':
                        $value             = new DataStructureElement();
                        $this->value[$key] = $value->parse($sub_item, $dependencies);
                        break;
                    case 'enum':
                        $value             = new EnumStructureElement();
                        $this->value[$key] = $value->parse($sub_item, $dependencies);
                        break;
                    default:
                        $this->value[$key] = (isset($sub_item->content)) ? $sub_item->content : null;
                        break;
                }
            }
        }

        return $this;
    }

    function __toString()
    {
        if (!is_array($this->type)) {
            return '';
        }
        $return = '<ul class="list-group">';
        foreach ($this->type as $key => $item) {
            $type =
                (in_array($item, self::DEFAULTS)) ? $item : '<a href="#object-' . str_replace(' ', '-',
                        strtolower($item)) . '">' . $item . '</a>';

            $value =
                (isset($this->value[$key])) ? ': <span class="example-value pull-right">' . json_encode($this->value[$key]) . '</span>' : null;

            $return .= '<li class="list-group-item">' . $type . $value . '</li>';
        }
        $return .= '</ul>';

        return $return;
    }


}