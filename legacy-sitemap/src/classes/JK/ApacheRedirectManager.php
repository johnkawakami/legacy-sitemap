<?php

namespace JK;

/**
 * Manages a block of 301 redirects in an .htaccess file.
 * The old and new URLs are stored in a SQLite database,
 * and the old and new parts can be saved at different
 * times.
 * 
 * So you can put in a URL to be redirected, and leave
 * the location blank.  The location can be added later.
 *
 * The database is considered authoritative.
 *
 * Loading from the htaccess file inserts and updates the database.
 * Loading does not erase the database.
 *
 * The htaccess file must be formatted like this for the reader
 * method to parse it:
 *
 * # BEGIN PAGE REDIRECTS
 * RewriteRule ^file/path/match.html$ some/url/path.html [R=301,NC,L]
 * RewriteRule ^file/path/match.html some/url/path.html [R=301,NC,L]
 * # END PAGE REDIRECTS
 *
 * The only parts that may vary are the $ and [...]. Lines that
 * do not match will be ignored and not imported. 
 *
 * When the htaccess file is written out, it will be written with the 
 * $ and [...] as above.  Lines that are not imported will not 
 * be written out.
 */

class ApacheRedirectManager 
{
    protected $start = "# BEGIN PAGE REDIRECTS";
	protected $end = '# END PAGE REDIRECTS';

    function __construct($database, $htaccess) 
    {
        $this->database = new \SQLite3($database);
        $result = $this->database->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='redirects'");
        if ($result!='redirects') {
            $this->database->exec('CREATE TABLE redirects (file TEXT PRIMARY KEY, url TEXT)');
        }
        $this->htaccess = $htaccess;
    }

    public function loadFromHtaccess() 
    {
        $file = file($this->htaccess);
        // echo var_dump($file);
        $row = 0;
        while(strpos($file[$row], $this->start) === false) {
            $row++;
        }
        $row++; // consume the start marker
        do {
            if (preg_match("#^RewriteRule\s+\^(.+?)[\$]{0,1}\s+(.+?)\s#", $file[$row], $matches) ) {
                $filename = $matches[1];
                $url = $matches[2];
                $stmt = $this->database->prepare('INSERT OR REPLACE INTO redirects (file, url) VALUES (:file, :url)');
                $stmt->bindValue(':file', $filename);
                $stmt->bindValue(':url', $url);
                $stmt->execute();
            }
            $row++;
        } while(isset($file[$row]) && (strpos($file[$row], $this->end) === false));
    }

    public function writeToHtaccess()
    {
        $result = $this->database->query('SELECT file, url FROM redirects');
        $lines = [];
        while($row = $result->fetchArray()) {
            if ($row['url'] != '') {
                $lines[] = 'RewriteRule ^'.$row['file'].'$ '.$row['url'].' [R=301,NC,L]';
            }
        }
        arsort($lines);
        $text = implode("\n", $lines);

        $file = file_get_contents($this->htaccess);
        $regex = '/'.$this->start.'.+?'.$this->end.'/ms'; 
        $newtext = $this->start."\n".$text."\n".$this->end."\n"; 
        $newfile = preg_replace( $regex, $newtext, $file);
        file_put_contents($this->htaccess, $newfile);
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
        $stmt = $this->database->prepare('INSERT OR REPLACE INTO redirects (file, url) VALUES (:file, :url)');
        $stmt->bindValue(':file', $file);
        $stmt->bindValue(':url', '');
        $stmt->execute();
    }

    public function addRedirect($file, $url, $fuzzyMatch=true)
    {
        // try to find an exact match
        $stmt = $this->database->prepare('SELECT url FROM redirects WHERE file=:file');
        $stmt->bindValue(':file', $file);
        $result = $stmt->execute();

        // if there's no match, and we are being fuzzy, try to find a match
        if (($result->columnType(0) === SQLITE3_NULL) && $fuzzyMatch) {
            $file = ($this->findSimilarFile($file) ?? $file);
        }

        // if it's not exact, make a fuzzy match
        $stmt = $this->database->prepare('INSERT OR REPLACE INTO redirects 
                                          (file, url) VALUES (:file, :url)');
        $stmt->bindValue(':file', $file);
        $stmt->bindValue(':url', $url);
        $stmt->execute();
    }

    /**
     * This is for situations when you don't know the complete path to the
     * old file.  It matches the file's basename, ignoring the path,
     * and returns any filename that matches.
     */
    public function findSimilarFile($file) 
    {
        $stmt = $this->database->prepare('SELECT file FROM redirects WHERE file LIKE :file');
        $stmt->bindValue(':file', "%/$file");
        $result = $stmt->execute();
        $row = $result->fetchArray();
        if ($row === false) {
            return null;
        }
        return $row['file'];
    }

    public function findRedirect($file) 
    {
        $stmt = $this->database->prepare('SELECT url FROM redirects WHERE file=:file');
        $stmt->bindValue(':file', $file);
        $result = $stmt->execute();
        $row = $result->fetchArray();
        return ($row['url'] ?? null);
    }
}
