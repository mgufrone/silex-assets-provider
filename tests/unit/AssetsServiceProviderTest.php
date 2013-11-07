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
                $app['assets']->setOption('baseUrl',$host);
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
		$result = $app['assets']->renderAssets($content);
		$this->assertTrue(!empty($result));
	}
	public function testAddFile()
	{
		$app = $this->app;
		$host = 'http://localhost/my-apps/';
                $app['assets']->setOption('baseUrl',$host);
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
                $app['assets']->setOption('baseUrl',$host);
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
    public function testPositioning()
    {
        $app = $this->app;
        $host = 'http://localhost/hello/';
//            Js default positioning
        $app['assets']->setOption('baseUrl',$host);
        $newJs = $host.'js/monster.js';
        $app['assets']->registerJs('js/monster.js');
        $this->assertEquals('http://localhost/hello/',$app['assets']->getOption('baseUrl'));
        $this->assertTrue(in_array($newJs, $app['assets']->getJs()));
        
//            Js Positioning
        $js = 'js/app.js';
        $app['assets']->registerJs($js, $app['assets']::ON_BODY);
        $this->assertTrue(in_array($host.$js,$app['assets']->getJs($app['assets']::ON_BODY)));
    }
    public function testCustomScriptsAndCss()
    {

        $app = $this->app;
        $host = 'http://localhost/hello/';
        // Javascript additional/custom script testing
        $app['assets']->setOption('baseUrl',$host);
        $customJs = 'var hello="monster";';
        $app['assets']->customJs("hello-world", $customJs);
        $this->assertEquals('http://localhost/hello/',$app['assets']->getOption('baseUrl'));
        $this->assertTrue(in_array($customJs, $app['assets']->getCustomJs()));
        
        $customJs = 'var hello="hai";';
        $app['assets']->customJs("hello-world", $customJs, $app['assets']::ON_BODY);
        $this->assertTrue(in_array($customJs, $app['assets']->getCustomJs($app['assets']::ON_BODY)));


        // CSS additional/custom script testing
        $customCss = "body{background:#fefefe;}";
        $app['assets']->customCss("hello-css",$customCss);
        $this->assertTrue(in_array($customCss,$app['assets']->getCustomCss()));
    }
}