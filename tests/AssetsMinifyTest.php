<?php

namespace Gufy\tests;
use \Silex\Application;
use \Gufy\Service\Provider\AssetsServiceProvider;
use \Silex\WebTestCase;
class AssetsServiceProviderTest extends WebTestCase
{
	public $app;
	public function createApplication()
	{

		$app = include __DIR__.'/app.php';
		return $app;
	}
	public function testMinify()
	{
		$client = $this->createClient();
		$crawler = $client->request("GET", "/");

		$app = $this->app;
		$this->assertClassHasAttribute('cached',get_class($app['assets']));
		$this->assertTrue($app['assets']->isCombineEnabled());
		
		// $this->assertFileEquals($app['assets']->getCacheFile('js'), __DIR__.'/cache/compare.js');
	}
	public function testDependency()
	{

	}
}