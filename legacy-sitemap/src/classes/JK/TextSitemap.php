<?php

namespace JK;

/**
 * Produces a sitemap for directories with HTML files.
 */

class TextSitemap
{
    protected $sitemapPath;
    protected $dirs;
    protected $rootDir;
    protected $database;
    protected $rootUrl;
    protected $removeText;

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

    public function refreshDatabase() 
    {
        $this->database->exec('DELETE FROM titles');
        foreach($this->dirs as $dir) {
            $diter = dir($this->rootDir . $dir);
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

    public function getTextSitemap(): string
    {
        $result = $this->database->query('SELECT url FROM titles');
        $o = [];
        while($row = $result->fetchArray()) {
            $o[] = $row['url'];
        }
        return implode("\n", $o);
    }

    public function getHtmlSitemap(): string
    {
        $title_stmt = $this->database->prepare('SELECT url, title FROM titles');
		$result = $title_stmt->execute();
        $o = [];
        while($row = $result->fetchArray()) {
            $url = $row['url'];
            $title = $row['title'];
            $o[] = "<a href='$url'>$title</a><br />\n";
        }
        return implode("\n", $o);
    }

    public function getArraySitemap(): array
    {
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

    private function getHtmlTitle($path, $remove=null) 
    {
        $file = file_get_contents($this->rootDir . $path);
        if (preg_match("#<title>(.+)</title>#m", $file, $matches)) {
            $title = $matches[1];
            if ($remove !== null) {
                $title = str_replace($remove, '', $title);
            }
            return $title;
        }
        return null;
    }

    public function setRemoveFromTitle($text) {
        $this->removeText = $text;
    }
}
