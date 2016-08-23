<?php
/**
 * This file contains the TemplateGenerator.php
 *
 * @package PHPDraft\Out
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Out;

use Michelf\Markdown;
use PHPDraft\Model\Category;
use PHPDraft\Model\DataStructureElement;

class TemplateGenerator
{
    /**
     * JSON object of the API blueprint
     * @var mixed
     */
    protected $categories = [];

    /**
     * The template file to load
     * @var string
     */
    protected $template;

    /**
     * The base URl of the API
     * @var
     */
    protected $base_data;

    /**
     * Structures used in all data
     * @var DataStructureElement[]
     */
    protected $base_structures = [];

    /**
     * TemplateGenerator constructor.
     *
     * @param string $template name of the template to load
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Pre-parse objects needed and print HTML
     *
     * @param mixed $object JSON to parse from
     *
     * @return void
     */
    public function get($object)
    {
        //Prepare base data
        if (is_array($object->content[0]->content))
        {
            foreach ($object->content[0]->attributes->meta as $meta)
            {
                $this->base_data[$meta->content->key->content] = $meta->content->value->content;
            }

            $this->base_data['TITLE'] = $object->content[0]->meta->title;
        }

        //Parse specific data
        if (is_array($object->content[0]->content))
        {
            foreach ($object->content[0]->content as $value)
            {
                if ($value->element === 'copy')
                {
                    $this->base_data['DESC'] =
                        preg_replace('/(<\/?p>)/', '', Markdown::defaultTransform($value->content), 2);
                    continue;
                }

                $cat                = new Category();
                $this->categories[] = $cat->parse($value);

                if ($value->meta->classes[0] === 'dataStructures')
                {
                    $this->base_structures = $cat->structures;
                }
            }
        }

        include_once 'PHPDraft/Out/HTML/' . $this->template . '.php';
    }

    /**
     * Get an icon for a specific HTTP Method
     *
     * @param string $method HTTP method
     *
     * @return string class to represent the HTTP Method
     */
    function get_method_icon($method)
    {
        switch (strtolower($method))
        {
            case 'post':
                $class = 'glyphicon glyphicon-plus';
                break;
            case 'put':
                $class = 'glyphicon glyphicon-pencil';
                break;
            case 'get':
                $class = 'glyphicon glyphicon-arrow-down';
                break;
            case 'delete':
                $class = 'glyphicon glyphicon-remove';
                break;
            default:
                $class = '';
        }

        return $class . ' ' . $method;
    }

    /**
     * Get a bootstrap class to represent the HTTP return code range
     *
     * @param int $response HTTP return code
     *
     * @return string Class to use
     */
    function get_response_status($response)
    {
        if ($response <= 299)
        {
            return 'success';
        }
        elseif ($response > 299 && $response <= 399)
        {
            return 'warning';
        }
        else
        {
            return 'error';
        }
    }

    /**
     * Determine if an object should be printed
     *
     * @param DataStructureElement $object Objects to print
     *
     * @return string Object representation
     */
    function get_data_structure($object)
    {
        if (!get_class($object) === 'DataStructureElement')
        {
            return;
        }
        return $object;
    }

}