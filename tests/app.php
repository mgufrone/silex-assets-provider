<?php

include 'vendor/autoload.php';
use \Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use \Gufy\Service\Provider\AssetsServiceProvider;

$app = new Application;

$expectJs = array(
	'js/jquery.min.js',
	'js/jquery.ui.min.js'
);
$expectCss = array(
	'css/app.css'
);
$app->register(new AssetsServiceProvider, array(
	'assets.js'=>$expectJs,
	'assets.css'=>$expectCss,
	'assets.options'=>array(
		'baseUrl'=>'http://localhost/my-apps/',
		'basePath'=>__DIR__.'/assets/',
		'cachePath'=>__DIR__.'/cache/',
		'scriptPath'=>__DIR__,
		'cacheFileName'=>'cached',
		'combine'=>true,
	),
));
$app->get('/',function() use ($app){
	return "";
});
return $app;