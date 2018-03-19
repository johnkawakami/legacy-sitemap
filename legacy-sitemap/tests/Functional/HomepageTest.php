<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testGetHomepageWithoutName()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Slim 3', (string)$response->getBody());
        $this->assertNotContains('Hello', (string)$response->getBody());
    }

    public function testWsGetSitemap()
    {
        $response = $this->runApp('GET', '/ws/sitemap');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that the index route won't accept a post request
     */
    public function testPostStatus()
    {
        $response = $this->runApp('POST', '/ws/move', ['url'=>'http://example.com/d/node/123.html', 'state'=>'test']);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertContains('Method not allowed', (string)$response->getBody());
    }
}
