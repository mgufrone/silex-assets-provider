<?php

include 'vendor/autoload.php';
use \Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use \Gufy\Service\Provider\AssetsServiceProvider;

$app = new Application;

$expectJs = array(
	'js/jquery.min.js'
);
$expectCss = array(
	'css/app.css'
);
$app->register(new AssetsServiceProvider, array(
	'assets.js'=>$expectJs,
	'assets.css'=>$expectCss,
));
$app->get('/',function(Request $request) use ($app){
	return "Hello World";
});
$app->run();
return $app;