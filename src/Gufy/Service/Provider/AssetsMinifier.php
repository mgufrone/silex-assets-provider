<?php
/**
* Assets Minifier for Silex Micro Framework
* @author mgufron
* @link http://mgufron.com
* @since 1.0
* @package gufy
*/

namespace Gufy\Service\Provider;

class AssetsMinifier 
{	
	/**
	* Base path of the asset file
	* @var string $basePath
	*/
	public $basePath;
	/**
	* executed (document root) file location
	* @var string $basePath
	*/
	public $scriptPath;
	/**
	* core Url location
	* @var string $coreUrl
	*/
	public $coreUrl;

	public function __construct($options=array())
	{
		foreach($options as $key=>$value)
			$this->$key = $value;
	}

	/**
	* minifying css contents
	* @param string $str contents of css
	* @return string return compressed css contents
	*/
	public function minifyCSS($str)
	{
		$find = array('!/\*.*?\*/!s',
			"/\n/",
			'/\n\s*\n/',
			'/[\n\r \t]/',
			'/ +/',
			'/ ?([,:;{}]) ?/',
			'/;}/'
		);
		$repl = array('',
			'',
			"\n",
			' ',
			' ',
			'$1',
			'}'
		);
		return preg_replace($find, $repl, $str);
	}

	/**
	* minifying javascripts contents
	* @param string $str contents of javascripts
	* @return string return compressed js contents
	*/
	public function minifyJS($str)
	{
		return preg_replace(
			array(
				'!/\*.*?\*/!s', 
				"/\n/",
				"/\n\s+/", 
				"/\n(\s*\n)+/", 
				"!\n//.*?\n!s", 
				"/\n\}(.+?)\n/",
				"/;\n/"
			), array(
				'', 
				'', 
				"\n", 
				"\n", 
				"\n", 
				"}\\1\n",
				';'
			), $str);
	}

	/**
	* get all contents and process the whole contents
	* @param string $type compress by type
	* @param array $files files that will be compressed
	* @param string $file 
	* @return string return cache url of 
	*/
	public function compress($type, $files, $file) 
	{
		// files have been updated so update the minified file
		if($this->isUpdated($files))
		{
			$content = $this->getFileContents($files);
			switch($type){
				case 'css':
					$content = $this->minifyCSS($content);
				break;
				case 'js':
					$content = $this->minifyJS($content);
				break;
			}
			$this->saveFile($file, $content);
		}
		return str_replace($this->scriptPath,$this->coreUrl,$file);	
	}

	/**
	* checking whether any registered files has been updated
	* @param array $files checking
	* @return boolean true if there is an updated file
	*/
	private function isUpdated($files)
	{
		$LastUpdate = 0;
		$basePath = $this->basePath;
		if(!empty($files))
		foreach($files as $file){
			$ed = filemtime($basePath.$file);
			if($ed > $LastUpdate){ $LastUpdate = $ed; }
		}
		return $LastUpdate;

	}
	
	/**
	* checking whether any registered files has been updated
	* @param string $file cache file that will be save the compressed contents
	* @param string $save contents that will be saved
	* @return boolean true if there is an updated file
	*/
	private function saveFile($file, $save)
	{
		$fp = fopen($file, 'w');
		flock($fp, LOCK_EX);
		fwrite($fp, $save);
		flock($fp, LOCK_UN);
		fclose($fp);
		return true;
	}

	/**
	* get contents of all registered files, it is also check whether file is exist or not
	* @param array $files all files that will be retrieved
	* @return strign return mixed of all contents
	*/
	private function getFileContents($files)
	{
		$content = '';
		$basePath = $this->basePath;

		array_walk($files,function(&$value, $key) use(&$content, $basePath){
			if(file_exists($basePath.$value))
			$content .= file_get_contents($basePath.$value);
		});
		return $content;
	}	
}