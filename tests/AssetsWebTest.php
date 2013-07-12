<?php

namespace Gufy\tests;
use \Silex\Application;
use \Gufy\Service\Provider\AssetsServiceProvider;
use \Silex\WebTestCase;
class AssetsServiceProviderTest extends WebTestCase
{
	public function createApplication()
	{
		$app = require(__DIR__.'/../app.php');
		$app['debug'] = true;
		$app['exception_handler']->disable();
		return $app;
	}
	public function testRun()
	{
		$client = $this->createClient();
		$crawler = $client->request("GET", "/");

		$this->assertTrue($client->getResponse()->isOk());
	    $this->assertCount(1, $crawler->filter('script'));
	    $this->assertCount(1, $crawler->filter('link'));

	}
}