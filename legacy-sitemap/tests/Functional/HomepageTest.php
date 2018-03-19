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
        $this->assertContains('Legacy Sitemap', (string)$response->getBody());
    }
    public function testRefreshAPI()
    {
        $response = $this->runApp('GET', '/refresh');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Legacy Sitemap', (string)$response->getBody());
    }

    public function testGetSitemap()
    {
        $response = $this->runApp('GET', '/sitemap');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getBody())));
    }

    /**
     * Test that the index route won't accept a post request
     */
    public function testPostStatus()
    {
        $response = $this->runApp('POST', '/badmove', ['url'=>'http://example.com/d/node/123.html', 'state'=>'test']);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertContains('Method not allowed', (string)$response->getBody());
    }
}
