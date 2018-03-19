<?php

/**
 * Moves files between directories.
 *
 * Each directory is referenced as a named "state", and the file is 
 * moved between directories by "state" rather than path.
 *
 * This way, you can organize your files without messing with paths.
 *
 * All paths are relative to the $rootDir.
 * Absolute paths cannot be specified.
 *
 * If a file is not found at the path given, it finds the file within
 * one of the state directories, and moves it to the destination.
 */

namespace JK;

class FileMover
{
    protected $rootDir;
    protected $states;

    public function __construct($rootDir) 
    {
        if (!$rootDir) throw new \Exception("root dir must be specified");
        $this->rootDir = realpath($rootDir).DIRECTORY_SEPARATOR;
        $this->states = [];
    }

    public function addStates($states) 
    {
        foreach($states as $state=>$dir) {
            $path = realpath($this->rootDir.$dir);
            // add or change the state only if the path exists
            if (file_exists($path)) {
                $this->states[$state] = realpath($this->rootDir.$dir);
            }
        }
    }

    public function getStates() {
        return $this->states;
    }

    public function move($filepath, $state) 
    {
        if (substr($filepath,0,1)=='/') throw new Exception('Absoulte paths not allowed.');
        $filename = basename($filepath);
        $path = $this->rootDir.$filepath;
        if (file_exists($path)) {
            rename($path, $this->states[$state].DIRECTORY_SEPARATOR.$filename);
        } else {
            $oldstate = $this->find($filename);
            if ($oldstate == $state) return;

            $path1 = $this->states[$oldstate].DIRECTORY_SEPARATOR.$filename;
            $path2 = $this->states[$state].DIRECTORY_SEPARATOR.$filename;
            rename($path1, $path2);
        }
    }

    /**
     * Finds a path given a filename.
     */
    public function find($filename) 
    {
        foreach($this->states as $state=>$dir) {
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($path)) {
                return $state;
            }
        }
        throw new \Exception("$filename not found in any directory");
    }
}
