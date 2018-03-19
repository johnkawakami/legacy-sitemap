<?php

namespace JK;

/**
 * Base class for classes that adapt different data formats that hold redirect data.
 *
 * Redirect data is passed in and out of the object as an array with this format:
 *
 *  [ [ 'file' => $filename, 'url' => $targetUrl ], ... ]
 *
 * There are no duplicate filenames.
 * 
 *     $r = new RedirectTableGateway();
 *     $r->addRedirect('foo', 'bar');
 *     $r->addRedirect('foo', 'boo');
 *     $r->getFind('foo'); // returns 'boo'
 *
 * This class makes no restrictions about how the file or url is related to the
 * docroot or urlroot, but it's best that all paths should be made
 * relative to the docroot.
 */
class RedirectTableGateway 
{
    protected $data;
    protected $errors;

    public function __construct() 
    {
        $this->data = [];
        $this->errors = [];
    }

    /**
     * Returns the entire database.
     */
    public function getData()
    {
        $o = [];
        foreach($this->data as $key=>$value) {
            $o[] = [ 'file'=>$key, 'url'=>$value ];
        }
        return $o;
    }

    /**
     * Adds a file to url mapping.
     * If there's already an existing entry for the file, it's overwritten.
     */
    public function addRedirect($file, $url) 
    {
        $this->data[$file] = $url;
    }

    /**
     * @return array of error messages.
     */
    public function getErrors() 
    {
        return $this->errors;
    }

    /**
     * Accumulates error messages.
     */
    public function addError($message) 
    {
        $this->errors[] = $message;
    }

    /**
     * @return string the URL for a given file, or false if nothing is found.
     */
    public function find($file) 
    {
        if (isset($this->data[$file])) return $this->data[$file];
        else return false;
    }

    /**
     * Replaces the entire database.
     */
    public function replaceData($data) 
    {
        $this->data = [];
        foreach($data as $row) {
            $this->data[$row['file']] = $row['url'];
        }
    }

}
