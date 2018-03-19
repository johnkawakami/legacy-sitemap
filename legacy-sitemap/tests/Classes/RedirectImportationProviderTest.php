<?php

namespace Tests\Classes;

use JK\RedirectImportationProvider;

class RedirectImportationProviderTestCase extends \PHPUnit_Framework_TestCase
{
    function setUp() 
    {
        $this->sample = "Redirect	http://content/1441.html	http://riceball.com/sysadmin/postfix-config-on-debian-ubuntu-linux-to-get-past-my-uptight-spamassassin-filters/	[R=301,NC,L]
Redirect	http://content/1489.html	http://riceball.com/sysadmin/devpi-on-apache-with-daemontools-supervise/	[R=301,NC,L]
Redirect	http://content/turning-california-warn-pdfs-text.html	http://riceball.com/sysadmin/turning-california-warn-pdfs-into-text/	[R=301,NC,L]";
        $start = "# BEGIN PAGE REDIRECTS";
        $end = '# END PAGE REDIRECTS';
        $htaccess = "Ignore this \n$start\nRewriteRule ^1441.html$ bar.html [R=301,L]\n$end\nIgnore this";
        file_put_contents('/tmp/htaccess', $htaccess);
        unlink('/tmp/test.sqlite3');
        $this->rip = new RedirectImportationProvider(
            new \JK\RedirectTableTS('/tmp/test.sqlite3'),
            new \JK\HtmlImportRedirectTableGateway(),
            new \JK\ApacheRedirectTableGateway('/tmp/htaccess')
        );
    }
    function testImport()
    {
        $this->rip->importText($this->sample);

        $imported = $this->rip->getImportedRows();

        // the 1441.html should be preserved
        $this->assertEquals('1441.html', $imported[0]['file']);
        $this->assertRegExp('/postfix/', $imported[0]['url']);

        $htaccess = file_get_contents('/tmp/htaccess');
        $this->assertRegExp('/1489.html/m', $htaccess);
    }
}
