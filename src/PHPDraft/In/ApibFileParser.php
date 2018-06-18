<?php
/**
 * This file contains the ApibFileParser.
 *
 * @package PHPDraft\In
 *
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\In;

use PHPDraft\Parse\ExecutionException;

/**
 * Class ApibFileParser.
 */
class ApibFileParser
{
    /**
     * Complete API Blueprint.
     *
     * @var string
     */
    protected $full_apib;

    /**
     * Location of the API Blueprint to parse.
     *
     * @var string
     */
    protected $location;

    /**
     * Filename to parse.
     *
     * @var
     */
    private $filename;

    /**
     * FileParser constructor.
     *
     * @param string $filename File to parse
     */
    public function __construct($filename = 'index.apib')
    {
        $this->filename = $filename;
        $this->location = pathinfo($this->filename, PATHINFO_DIRNAME) . '/';

        set_include_path(get_include_path() . ':' . $this->location);
    }

    public function parse()
    {
        $this->full_apib = $this->get_apib($this->filename);

        return $this;
    }

    /**
     * Parse a given API Blueprint file
     * This changes all `include(file)` tags to the contents of the file.
     *
     * @param string $filename File to parse
     *
     * @return string The full API blueprint file
     */
    private function get_apib($filename)
    {
        $path    = $this->file_path($filename);
        $file    = file_get_contents($path);
        $matches = [];
        preg_match_all('<!-- include\(([\S\s]*?)(\.[a-z]*?)\) -->', $file, $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $file = str_replace('<!-- include(' . $matches[1][$i] . $matches[2][$i] . ') -->',
                $this->get_apib($matches[1][$i] . $matches[2][$i]), $file);
        }

        preg_match_all('<!-- schema\(([a-z0-9_.\/\:]*?)\) -->', $file, $matches);
        foreach ($matches[1] as $value) {
            $file = str_replace('<!-- schema(' . $value . ') -->', $this->get_schema($value), $file);
        }

        return $file;
    }

    /**
     * Check if an APIB file exists.
     *
     * @param string $filename File to check
     *
     * @throws ExecutionException when the file could not be found.
     *
     * @return string
     */
    private function file_path($filename)
    {
        $path = $this->location . $filename;
        if (file_exists($path)) {
            return $path;
        }
        $path = stream_resolve_include_path($filename);
        if ($path === FALSE || !file_exists($path)) {
            throw new ExecutionException("API File not found: $filename", 1);
        }

        return $path;
    }

    /**
     * Get an external Schema by URL.
     *
     * @param string $url URL to fetch the schema from
     *
     * @return string The schema as a string
     */
    private function get_schema($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Return the value of the class.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->full_apib;
    }
}
