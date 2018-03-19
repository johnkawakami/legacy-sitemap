<?php

namespace Tests\Classes;

use JK\RedirectTableGateway;

class RedirectTableTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $this->rt = new RedirectTableGateway();
    }

    public function testAddRedirect()
    {
        $this->rt->addRedirect('test1.html', 'foo1.html');
        $data = $this->rt->getData();
        $this->assertEquals($data[0]['file'], 'test1.html');
        $this->assertEquals($data[0]['url'], 'foo1.html');
    }

    public function testFind()
    {
        $this->rt->addRedirect('test1.html', 'foo1.html');
        $url = $this->rt->find('test1.html');
        $this->assertEquals($url, 'foo1.html');
    }
}
