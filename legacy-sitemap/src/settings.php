<?php

$dotenv = new Dotenv\Dotenv(__DIR__.'/../../', 'legacy-sitemap.env');
$dotenv->load();
$dotenv->required('LEGACY_SITEMAP_DOCROOT');
$dotenv->required('LEGACY_SITEMAP_URL');
$dotenv->required('LEGACY_SITEMAP_DATABASE');
$dotenv->required('LEGACY_SITEMAP_API_KEY');

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
            'remove-from-title' => '| a computer hobbyist\'s notebook',
            'root-directory' => getenv('LEGACY_SITEMAP_DOCROOT'),
            'root-url' => getenv('LEGACY_SITEMAP_URL'),
            'sitemap-path' => getenv('LEGACY_SITEMAP_DOCROOT').'d/sitemap.txt'
        ],

        // HTML File Mover
        'file-mover' => [
            'states' => [
                'trash' => 'trash/',
                'import' => 'html-files-to-import/',
                'retain' => 'r/'
            ],
            'root-directory' => getenv('LEGACY_SITEMAP_DOCROOT')
        ],

        // Apache Redirect manager for htaccess file
        'apache-redirect-table-gateway' => [
            'htaccess' => 'd/.htaccess',
            'root-directory' => getenv('LEGACY_SITEMAP_DOCROOT')
        ],

        // SQLite3 Database shared between the sitemap and redirect manager
        'database' => getenv('LEGACY_SITEMAP_DATABASE'),
        'appkey' => getenv('LEGACY_SITEMAP_API_KEY') 

    ],
];
