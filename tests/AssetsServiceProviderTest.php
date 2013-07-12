<?php

namespace Gufy\tests;
use \Silex\Application;
use \Gufy\Service\Provider\AssetsServiceProvider;
class AssetsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	public function testRegister()
	{
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
		$app->get('/',function() use ($app){
			return "";
		});
		ob_start();
		$app->run();
		$content = ob_get_clean();
		// test if expected js is as same as registered js
		$this->assertEquals($expectJs, $app['assets']->getJs());


		// test if expected js is as same as registered js
		$this->assertEquals($expectCss, $app['assets']->getCss());

		// test rendered css 
		$expectedRenderedJs = '<script src="js/jquery.min.js" type="text/javascript"></script>';
		$this->assertEquals($expectedRenderedJs, $app['assets']->renderJs());
		// test rendered css
		$expectedRenderedCss = '<link href="css/app.css" type="text/css" rel="stylesheet">';
		$this->assertEquals($expectedRenderedCss, $app['assets']->renderCss());
		$content = "";
		$this->assertTrue(!empty($app['assets']->renderAssets($content)));
	}
}