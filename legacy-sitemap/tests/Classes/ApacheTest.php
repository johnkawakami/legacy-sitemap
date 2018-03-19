<?php

namespace Tests\Classes;

use JK\ApacheRedirectTableGateway;

class ApacheTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        copy('data/htaccess', 'data/.htaccess');
        $this->arm = new ApacheRedirectTableGateway( 'data/.htaccess' );
        $this->assertTrue(file_exists('data/.htaccess'));
    }
    public function tearDown()
    {
        unlink('data/.htaccess');
    }

    public function testLoadFromHtaccess()
    {
        $this->arm->loadTable();
        $url = $this->arm->find('data/html/file2.html');
        $this->assertEquals($url, 'foo/file2.html');
    }

    public function testWriteToHtaccess()
    {
        $this->arm->loadTable();
        $this->arm->addRedirect('data/html/file1.html', 'foo/file1.html');
        $this->arm->saveTable();
        $file = file_get_contents('data/.htaccess');
        $this->assertRegExp('#foo/file1.html#m', $file);
        $this->assertRegExp('#data/html/file1.html#m', $file);
    }

}
