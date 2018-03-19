<?php

namespace Tests\Classes;

use JK\TextSitemap;

define('TS_DOCROOT', 'data/html/');
define('TS_DIR1', 'dir1/');
define('TS_DIR2', 'dir2/');

class TextSitemapTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->rootUrl = 'http://example.com/';
        $this->tsm = new TextSitemap(TS_DOCROOT, $this->rootUrl);
        $this->tsm->setDatabase(new \SQLite3('/tmp/test.database'));
        $this->assertTrue(file_exists('/tmp/test.database'));
    }

    public function testAddDirectories() 
    {
        $this->tsm->addDirectories(TS_DIR1);
        $this->assertTrue(in_array(TS_DIR1, $this->tsm->getDirectories()));
    }

    public function testAddDirectoriesArray() 
    {
        $this->tsm->addDirectories([TS_DIR1, TS_DIR2]);
        $this->assertTrue(in_array(TS_DIR1, $this->tsm->getDirectories()));
        $this->assertTrue(in_array(TS_DIR2, $this->tsm->getDirectories()));
    }

    public function testRefreshDatabase() 
    {
        $this->tsm->refreshDatabase();
    }

    public function testPathToUrl() 
    {
        $url = $this->tsm->pathToUrl('');
        $this->assertEquals($this->rootUrl, $url);
        $url = $this->tsm->pathToUrl('dir1/');
        $this->assertEquals($this->rootUrl.'dir1/', $url);

        $this->expectException(\Exception::class);
        $url = $this->tsm->pathToUrl(TS_DOCROOT.'dirnonexistent/');
    }

    public function testGetTextSitemap()
    {
        $this->tsm->addDirectories(TS_DIR1);
        touch(TS_DOCROOT.TS_DIR1.'file3.html');
        $this->tsm->refreshDatabase();
        $this->assertRegExp('/file3\.html$/', $this->tsm->getTextSitemap());
        unlink(TS_DOCROOT.TS_DIR1.'file3.html');
    }

    public function testGetHtmlSitemap()
    {
        $this->tsm->addDirectories(TS_DIR1);
        $this->tsm->refreshDatabase();
        $html = $this->tsm->getHtmlSitemap();
        $code = "<a href='http://example.com/dir1/file2.html'>Test Title 2</a>";
        $this->assertRegExp("#$code#", $html);
    }

    public function testSaveTextSitemap()
    {
        $this->tsm->setSitemapPath('/tmp/sitemap.txt');
        $this->tsm->addDirectories(TS_DIR1);
        $this->tsm->addDirectories(TS_DIR2);
        $this->tsm->refreshDatabase();
        $this->tsm->saveTextSitemap();
        $this->assertTrue(file_exists('/tmp/sitemap.txt'));

        $file = file_get_contents('/tmp/sitemap.txt');
        $this->assertRegExp('/file1\.html$/m', $file);
        $this->assertRegExp('/file2\.html$/m', $file);
        $this->assertRegExp('/file3\.html$/m', $file);
    }

}
