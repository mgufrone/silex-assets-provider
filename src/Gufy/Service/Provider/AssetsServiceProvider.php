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
	/**
	* All additional options will be registered here
	* @var array $options
	*/
	private $options = array();

	/**
	* Core url for assets manager
	* @var string $options
	*/
	private $coreUrl;

	/**
	* variable who take care of all registered javascripts files
	* @var $js
	*/
	public $js=array();

	/**
	* variable who take care of all registered stylesheets files
	* @var array $css
	*/
	public $css=array();

	/**
	* variable who take care of all registered cached files by its type
	* @var array $cached
	*/
	public $cached=array();

	// implementation of Silex Service Provider register method
	public function register(Application $app)
	{
		$assets = $this;
		$app['assets'] = $app->share(function() use($app, $assets){
			
			return $assets;
		});
	}
	
	// implementation of Silex Service Provider register method
	public function boot(Application $app)
	{
		$assets = $this;
		$app->before(function(Request $request) use($app, $assets){
			$baseUrl = rtrim($request->getScheme().'://'.$request->getHttpHost().(empty($request->getBasePath())?'/':$request->getBasePath()),'/');
			$assets->setCoreUrl($baseUrl);
			return $assets;
		});

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
	* Set core url of the assets manager
	* @param String $url url name. You don't need to use this function, because it's only used for registering core url of the assets
	* @return self-object it is useful for method chaining
	*/
	public function setCoreUrl($url='')
	{
		$this->coreUrl = $url;
		return $this;
	}
	public function setDefaultOptions(Application $app)
	{

		/*$request = $app['request'];
		if(empty($options['baseUrl']))
			$this->setOption('baseUrl',$request->getScheme().'://'.$request->getHttpHost().$request->getBasePath());*/
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
		if($this->isCombineEnabled())
			$this->combine('js', $js);

		// avoiding duplicate assets
		$js = array_flip(array_flip($js));
		array_walk($js, function(&$value) use($options){
			if(!empty($options['baseUrl'])&&strpos($value,'http://')===false)
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
		if($this->isCombineEnabled())
			$this->combine('css', $css);

		// avoiding duplicate assets
		$css = array_flip(array_flip($css));

		array_walk($css, function(&$value) use($options){
			if(!empty($options['baseUrl'])&&strpos($value,'http://')===false)
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

	/**
	* Get registered option
	* @param String $optionName option name you want to retrieve
	* @return mixed value of $optionName if it has been registered
	*/
	public function getOption($optionName)
	{
		return isset($this->options[$optionName])?$this->options[$optionName]:'';
	}

	/**
	* Set option of service provider
	* @param String $optionName option name you want to register
	* @param mixed $optionValue option value you want to register by optionName key
	* @return self-object it is useful for method chaining
	*/
	public function setOption($optionName, $optionValue)
	{
		$this->options[$optionName] = $optionValue;
		return $this;
	}

	/**
	* For test purpose, it is used to get directly what is the file url of the cache file
	* @return boolean true if combine mode option is available
	*/
	public function getCacheFile($type='js')
	{
		return $this->cached[$type];
	}

	/**
	* Checking if combine mode is enabled
	* @return boolean true if combine mode option is available
	*/
	public function isCombineEnabled()
	{
		return $this->getOption('combine');
	}

	/**
	* Combining cache path 
	* @param String $type type files that will be combined
	* @param String $files files that will be combined and cached
	* @return void
	*/
	public function combine($type, &$files)
	{
		$basePath = realpath($this->getOption('basePath'));
		if(empty($basePath))return $files;
		$cacheName = $this->createCachePath($this->getOption('cacheFileName')?$this->getOption('cacheFileName'):$this->createFileName($type,$files)).'.'.$type;
		$this->setOption('coreUrl',$this->coreUrl);
		$minifier = new AssetsMinifier($this->options);
		$cacheUrl = $minifier->compress($type, $files, $cacheName);
		if(!empty($files))
		array_walk($files, function(&$value, $key) use($cacheUrl) {
			$value = $cacheUrl;
		});
		$this->cached[$type] = $cacheUrl;
	}

	/**
	* Generate random cache file name, if cacheFileName doesn't represent on the option variables
	* @param String $createFileName file name of the cache that will be used as the combined files
	* @return String return random string as the cache filename
	*/
	private function createFileName($type,$files)
	{
		return substr(md5($type.implode(',',$files)),0,7);
	}

	/**
	* Generate cache path 
	* @param String $cacheFileName file name of the cache that will be used as the combined files
	* @return String return cacheFileName prepended with cachePath
	*/
	private function createCachePath($cacheFileName)
	{
		return $this->getOption('cachePath').$cacheFileName;
	}
}