# Silex Assets Manager

It is a simple and useful assets manager for silex. 
All you need to do is simple, to register some files before anything else just register the service provider and provide
some parameters within it. For example:

	<?php

	$app = new Applicaton;
	$app->register(\Gufy\AssetsServiceProvider,array(
		'assets.js'=>array("... list of javascripts files ..."),
		'assets.css'=>array("... list of css files ..."),
	));

	?>

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