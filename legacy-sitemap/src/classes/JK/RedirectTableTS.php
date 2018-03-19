<?php

namespace JK;

/**
 * fixme - this should be split into two parts: the underlying data store, and the clever finders. 
 *
 * Manages the persisent data store for the redirect tables.
 *
 * Conforms to the other RedirectTable classes.
 */
class RedirectTableTS 
{
    function __construct($database) 
    {
        $this->database = new \SQLite3($database);
        $result = $this->database->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='redirects'");
        if ($result!='redirects') {
            $this->database->exec('CREATE TABLE redirects (file TEXT PRIMARY KEY, url TEXT)');
        }
    }

    private function insert($file, $url)
    {
        $stmt = $this->database->prepare('INSERT OR REPLACE INTO redirects 
                                          (file, url) VALUES (:file, :url)');
        $stmt->bindValue(':file', $file);
        $stmt->bindValue(':url', $url);
        $stmt->execute();
    }

    /**
     * You can add an entry for a file, without a target URL. This is
     * for situations where you don't know the target URL.  Just add
     * the file.  Make sure to add the path to the file, like archives/file-name.html
     *
     * The idea behind this is to make it feasible to make a decision to import
     * a file, before you know it's URL.  Later, when you know the URL, you may
     * add the redirect using the findSimilarFile() method.
     *
     * During the import, you know the filename and new url, but don't know the
     * old path.
     */
    public function addFile($file) 
    {
        $this->insert($file, '');
    }

    /**
     * Adds the rule without checking for similar file names.
     */
    public function addRedirect($file, $url)
    {
        $this->insert($file, $url);
        return [ 'file'=>$file, 'url'=>$url ];
    }

    /**
     * Adds the rule to a matching filename base, but if it
     * doesn't exist, just adds the redirect.
     */
    public function addRedirectBestMatchFirst($file, $url)
    {
        $found = $this->findSimilarFile($file);
        if ($found) {
            $this->addRedirect($found, $url);
            return [ 'file'=>$found, 'url'=>$url ];
        } else {
            $this->addRedirect($file, $url);
            return [ 'file'=>$file, 'url'=>$url ];
        }
    }

    /**
     * Adds an array of redirects.
     */
    public function addRedirects( $data ) 
    {
        foreach($data as $datum) {
            $this->addRedirect($datum['file'], $datum['url'], false);
        }
    }

    public function getData() 
    {
        $data = [];
        $result = $this->database->query('SELECT file, url FROM redirects');
        while($row = $result->fetchArray()) {
            if ($row['url'] != '') {
                $data[] = ['file'=>$row['file'], 'url'=>$row['url']];
            }
        }
        return $data;
    }

    /**
     * This is for situations when you don't know the complete path to the
     * old file.  It matches the file's basename, ignoring the path,
     * and returns any filename that matches.
     *
     * @return string filename that's a close match | null
     */
    public function findSimilarFile($file) 
    {
        $file = preg_replace('/^http[s]*:\/\//', '', $file);
        $file = basename($file);
        $stmt = $this->database->prepare('SELECT file FROM redirects WHERE file LIKE :file');
        $stmt->bindValue(':file', "%$file");
        $result = $stmt->execute();
        $row = $result->fetchArray();
        if ($row === false) {
            return null;
        }
        return $row['file'];
    }

    /**
     * Finds a file in the database, and returns a URL.
     *
     * @return null if nothing found, '' if redirect doesn't yet exist.
     */
    public function find($file) 
    {
        $stmt = $this->database->prepare('SELECT url FROM redirects WHERE file=:file');
        $stmt->bindValue(':file', $file);
        $result = $stmt->execute();
        $row = $result->fetchArray();
        return ($row['url'] ?? null);
    }
}
