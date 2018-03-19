<?php

namespace Tests\Classes;

use JK\RedirectTableTS;

class RedirectTableTsTestCase extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $db = "/tmp/test.sqlite3";
        @unlink($db);
        $this->ts = new RedirectTableTS($db);
    }

    function testAddFile()
    {
        $this->ts->addFile('foo.html');
        $url = $this->ts->find('foo.html');
        $this->assertEquals($url, '');
        $url = $this->ts->find('nothing');
        $this->assertEquals($url, null);
    }

    function testAddRedirect()
    {
        $this->ts->addRedirect('foo.html', 'bar.html');
        $url = $this->ts->find('foo.html');
        $this->assertEquals('bar.html', $url);
    }

    function testAddRedirects()
    {
        $data = [
            [ 'file' => 'foo.html', 'url' => 'bar.html' ],
            [ 'file' => 'baz.html', 'url' => 'glorp.html' ],
        ];
        $this->ts->addRedirects($data);
        $url = $this->ts->find('foo.html');
        $this->assertEquals('bar.html', $url);
        $url = $this->ts->find('baz.html');
        $this->assertEquals('glorp.html', $url);
    }

    function testFindSimilarFile()
    {
        $this->ts->addFile("foobar/var.var");
        $found = $this->ts->findSimilarFile('car/var.var');
        $this->assertEquals('foobar/var.var', $found);
    }
}
