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
	* for javascript positioning, whether it will be placed before </head> tag or </body> tag
	* it is useful when you consider about performance load.
	* @var int ON_HEAD position marker of javascript placement before </head>
	*/
	const ON_HEAD=1;

	/**
	* Position marker of javascript placement before </body>
	* @var int ON_HEAD position marker of javascript placement before </head>
	*/
	const ON_BODY=2;
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
	* All custom javascript contents will be added here. 
	* It need position and identifier to make a unique script, and you can replace the script by its id
	* @var array $customJs
	*/
	public $customJs=array();
	/**
	* variable who take care of all registered stylesheets files. 
	* @var array $css
	*/
	public $css=array();

	/**
	* All custom css contents will be added here.
	* @var array $customJs
	*/
	public $customCss=array();

	/**
	* variable who take care of all registered cached files by its type
	* @var array $cached
	*/
	public $cached=array();

	private $groups=array();
	private $attached_groups=array();

	// implementation of Silex Service Provider register method
	public function register(Application $app)
	{
		$assets = $this;
		$app['assets'] = $app->share(function() use($app, $assets){
			
			return $assets;
		});
	}

	// implementation of Silex Service Provider boot method
	public function boot(Application $app)
	{
		$assets = $this;
		$app->before(function(Request $request) use($app, $assets){
			$baseUrl = rtrim($request->getScheme().'://'.$request->getHttpHost().$request->getBasePath()).'/';
			$assets->setCoreUrl($baseUrl);
			$assets->setOption('baseUrl', $baseUrl);
			$assets->setOption('jsPosition',$assets::ON_BODY);
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
			// print_r($response->headers->get('content_type'));
			if($response->headers->get('content_type')!='application/json')
			{
				$content = $response->getContent();
				$assets->renderAssets($content);
				$response->setContent($content);
			}
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
	/**
	* begin rendering assets when response is valid and has been processed.
	* this is automatically triggered, so you don't need to trigger it manually
	* @param String $content
	* @return String $content content that has been preprocessed with registered assets and returned back
	*/
	public function renderAssets(&$content)
	{
		$this->prepareAssets();
		$js = $this->renderJs();
		$css = $this->renderCss();
		$bodyJs = $this->renderJs(self::ON_BODY);
		if(!empty($css))
		{
			if(strpos($content, "</head>")>0)
				$content = str_replace("</head>","####replace-css-here####</head>",$content);
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
                
		if(!empty($bodyJs))
		{
			if(strpos($content, "</body>")>0)
				$content = str_replace("</body>","####replace-js-here####</body>",$content);
			else
				$content .= "####replace-js-here####";
			$content = str_replace("####replace-js-here####",$bodyJs,$content);
		}
                
		return $content;
	}

	/**
	* Begin processing registering Javascripts if available
	* @return String $result if app has javascript files, all registered javascripts will be converted as readable html script
	*/
	public function renderJs($position=self::ON_HEAD)
	{
		$result = "";
		$js = $this->getJs($position);
		if(!empty($js))
		array_walk($js, function($value) use (&$result){
			$result .= '<script src="'.$value.'" type="text/javascript"></script>';
		});
		$custom = $this->getCustomJs($position);
		if(!empty($custom))
		{
			$customJs = '<script type="text/javascript" id="silex-assets-service-js-'.substr(md5(time()),0,5).'">';
			if($custom !== array())
				array_walk($custom, function($value) use (&$customJs){
					$customJs .= $value."\n";
				});
			$customJs .= "</script>";
			$result .= $customJs;
		}
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
		$custom = $this->getCustomCss();
		if(!empty($custom))
		{
			$customCss = '<style type="text/css" rel="stylesheet" id="silex-assets-service-css-'.substr(md5(time()),0,5).'">';
			if($custom !== array())
				array_walk($custom, function($value) use (&$customCss){
					$customCss .= $value."\n";
				});
			$customCss .= "</style>";
			$result .= $customCss;
		}
		return $result;
	}

	/**
	* Get all registered javascripts
	* @return array $this->js all available javascripts
	*/
	public function getJs($position=self::ON_HEAD)
	{
		if($this->js===array())
			return array();
		$js = isset($this->js[$position])&&!empty($this->js[$position])?$this->js[$position]:array();
		if($js===array())
			return $js;
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
	public function registerJs($filePath="",$position=self::ON_HEAD, $package_name='')
	{
		if(!empty($filePath))
		{
			if(!empty($package_name))
				$package_name.=':';

			$this->js[$position][$package_name.basename($filePath)] = $filePath;
		}
		// print_r($this->js);
		return $this;
	}

	/**
	* Add custom script on your apps. It is useful when you want to attach some custom script on the fly.
	* @param string $id identity name of the script
	* @param string $script script you want to attach
	* @param int $position position where you want to place you script. it must be @link(ON_BODY) or @link(ON_HEAD)
	* @return AssetsServiceProvider so you can use it as method-chaining mode
	*/
	public function customJs($id, $script="", $position=self::ON_HEAD)
	{
		if(!isset($this->customJs[$position]))
			$this->customJs[$position] = array();
		$this->customJs[$position][$id] = $script;
		return $this;
	}

	/**
	* It is used to get custom javascript by reserved position. 
	* @param int $position position where you want to place you script. it must be @link(ON_BODY) or @link(ON_HEAD)
	* @return array if $position on $customJs is available it will return as is, if not it just return an empty array
	*/
	public function getCustomJs($position=self::ON_HEAD)
	{
		return isset($this->customJs[$position])?$this->customJs[$position]:array();
	}

	/**
	* Reset assets
	* @param string $filePath register a single css file to assets manager
	* @return AssetsServiceProvider this is useful to make a method-chaining 
	*/
	public function registerCss($filePath="",$package_name='')
	{
		if(!empty($filePath))
		{
			if(!empty($package_name))
				$package_name .= ':';
			$this->css[$package_name.basename($filePath)] = $filePath;
		}
		return $this;
	}

	/**
	* Register custom style on your apps. It is useful when you want to attach some custom style on the fly.
	* @param string $id identity name of the style
	* @param string $css style you want to attach
	* @return AssetsServiceProvider so you can use it as method-chaining mode
	*/
	public function customCss($id, $css="")
	{
		if(func_num_args()==1)
		{
			$css = $id;
			$id = uniqid();
		}
		$this->customCss[$id] = $css;
		return $this;
	}

	/**
	* It is used to get all registered custom styles
	* @return array $this->customCss 
	*/
	public function getCustomCss()
	{
		return $this->customCss;
	}

	/**
	* Reset assets
	* @param string $type provide type if you want to reset specific asset type, or leave blank if you reset the whole assets
	* @return AssetsServiceProvider this is useful to make a method-chaining 
	*/
	public function reset($type='',$group_name='')
	{
		if(!empty($type))
		{
			if(!empty($group_name))
			{
				$lib = $this;
				if(!empty($this->$type))
				array_walk($this->$type, function($values, $key0) use($group_name, $type, $lib){
					// print_r($values);
					// print $type;
					if($type == 'js')
					{
						// print_r($values);

						array_walk($values, function($value, $key1) use($group_name, $key0, $type, $lib){
							$keys = explode(':',$key1);
							$registered = $keys[0];
							if(!empty($registered) && $registered == $group_name)
							{
								$cache = $lib->$type;
								unset($cache[$key0][$key1]);
								$lib->$type = $cache;
								// unset($lib->$type[$key0][$key1]);
							}
						});
					}
					else
					{
						$keys = explode(':',$key0);
						$registered = $keys[0];
						if(!empty($registered) && $registered == $group_name)
						{
							$cache = $lib->$type;
							unset($cache[$key0]);
							$lib->$type = $cache;
							// unset($lib->$type[$key0]);
						}
					}
				});
			}
			else
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

	/**
	* @param string $group_name group name that will be registered
	* @param array $group_contents array of contents as a registered package contents
	* @param boolean $auto_attach auto registered to assest if set to true
	* @return self object
	*/
	public function attach($group_name, $group_contents, $auto_attach=false)
	{
		$lib = $this;
		$count_args = func_num_args();
		if($count_args==1)
			$this->attached_groups[] = $group_name;
		else
		{
			$this->groups[$group_name] = $group_contents;
			if($auto_attach)
				$this->attached_groups[] = $group_name;
		}
			
		return $this;
	}

	/**
	* @param string $group_name group name that will be detached from registered asset groups
	* @return self object
	*/
	public function detach($group_name)
	{

		if(isset($this->attached_groups[$group_name]))
			unset($this->attached_groups[$group_name]);
		if(isset($this->groups[$group_name]))
			unset($this->groups[$group_name]);
		$this->reset('js',$group_name);
		$this->reset('css',$group_name);
		return $this;
	}

	/**
	* preparing auto attached groups before registering any asset files
	* @return self object
	*/
	private function prepareAssets()
	{
		$lib = $this;
		array_walk($lib->attached_groups, function($group_name) use($lib){
			$group_contents = $lib->groups[$group_name];
			array_walk($group_contents, function($value, $key) use($lib, $group_name){
				switch($key)
				{
					case 'css':
					array_walk($value, function($value, $key) use($lib, $group_name){
						$lib->registerCss($group_name.'/'.$value,$group_name);
					});
					break;
					case 'js':
					array_walk($value, function($value, $key) use($lib, $group_name){
						$lib->registerJs($group_name.'/'.$value,$lib->getOption('jsPosition'),$group_name);
					});
					break;
				}
			});
		});
		return $this;
	}
}