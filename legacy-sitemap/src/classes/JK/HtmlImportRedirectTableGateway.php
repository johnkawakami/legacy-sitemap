<?php

namespace JK;

/**
 * Reads HTML Import 2 WordPress Plugin redirect code, and turns it into an a 
 * redirect table.
 *
 * The format matches the Apache2 config file format. (It differs from mod_rewrite rules.)
 */
class HtmlImportRedirectTableGateway extends RedirectTableGateway
{
    function __construct() 
    {
        parent::__construct();
    }

    function loadText($text) 
    {
        $lines = explode("\n", $text);
        foreach($lines as $line) {
            $line = rtrim($line);
            if ($line==='') continue;
            if (preg_match('/^Redirect\s+(.+?)\s+([^\s]+)\s*.*$/', $line, $matches)) {
                $this->addRedirect( $matches[1], $matches[2] );
            } else {
                $this->addError("No Match: $line");
            }
        }
        return $this;
    }
}
