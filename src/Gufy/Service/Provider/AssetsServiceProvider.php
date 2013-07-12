<?php
/**
* Assets Service Provider for Silex Micro Framework
* @author mgufron
* @link http://mgufron.com
* @since 1.0
* @package gufy
*/
namespace Gufy\Service\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AssetsServiceProvider implements ServiceProviderInterface
{
	public $js=array();

	public $css=array();

	public function register(Application $app)
	{
		$assets = $this;
		$app['assets'] = $app->share(function() use($app, $assets){
			
			return $assets;
		});
	}
	public function boot(Application $app)
	{
		$assets = $this;
		$assets->js = isset($app['assets.js'])?$app['assets.js']:array();
		$assets->css = isset($app['assets.css'])?$app['assets.css']:array();
		$app->after(function(Request $request, Response $response) use($app, $assets){
			$content = $response->getContent();
			$assets->renderAssets($content);
			$response->setContent($content);
			return $response;
		});
	}

	public function renderAssets(&$content)
	{
		$js = $this->renderJs();
		$css = $this->renderCss();
		if(!empty($css))
		{
			if(strpos($content, "</head>")>0)
				$content = str_replace("</head>","####replace-css-here####</body>",$content);
			else
				$content = "####replace-css-here####".$content;
			$content = str_replace("####replace-css-here####",$css,$content);
		}
		if(!empty($js))
		{
			if(strpos($content, "</body>")>0)
				$content = str_replace("</body>","####replace-js-here####</body>",$content);
			else
				$content .= "####replace-js-here####";
			$content = str_replace("####replace-js-here####",$js,$content);
		}
		return $content;
	}

	public function renderJs()
	{
		$result = "";
		array_walk($this->js, function($value) use (&$result){
			$result .= '<script src="'.$value.'" type="text/javascript"></script>';
		});
		return $result;
	}
	public function renderCss()
	{
		$result = "";
		if(!empty($this->css))
		array_walk($this->css, function($value) use (&$result){
			$result .= '<link href="'.$value.'" type="text/css" rel="stylesheet">';
		});
		return $result;
	}

	public function getJs()
	{
		return $this->js;
	}
	public function getCss()
	{
		return $this->css;
	}

}