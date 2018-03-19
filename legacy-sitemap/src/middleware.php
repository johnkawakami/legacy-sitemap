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

