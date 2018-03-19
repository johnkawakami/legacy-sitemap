<?php

namespace JK;

/**
 * Manages a block of 301 redirects in an .htaccess file.
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
 *
 * Note: all paths exposed to the world are relative to the docroot,
 * but all paths in the .htaccess file are relative to the .htaccess
 * file.
 *
 * If you pass a new table into this class, the file paths should
 * be relative to the docroot.
 */
class ApacheRedirectTableGateway extends RedirectTableGateway
{
    protected $start = "# BEGIN PAGE REDIRECTS";
	protected $end   = "# END PAGE REDIRECTS";
    private $docroot;
    private $htaccess;
    private $htaccesspath;
    private $htaccessroot;

    function __construct(string $docroot, string $htaccess) 
    {
        parent::__construct();
        $this->docroot = $docroot;
        $this->htaccess = $htaccess;  // relative to docroot
        $this->htaccesspath = $docroot . DIRECTORY_SEPARATOR . $htaccess;
        /**
         * Path from the docroot to this .htaccess file.
         */
        $this->htaccessroot = str_replace(basename($this->htaccess), '', $this->htaccess);
    }

    /**
     * Takes the path from the .htaccess and roots it at the docroot.
     */
    private function atDocroot(string $file): string 
    {
        return $this->htaccessroot.$file;
    }

    /**
     * Takes the path relative to the docroot, and makes it rooted to the .htaccess
     */
    private function atHtaccessRoot(string $file): string
    {
        return str_replace($this->htaccessroot, '', $file);
    }

    /**
     * Reads the .htaccess file, extracts redirects, creates table.
     */
    public function loadTable() 
    {
        $file = file($this->htaccesspath);
        $row = 0;
        while(isset($file[$row]) && (strpos($file[$row], $this->start) === false)) {
            $row++;
        }
        do {
            if (preg_match("#^RewriteRule\s+\^(.+?)[\$]{0,1}\s+(.+?)\s#", $file[$row], $matches) ) {
                $this->addRedirect( $this->atDocroot($matches[1]), $matches[2] );
            }
            $row++;
        } while(isset($file[$row]) && (strpos($file[$row], $this->end) === false));
    }


    /**
     * Writes table out to the .htaccess file.
     */
    public function saveTable()
    {
        $lines = [];
        foreach($this->getData() as $row) {
            $file = $this->atHtaccessRoot($row['file']);
            $lines[] = 'RewriteRule ^'.$file.'$ '.$row['url'].' [R=301,NC,L]';
        }
        arsort($lines);
        $text = implode("\n", $lines);

        $file = file_get_contents($this->htaccesspath);
        $regex = '/'.$this->start.'.+?'.$this->end.'/ms'; 
        $newtext = $this->start."\n".$text."\n".$this->end; 
        $newfile = preg_replace($regex, $newtext, $file);
        file_put_contents($this->htaccesspath, $newfile);
    }
}
