<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/', function (Request $request, Response $response, array $args) {
    return 'Hello, world.';
});
$app->get('/refresh', function (Request $request, Response $response, array $args) {
    $this->textSitemap->refreshDatabase();
    $args['list'] = $this->textSitemap->getHtmlSitemap();
    return $this->renderer->render($response, 'app.phtml', $args);
});

// REST API
$app->get('/sitemap', function (Request $request, Response $response, array $args) {
    $sitemap = $this->textSitemap->getArraySitemap();
    $newResponse = $response->withJson($sitemap);
    return $newResponse;
});

$app->post('/move', function (Request $request, Response $response, array $args) {
    $params = $request->getParsedBody();
    $url = $params['url'];
    $state = $params['state'];

    if (!in_array($state, ['import', 'retain', 'trash'])) {
        return $response->withStatus(403)->withJson(['status'=>'invalid state']);
    }

    // convert the URL to a filename
    $file = $this->textSitemap->urlToPath($url);
    $this->fileMover->move($file, $state);
    $this->textSitemap->refreshDatabase();
    $this->textSitemap->saveTextSitemap();

    $o = [ 'file'=>$file, 'state'=>$state ];
    return $response->withJson($o);
});

