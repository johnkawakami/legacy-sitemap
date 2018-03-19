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

// apache htaccess redirect manager
$container['apacheRedirectManager'] = function($c) {
    $settings = $c->get('settings')['apache-redirect-manager'];
    $manager = new JK\ApacheRedirectManager($c->get('database'), $settings['htaccess']);
    return $manager;
};

