<?php

namespace Tests\Classes;

use JK\ApacheRedirectManager;

class ApacheRedirectManagerTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        copy('data/htaccess', 'data/.htaccess');
        $this->arm = new ApacheRedirectManager( '/tmp/redirs.sqlite3', 'data/.htaccess' );
        $this->assertTrue(file_exists('/tmp/redirs.sqlite3'));
        $this->assertTrue(file_exists('data/.htaccess'));
    }
    public function tearDown()
    {
        unlink('data/.htaccess');
        unlink('/tmp/redirs.sqlite3');
    }

    public function testLoadFromHtaccess()
    {
        $this->arm->loadFromHtaccess();
        $url = $this->arm->findRedirect('data/html/file2.html');
        $this->assertEquals($url, 'foo/file2.html');
    }

    public function testWriteToHtaccess()
    {
        $this->arm->loadFromHtaccess();
        $this->arm->addRedirect('data/html/file1.html', 'foo/file1.html');
        $this->arm->writeToHtaccess();
        $file = file_get_contents('data/.htaccess');
        $this->assertRegExp('#foo/file1.html#m', $file);
        $this->assertRegExp('#data/html/file1.html#m', $file);
    }

    public function testAddRedirectFuzzy()
    {
        $this->arm->addFile('dir/test-file-name.html');
        $this->arm->addRedirect('test-file-name.html', 'http://test.com/');
        $this->arm->writeToHtaccess();
        $file = file_get_contents('data/.htaccess');
    }

    public function testFindSimilarFile() 
    {
        $this->arm->addFile('dir/test-file-name.html');
        $filename = $this->arm->findSimilarFile('test-file-name.html');
        $this->assertEquals($filename, 'dir/test-file-name.html');
    }
}
