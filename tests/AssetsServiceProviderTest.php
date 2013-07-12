<?php

namespace Gufy\tests;
use \Silex\Application;
use \Gufy\Service\Provider\AssetsServiceProvider;
class AssetsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	public $app;
	public function setUp()
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
			'assets.options'=>array(
				'baseUrl'=>'http://localhost/my-apps/'
			),
		));
		$app->get('/',function() use ($app){
			return "";
		});

		// ignore for output some things
		ob_start();
		$app->run();
		$content = ob_get_clean();	
		return $this->app = $app;
	}
	public function testRegister()
	{
		$app = $this->app;
		$host = 'http://localhost/my-apps/';
		$expectJs = array(
			'jquery.min.js'=>$host.'js/jquery.min.js'
		);
		$expectCss = array(
			'app.css'=>$host.'css/app.css'
		);
		// test if expected js is as same as registered js
		$this->assertEquals($expectJs, $app['assets']->getJs());


		// test if expected js is as same as registered js
		$this->assertEquals($expectCss, $app['assets']->getCss());

		// test rendered css 
		$expectedRenderedJs = '<script src="'.$host.'js/jquery.min.js" type="text/javascript"></script>';
		$this->assertEquals($expectedRenderedJs, $app['assets']->renderJs());
		// test rendered css
		$expectedRenderedCss = '<link href="'.$host.'css/app.css" type="text/css" rel="stylesheet">';
		$this->assertEquals($expectedRenderedCss, $app['assets']->renderCss());
		$content = "";
		$this->assertTrue(!empty($app['assets']->renderAssets($content)));
	}
	public function testAddFile()
	{
		$app = $this->app;
		$host = 'http://localhost/my-apps/';
		$app['assets']->registerJs('js/jquery.ui.js');
		$this->assertEquals(array('jquery.min.js'=>$host.'js/jquery.min.js','jquery.ui.js'=>$host.'js/jquery.ui.js'),$app['assets']->getJs());

		$app['assets']->registerCss('css/hello.css');
		$this->assertEquals(array('app.css'=>$host.'css/app.css','hello.css'=>$host.'css/hello.css'),$app['assets']->getCss());		
	}

	public function testResetAssetByType()
	{
		$app = $this->app;
		$app['assets']->reset('js');
		$this->assertEquals(array(), $app['assets']->getJs());

		$app['assets']->reset('css');
		$this->assertEquals(array(), $app['assets']->getCss());
	}

	public function testResetAll()
	{

		$app = $this->app;
		$app['assets']->reset();

		$this->assertEquals(array(), $app['assets']->getJs());
		$this->assertEquals(array(), $app['assets']->getCss());
	}

	public function testPreConfiguredOptions()
	{
		$app = $this->app;
		$host = 'http://localhost/my-apps/';
		$this->assertEquals($host,$app['assets']->getOption('baseUrl'));
		$this->assertEquals(array('jquery.min.js'=>$host.'js/jquery.min.js'),$app['assets']->getJs());
		$this->assertEquals(array('app.css'=>$host.'css/app.css'),$app['assets']->getCss());	
	}

	public function testBaseUrl()
	{
		$app = $this->app;
		$app['assets']->setOption('baseUrl', 'http://localhost/hello/');
		$this->assertEquals('http://localhost/hello/',$app['assets']->getOption('baseUrl'));
		$this->assertEquals(array('jquery.min.js'=>'http://localhost/hello/js/jquery.min.js'),$app['assets']->getJs());
		$this->assertEquals(array('app.css'=>'http://localhost/hello/css/app.css'),$app['assets']->getCss());
	}
}