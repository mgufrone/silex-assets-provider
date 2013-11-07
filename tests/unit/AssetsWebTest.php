<?php

namespace Gufy\tests;
use \Silex\Application;
use \Gufy\Service\Provider\AssetsServiceProvider;
use \Silex\WebTestCase;
class AssetsWebTest extends WebTestCase
{
	public function createApplication()
	{
		$app = require(__DIR__.'/../app.php');
		// $app['assets.options']['combine'] =false;
		$app['debug'] = true;
		$app['exception_handler']->disable();
		return $app;
	}
	public function testRun()
	{
		$app = $this->app;
		$customCss = "body{background:#333}";
		$app['assets']->customCss("hello",$customCss);
        $customJs = 'var hello="monster";';
        $app['assets']->customJs("hello-world", $customJs);
        $this->assertTrue( in_array($customJs, $app['assets']->getCustomJs()) );

        $customJs2 = 'var hello="hai";';
        $app['assets']->customJs("hello-world", $customJs2, $app['assets']::ON_BODY);
        $this->assertTrue(in_array($customJs2, $app['assets']->getCustomJs($app['assets']::ON_BODY)));
       
		$client = $this->createClient();
		$crawler = $client->request("GET", "/");
		$content = $client->getResponse()->getContent();

		$this->assertContains($customJs, $content);   
		$this->assertTrue($client->getResponse()->isOk());
		$this->assertContains($customJs2, $content); 
		$this->assertContains($customCss, $content);

	    // $this->assertCount(1, $crawler->filter('script'));
	    // $this->assertCount(1, $crawler->filter('link'));

	}

}