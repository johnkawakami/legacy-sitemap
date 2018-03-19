<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/', function (Request $request, Response $response, array $args) {
    return 'Legacy Sitemap';
});
$app->get('/refresh', function (Request $request, Response $response, array $args) {
    try {
        $this->textSitemap->refreshDatabase();
        $args['list'] = $this->textSitemap->getHtmlSitemap();
        return $this->renderer->render($response, 'app.phtml', $args);
    } catch (Exception $e) {
        return $response->withStatus(500)->write(htmlspecialchars($e->getMessage()));
    }
});

// REST API
$app->get('/sitemap', function (Request $request, Response $response, array $args) {
    try {
        $sitemap = $this->textSitemap->getArraySitemap();
        $newResponse = $response->withJson($sitemap);
        return $newResponse;
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['status'=> ($e->getMessage()) ]);
    }
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
    // move it
    $this->fileMover->move($file, $state);
    // mark this file in the redirect table, for later
    $this->redirectTableTS->addFile($file);

    $this->textSitemap->refreshDatabase();
    $this->textSitemap->saveTextSitemap();

    $o = [ 'file'=>$file, 'state'=>$state ];
    return $response->withJson($o);
});

$app->post('/html-import-redirects', function (Request $request, Response $response, array $args) {
    $rip = $this->redirectImportationProvider;
    $params = $request->getParsedBody();
    $data = $params['data'];
    $rip->importText($data);
    // move all the imported rows' files to the trash
    array_map(function($row) {
        $this->fileMover->move( basename($row['file']), 'trash' );
    }, $rip->getImportedRows());
    return $response->withJson([ "errors" => $rip->getImportErrors(), "successes" => $rip->getImportedRows() ]);
});
