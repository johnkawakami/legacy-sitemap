<?php

namespace Tests\Classes;

use JK\HtmlImportRedirectTableGateway;

class HtmlImportTestCase extends \PHPUnit_Framework_TestCase
{
    function setUp() 
    {
        $this->sample = "Redirect	http://content/1441.html	http://riceball.com/sysadmin/postfix-config-on-debian-ubuntu-linux-to-get-past-my-uptight-spamassassin-filters/	[R=301,NC,L]
Redirect	http://content/1489.html	http://riceball.com/sysadmin/devpi-on-apache-with-daemontools-supervise/	[R=301,NC,L]
Redirect	http://content/1492.html	http://riceball.com/sysadmin/local-npm-with-daemontools/	[R=301,NC,L]
Redirect	http://content/1502.html	http://riceball.com/sysadmin/learning-to-install-mongodb/	[R=301,NC,L]
Redirect	http://content/1507.html	http://riceball.com/sysadmin/error-1698-28000-access-denied-for-user-rootlocalhost/	[R=301,NC,L]
Redirect	http://content/431.html	http://riceball.com/sysadmin/a-safer-way-to-copy-a-directory/	[R=301,NC,L]
Redirect	http://content/531.html	http://riceball.com/sysadmin/daylight-savings-time-misconfigurations-between-computers-can-lead-to-cumulative-data-errors/	[R=301,NC,L]
Redirect	http://content/842.html	http://riceball.com/sysadmin/testing-for-hack-scripts-scan-your-uploads/	[R=301,NC,L]
Redirect	http://content/980.html	http://riceball.com/sysadmin/payment-card-industry-data-security-standard-pci-dss-getting-with-the-program/	[R=301,NC,L]
Redirect	http://content/conditional-if-statements-dos-batch-files-bat-and-little-goto.html	http://riceball.com/sysadmin/conditional-if-statements-in-dos-batch-files-bat-and-a-little-goto/	[R=301,NC,L]
Redirect	http://content/finding-anagrams-list.html	http://riceball.com/sysadmin/finding-anagrams-in-a-list/	[R=301,NC,L]
Redirect	http://content/formatting-mobile-html-email.html	http://riceball.com/sysadmin/formatting-mobile-html-email/	[R=301,NC,L]
Redirect	http://content/perl-mechanize-extract-date.html	http://riceball.com/sysadmin/perl-mechanize-screen-scraper-makes-it-easy-to-copy-data-from-web-pages/	[R=301,NC,L]
Redirect	http://content/turning-california-warn-pdfs-text.html	http://riceball.com/sysadmin/turning-california-warn-pdfs-into-text/	[R=301,NC,L]";
        $this->hti = new HtmlImportRedirectTableGateway();
    }

    function testImport()
    {
        $this->hti->loadText($this->sample);
        $data = $this->hti->getData();
        $this->assertEquals($data[0]['file'], 'http://content/1441.html');
        $this->assertRegExp('/spamassassin-filters\/$/', $data[0]['url']);
        $this->assertRegExp('/anagrams/', $data[10]['file']);
        $this->assertRegExp('/anagrams/', $data[10]['url']);
    }
}
