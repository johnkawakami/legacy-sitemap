<?php
// Application middleware


// lazy cors
$app->add(function ($req, $res, $next) {
    $ua = $req->getHeader('user-agent')[0];
    $response = $next($req, $res);
    return $response
        ->withHeader('User-Agent', $ua)
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, User-Agent, Authorization')
        ->withHeader('Access-Control-Allow-Origin', '*');
});

// API key check
$app->add(function ($req, $res, $next) {
    $auth = $req->getHeader('authorization')[0];
    $parts = explode(' ', $auth);
    if ($parts[1] === getenv('LEGACY_SITEMAP_API_KEY')) {
        $response = $next($req, $res);
        return $response;
    } else {
        return $res->withStatus(403)->withJson([ 'status'=>403, 'error'=>'Bad Authorization' ]);
    }
});
