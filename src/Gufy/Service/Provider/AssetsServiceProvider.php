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
	private $options = array();

	/**
	* variable who take care of all registered javascripts files
	* @var $js
	*/
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

		// Registering preloaded options
		$options = isset($app['assets.options'])?$app['assets.options']:array();
		if(!empty($options))
		array_walk($options, function($optionValue, $optionName) use($assets){
			$assets->setOption($optionName, $optionValue);
		});	

		// Registering preloaded javascript files
		$js = isset($app['assets.js'])?$app['assets.js']:array();
		if(!empty($js))
		array_walk($js, function($value) use($assets){
			$assets->registerJs($value);
		});

		// Registering preloaded css files
		$css = isset($app['assets.css'])?$app['assets.css']:array();
		if(!empty($css))
		array_walk($css, function($value) use($assets){
			$assets->registerCss($value);
		});

		$app->after(function(Request $request, Response $response) use($app, $assets){
			$content = $response->getContent();
			$assets->renderAssets($content);
			$response->setContent($content);
			return $response;
		});
	}
	/**
	* begin rendering assets when response is valid and has been processed.
	* this is automatically triggered, so you don't need to trigger it manually
	* @param String $content
	* @return String $content content that has been preprocessed with registered assets and returned back
	*/
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

	/**
	* Begin processing registering Javascripts if available
	* @return String $result if app has javascript files, all registered javascripts will be converted as readable html script
	*/
	public function renderJs()
	{
		$result = "";
		$js = $this->getJs();
		if(!empty($js))
		array_walk($js, function($value) use (&$result){
			$result .= '<script src="'.$value.'" type="text/javascript"></script>';
		});
		return $result;
	}

	/**
	* Begin processing registering Css Files if available
	* @return String $result if app has css files, all registered css will be converted as readable html script
	*/
	public function renderCss()
	{
		$result = "";
		$css = $this->getCss();
		if(!empty($css))
		array_walk($css, function($value) use (&$result){
			$result .= '<link href="'.$value.'" type="text/css" rel="stylesheet">';
		});
		return $result;
	}

	/**
	* Get all registered javascripts
	* @return array $this->js all available javascripts
	*/
	public function getJs()
	{
		$js = $this->js;
		$options = $this->options;
		array_walk($js, function(&$value) use($options){
			if(!empty($options['baseUrl']))
				$value = $options['baseUrl'].$value;
		});
		return $js;
	}

	/**
	* Get all registered css files
	* @return array $this->js all available javascripts
	*/
	public function getCss()
	{
		$css = $this->css;
		$options = $this->options;
		array_walk($css, function(&$value) use($options){
			if(!empty($options['baseUrl']))
				$value = $options['baseUrl'].$value;
		});
		return $css;
	}

	/**
	* Reset assets
	* @param string $filePath register a single js file to assets manager
	* @return AssetsServiceProvider this is useful to make a method-chaining 
	*/
	public function registerJs($filePath="")
	{
		if(!empty($filePath))
		$this->js[basename($filePath)] = $filePath;
		return $this;
	}

	/**
	* Reset assets
	* @param string $filePath register a single css file to assets manager
	* @return AssetsServiceProvider this is useful to make a method-chaining 
	*/
	public function registerCss($filePath="")
	{
		if(!empty($filePath))
		$this->css[basename($filePath)] = $filePath;
		return $this;
	}

	/**
	* Reset assets
	* @param string $type provide type if you want to reset specific asset type, or leave blank if you reset the whole assets
	* @return AssetsServiceProvider this is useful to make a method-chaining 
	*/
	public function reset($type='')
	{
		if(!empty($type))
		{
			$this->$type = array();
		}
		else
		{
			$this->js = array();
			$this->css = array();
		}
		return $this;
	}

	public function getOption($optionName)
	{
		return isset($this->options[$optionName])?$this->options[$optionName]:'';
	}

	public function setOption($optionName, $optionValue)
	{
		$this->options[$optionName] = $optionValue;
		return $this;
	}
}