<?php

namespace Tests\Classes;

use JK\FileMover;

define('FMTC_DOCROOT', 'tmp/data/docroot/');
class FileMoverTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        mkdir(FMTC_DOCROOT.'/foo', 0777, true);
        mkdir(FMTC_DOCROOT.'/bar', 0777, true);
        mkdir(FMTC_DOCROOT.'/baz', 0777, true);
        $this->fm = new FileMover(FMTC_DOCROOT);
        $this->fm->addStates( [
            'foo' => 'foo/',
            'bar' => 'bar/'
        ] );
    }
    public function tearDown()
    {
        system('rm -rf tmp/data/');
    }

    public function testAddStates() 
    {
        $this->fm->addStates( [
            'foo' => 'foo/',
            'baz' => 'baz/',
            'nah' => 'nonexistentdirectory/'
        ] );
        $this->assertEquals(count($this->fm->getStates()), 3);
    }

    public function testMove()
    {
        touch(FMTC_DOCROOT.'/baz/test.html');
        $this->fm->move('baz/test.html', 'foo');
        $this->assertTrue(file_exists(FMTC_DOCROOT.'/foo/test.html'));
    }

    public function testFind() 
    {
        touch(FMTC_DOCROOT.'/foo/test.html');
        $state = $this->fm->find('test.html');
        $this->assertEquals($state, 'foo');
    }

}
