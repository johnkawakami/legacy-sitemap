<?php
$docroot = '/www/riceball.com/public/';
$urlroot = "http://localhost:8080/";

return [
    'settings' => [
        "determineRouteBeforeAppMiddleware" => true,
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Text Sitemap settings
        'text-sitemap' => [
            'directories' => [
                'd/content/',
                'd/node/',
            ],
            'sitemap-path' => 'd/sitemap.txt',
            'remove-from-title' => '| a computer hobbyist\'s notebook',
            'root-directory' => $docroot,
            'root-url' => $urlroot
        ],

        // HTML File Mover
        'file-mover' => [
            'states' => [
                'trash' => 'trash/',
                'import' => 'html-files-to-import/',
                'retain' => 'r/'
            ],
            'root-directory' => $docroot
        ],

        // Apache Redirect manager for htaccess file
        'apache-redirect-manager' => [
            'htaccess' => 'd/.htaccess',
        ],

        // SQLite3 Database shared between the sitemap and redirect manager
        'database' => '/www/riceball.com/titles.sqlite',

    ],
];
