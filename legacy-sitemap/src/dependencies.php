<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// SQLite3 database shared across the app
$container['database'] = function($c) {
    return new SQLite3($c->get('settings')['database']);
};

// textSitemap scans directories and generates sitemaps
$container['textSitemap'] = function($c) {
    $settings = $c->get('settings')['text-sitemap'];
    $sitemap = new JK\TextSitemap($settings['root-directory'], $settings['root-url']);
    $sitemap->setDatabase($c->get('database'));
    $sitemap->addDirectories($settings['directories']);
    $sitemap->setSitemapPath($settings['sitemap-path']);
    $sitemap->setRemoveFromTitle($settings['remove-from-title']);
    return $sitemap;
};

// fileMover moves files into directories
$container['fileMover'] = function($c) {
    $settings = $c->get('settings')['file-mover'];
    $fileMover = new JK\FileMover($settings['root-directory']);
    $fileMover->addStates($settings['states']);
    return $fileMover;
};

// reads and writes .htaccess files
$container['apacheRedirectTableGateway'] = function($c) {
    $settings = $c->get('settings')['apache-redirect-table-gateway'];
    $manager = new JK\ApacheRedirectTableGateway($settings['root-directory'], $settings['htaccess']);
    return $manager;
};

// reads the output from HTML Import
$container['htmlImportRedirectTableGateway'] = function($c) {
    $settings = $c->get('settings')['html-import-redirect-table-gateway'];
    $manager = new JK\HtmlImportRedirectTableGateway();
    return $manager;
};

// higher-level functions to work with the database's redirect table
$container['redirectTableTS'] = function($c) {
    $settings = $c->get('settings');
    $manager = new JK\RedirectTableTS($settings['database']);
    return $manager;
};

// implementation for the importation service
$container['redirectImportationProvider'] = function($c) {
    $manager = new JK\RedirectImportationProvider(
        $c['redirectTableTS'],
        $c['htmlImportRedirectTableGateway'],
        $c['apacheRedirectTableGateway']
    );

    return $manager;
};

