# Silex Assets Manager

A simple and useful assets manager. 

# Installation

Because Silex is using composer as its dependency manager, so does this Assets Manager. just provide this line on your composer.json

	{
		...
		"require":{
			...
			"gufy/assets-service":"dev-master"
			...
		}
		...
	}

Or refer to https://packagist.org/packages/gufy/assets-services for more detailed available versions

It is a simple and useful assets manager for silex. 
All you need to do is simple, to register some files before anything else just register the service provider and provide
some parameters within it. For example:

	<?php

	$app = new Applicaton;
	$app->register(new \Gufy\Service\Provider\AssetsServiceProvider,array(
		'assets.js'=>array("... list of javascripts files ..."),
		'assets.css'=>array("... list of css files ..."),
	));

	?>

## Available Options

When you register this provider, there are some configurations that you can provide on it. Here is the configuration options
	
	<?php
	array(
		'assets.options'=>array(
			'baseUrl'=>'... your base url ...',// base url will prepend to all of the assets, i.e if you provide baseUrl with 'http://localhost' and you have javascript file 'js/app.js' then, it will output as http://localhost/js/app.js
			// if you consider about combine and compress your assets, please provide this.
			'combine'=>true, // set assets manager in combine mode
			'basePath'=>'', // base directory path of your assets
			'scriptPath'=>'', // set this variable with directory root of your webapp
			'cachePath'=>'', // directory who handle your cached assets files
			'cacheFileName'=>'', // custom file name of the cache file, leave blank or remove it and it will generated random string

		), 
	);

Or if you prefer setting it up on the fly or in the specific response only, you can use this

	<?php
	$app['assets']->setOption('optionName','...value...');
	// example
	$app['assets']->setOption('baseUrl','http://localhost/hello-world/');


## Add single assets

If you want to add files on the controller or specific response only, you can do like this. 
	
	<?php
	$app->get('/',function(Request $request) use($app){

		// for javascript file
		$app['assets']->registerJs('... your file name ...');

		// for css file		
		$app['assets']->registerCss('... your file name ...');
		return "something";
	});

## Add custom Style or Javascript on the fly

If you want to add custom style or javascript on the fly, use this. 
	
	<?php
	$app->get('/',function(Request $request) use($app){

		// for javascript file
		$app['assets']->customJs('... $id ..','... $scripts ...');

		// for css file		
		$app['assets']->customCss('... $styles ...');
		return "something";
	});


## Reset the whole assets

For reset by type, simply use this function
	
	<?php
	// reset javascript files 
	$app['assets']->reset('js');

	// reset css files
	$app['assets']->reset('css');

Or if you wanna reset the whole assets, it's simple use this. 
	
	<?php
	// reset the whole files
	$app['assets']->reset();


## Contribution

If you want to contribute on this repo, just fork this repo or give me some feedback from the issue feature.

