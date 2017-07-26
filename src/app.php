<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

$app = new Application();
$app->register(new RoutingServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider(),array(
    'twig.path' => __DIR__.'/templates',
    'twig.options' => array(
        'cache' => false
    )
));


//var_dump($app['twig']);

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath().'/'.ltrim($asset, '/');
    }));

    return $twig;
});
/*
$app['twig.options'] = array(
    'cache' => false
);*/

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
//var_dump($url);
if(!empty($url['path'])) {

    $server = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $db = substr($url["path"], 1);

    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver' => 'pdo_mysql',

                'host' => $server,
                'dbname' => $db,
                'user' => $username,
                'password' => $password,

                'charset' => 'utf8mb4',
                'options' => array(
                    1002 => "SET NAMES utf8",
                    1000 => true
                )
            ),
        )
    );

}else{



    if($_SERVER['HTTP_HOST'] == "dtodoaqui.com"){
        //$ini = parse_ini_file(substr(__DIR__, 7, -9).'/config/app.ini');
        $ini = parse_ini_file(__DIR__.'/../config/app.ini');
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver' => 'pdo_mysql',
                'host' => $ini['db_host'],
                'dbname' => $ini['db_name'],
                'user' => $ini['db_user'],
                'password' => $ini['db_password'],
                'charset' => 'utf8mb4',
                'options' => array(
                    1002 => "SET NAMES utf8",
                    1000 => true
                )
            ),
        )
    );

    }
    else{
        $ini = parse_ini_file(__DIR__.'/../config/app.ini');


	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		    'db.options' => array(
		        'driver' => 'pdo_mysql',
		        'host' => $ini['db_host'],
		        'dbname' => $ini['db_name'],
		        'user' => $ini['db_user'],
		        'password' => $ini['db_password'],
		        'charset' => 'utf8mb4',
		        'options' => array(
		            1002 => "SET NAMES utf8",
		            1000 => true
		        )
		    ),
		)
	    );
    }


    





}

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/dev.log',
    'monolog.level' => "WARNING"
));

$app->register(new Silex\Provider\SwiftmailerServiceProvider(),array(
    'swiftmailer.options' => array(
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'username' => 'unmailfulano@gmail.com',
        'password' => '996666567',
        'encryption' => 'ssl',
        'auth_mode' => 'login'/*
        'host' => 'localhost',
        'port' => 25,*/
    )
));
//$app['swiftmailer.use_spool'] = false;


$app['jwt.secret'] = "@itsudatte";
$app['jwt.leeway'] = 60;


return $app;
