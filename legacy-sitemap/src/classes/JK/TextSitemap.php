<?php

namespace JK;

/**
 * Produces text and HTML sitemaps for directories with HTML files.
 *
 * Text sitemaps are simple lists of URLs.
 *
 * HTML sitemaps are linked titles; the Array sitemap is an array of
 * URLs and titles.
 *
 * The sitemap can be written to a file.
 *
 * @example
 *     $ts = new TextSitemap($dir, $url);
 *     $ts->setDatabase($pathToDb);
 *     $ts->addDirectories( ['state'=>'path', ...] );
 *     $ts->setRemoveFromTitle('Text in the Title');
 *     $ts->refreshDatabase();
 *
 *     $ts->setSitemapPath( ... );
 *     $ts->saveTextSitemap();
 *
 * fixme - this should be broken into two classes, the sitemap
 * rendering and saving should be separate from the database.
 * SitemapTableGateway.php and TextSitemap.php
 * and maybe HtmlDirectoryGateway.php
 */
class TextSitemap
{
    protected $sitemapPath;
    protected $dirs;
    protected $rootDir;
    protected $database;
    protected $rootUrl;
    protected $removeText;
    const REFRESH_FLAG = '/tmp/legacy-sitemap.refresh.flag';

    function __construct($rootDir, $rootUrl) 
    {
        $this->rootDir = realpath($rootDir).DIRECTORY_SEPARATOR;
        $this->rootUrl = $rootUrl;
        $this->dirs = [];
        $this->removeText = null;
    }

    public function setDatabase(\SQLite3 $db) 
    {
        $this->database = $db;
        $this->database->enableExceptions(true);
        $result = $this->database->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='titles'");
        if ($result!='titles') {
            $this->database->exec('CREATE TABLE titles (url TEXT PRIMARY KEY, title TEXT)');
            touch(self::REFRESH_FLAG);
        }
    }

    public function addDirectories($dirs) 
    {
        if (is_string($dirs)) {
            $dirs = [$dirs];
        }
        $this->dirs = array_merge($this->dirs, $dirs);
        $this->dirs = array_unique($this->dirs);
    }

    public function getDirectories(): array
    {
        return $this->dirs;
    }

    public function setSitemapPath($path) 
    {
        $this->sitemapPath = $path;
        touch($this->sitemapPath);
    }

    /**
     * Scans the directories and produces a table of titles and file paths.
     * The titles are extracted from the HTML files.
     */
    public function refreshDatabase() 
    {
        $this->database->exec('DELETE FROM titles');
        foreach($this->dirs as $dir) {
            $diter = dir($this->rootDir . $dir);
            if ($diter === false) {
                throw new Exception($dir." caused error");
            }
            while($filename = $diter->read()) {
                if ($filename=='.' || $filename=='..') continue;
                $path = $dir . $filename;
                $url = $this->pathToUrl($path);
                $title = $this->getHtmlTitle($path, $this->removeText);

                $insert_stmt = $this->database->prepare('INSERT INTO titles (title, url) VALUES (:title, :url)');
                $insert_stmt->bindValue(':url', $url);
                $insert_stmt->bindValue(':title', $title);
                $insert_stmt->execute();
            }
        }
    }

    /**
     * Converts a file path to a URL, both rooted in the docroot and url root.
     * 
     * @throws Exception if the file does not exist.
     * fixme - this needs to accept paths relative to the root dir 
     */
    public function pathToUrl($path): string
    {
        $fullpath = $this->rootDir . $path;
        if (!file_exists($fullpath)) {
            throw new \Exception("$fullpath does not exist");
        }
        if (is_dir($fullpath)) {
            $path .= DIRECTORY_SEPARATOR;
        }
        return $this->rootUrl . str_replace($this->rootDir, '', $fullpath);
    }

    /**
     * Converts a URL to a file path.
     */
    public function urlToPath($url): string
    {
        // strip the root URL
        $path = str_replace($this->rootUrl, '', $url);
        return $path;
    }

    /**
     * @return string A list of URLs.
     */
    public function getTextSitemap(): string
    {
        $arr = $this->getArraySitemap();
        $o = [];
        foreach($arr as $row) {
            $o[] = $row['url'];
        }
        return implode("\n", $o);
    }

    /**
     * @return string HTML sitemap of linked titles.
     */
    public function getHtmlSitemap(): string
    {
        $arr = $this->getArraySitemap();
        $o = [];
        foreach($arr as $row) {
            $url = $row['url'];
            $title = $row['title'];
            $o[] = "<a href='$url'>$title</a><br />\n";
        }
        return implode("\n", $o);
    }

    /**
     * @return array Sitemap as an array of URLs and Titles.
     */
    public function getArraySitemap(): array
    {
        if (file_exists(self::REFRESH_FLAG)) {
            $this->refreshDatabase();
            unlink(self::REFRESH_FLAG);
        }
        $title_stmt = $this->database->prepare('SELECT url, title FROM titles');
		$result = $title_stmt->execute();
        $o = [];
        while($row = $result->fetchArray()) {
            $o[] = ['url'=>$row['url'], 'title'=>$row['title']];
        }
        return $o;
    }

    public function saveTextSitemap()
    {
        $sitemap = $this->getTextSitemap();
        file_put_contents($this->sitemapPath, $sitemap);
    }

    /**
     * Utility function to extract HTML titles from HTML files.
     */
    private function getHtmlTitle($path, $remove=null) 
    {
        $file = file_get_contents($this->rootDir . $path);
        if (preg_match("#<title>(.+)</title>#m", $file, $matches)) {
            $title = $matches[1];
            if ($remove !== null) {
                $title = str_replace($remove, '', $title);
            }
            $title = html_entity_decode($title, ENT_QUOTES|ENT_HTML5 );
            return $title;
        }
        return null;
    }

    /**
     * Titles from most CMSs include the site title along with
     * the article title. This sets a string that's removed
     * from the title when the titles are read from the files.
     */
    public function setRemoveFromTitle($text) {
        $this->removeText = $text;
    }
}
