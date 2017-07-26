<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use \Firebase\JWT\JWT;

Request::setTrustedProxies(array('0.0.0.0'));
$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    //if($request->getMethod() == "OPTIONS") {

        $response->headers->set("Cache-Control", "no-cache");
        $response->headers->set("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type, Accept");
        $response->headers->set("Access-Control-Max-Age", "1728000");
    //}
});

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});




$app->match('/', function (Request $request) use ($app) {


    $url =  $request->request->get('url');

    if (
        strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit/") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false
    ) {
        return $app['twig']->render('share.html.twig', array(
            'url' => "http://dtservrest.herokuapp.com/share/index_dev.php",
            'type' => "article",
            'title' => "Titulo de prueba",
            'description' => "Una descripcion cualquiera",
            'image' => "http://dtservrest.herokuapp.com/api/photo"
        ));

    }
    else {
        return $app->redirect('http://dtodo.herokuapp.com/');
    }

});


$app->match('/establishments/{id}', function (Request $request) use ($app) {


    $url =  $request->request->get('url');

    if (
        strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit/") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false
    ) {
        return $app['twig']->render('share.html.twig', array(
            'url' => "http://dtservrest.herokuapp.com/share/",
            'type' => "article",
            'title' => "Titulo de prueba",
            'description' => "Una descripcion cualquiera",
            'image' => "http://dtservrest.herokuapp.com/api/photo"
        ));

    }
    else {
        return $app->redirect('http://dtodo.herokuapp.com/');
    }

});

$app->error(function (\Exception $e, $code) use ($app) {

    // commented for testing purposes
    /*if ($app['debug']) {
        return;
    }*/

    if ($code == 404) {

     /*   $loader = $app['dataloader'];
        $data = array(
            'global' => $loader->load('global'),
            'common' => $loader->load('common', $app['locale']),
            'header' => $loader->load('header', $app['locale']),
            'footer' => $loader->load('footer', $app['locale'])
        );

        return new Response( $app['twig']->render('404.html.twig', array( 'data' => $data )), 404);
    */
        return $app->redirect('/');
    }

    return new Response('We are sorry, but something went terribly wrong.', $code);

});

