<?php
namespace WPOptimizeSpeedByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WpPepVN\DependencyInjection
	, WpPepVN\Utils
	, WpPepVN\Text
	, WpPepVN\System
	, WPOptimizeByxTraffic\Application\Service\Device
;

class CacheRequestUri
{
	public $di = false;
	
	private $_tempData = array();
	
	public function __construct(DependencyInjection $di)
	{
		$this->di = $di;
	}
	
	
    public function get_cache_host_folder_path($uri = null) 
	{
		$request = $this->di->getShared('request');
		
		if($uri === null) {
			$uri = $request->getFullUri();
		}
		
		$parseUri = Utils::parse_url($uri);
		
		if(isset($parseUri['host']) && $parseUri['host']) {
		} else {
			$parseUri['host'] = 0;
		}
		
		$folderPath = WP_CONTENT_PEPVN_DIR . 'cache' . DIRECTORY_SEPARATOR . 'request-uri' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $parseUri['host'] . DIRECTORY_SEPARATOR;
		
		return $folderPath;
	}
	
	
    public function get_cache_full_folder_path($uri = null) 
	{
		$request = $this->di->getShared('request');
		
		if($uri === null) {
			$uri = $request->getFullUri();
		}
		
		$parseUri = Utils::parse_url($uri);
		
		if(isset($parseUri['host']) && $parseUri['host']) {
		} else {
			$parseUri['host'] = 0;
		}
		
		if(isset($parseUri['path']) && $parseUri['path']) {
		} else {
			$parseUri['path'] = 0;
		}
		
		$folderPath = $this->get_cache_host_folder_path($uri);
		
		$folderPathPlus = '';
		
		if(isset($parseUri['path']) && $parseUri['path']) {
			$folderPathPlus .= $parseUri['path'] . DIRECTORY_SEPARATOR;
		}
		
		if($folderPathPlus) {
			
			$folderPathPlus = Utils::fixPath($folderPathPlus) . DIRECTORY_SEPARATOR;
			
			$folderPathPlus = explode(DIRECTORY_SEPARATOR,$folderPathPlus);
			
			foreach($folderPathPlus as $keyOne => $valueOne) {
				$valueOne = trim(Text::replaceSpecialChar($valueOne,' ','.-_'));
				$valueOne = Text::removeSpace($valueOne,'-');
				$folderPathPlus[$keyOne] = $valueOne;
			}
			
			$folderPathPlus = implode(DIRECTORY_SEPARATOR,$folderPathPlus);
		}
		
		$folderPath .= $folderPathPlus;
		
		return $folderPath;
	}
	
    public function get_cache_file_path($uri = null, $options = false, $configs = null) 
	{
		if(!$options) {
			$options = array();
		}
		
		$request = $this->di->getShared('request');
		$wpExtend = $this->di->getShared('wpExtend');
		
		if($uri === null) {
			$uri = $request->getFullUri();
		}
		
		$scheme = '';
		
		$parseUri = Utils::parse_url($uri);
		
		if(isset($parseUri['scheme']) && $parseUri['scheme']) {
		} else {
			$parseUri['scheme'] = 'http';
		}
		
		if('https' === $parseUri['scheme']) {
			$scheme = '-https';
		}
		
		if(isset($parseUri['host']) && $parseUri['host']) {
		} else {
			$parseUri['host'] = 0;
		}
		
		if(isset($parseUri['path']) && $parseUri['path']) {
		} else {
			$parseUri['path'] = 0;
		}
		
		$folderPath = $this->get_cache_full_folder_path($uri);
		
		if(!is_dir($folderPath)) {
			System::mkdir($folderPath);
		}
		
		if(is_dir($folderPath) && is_writable($folderPath)) {
			
			if(isset($configs['file_extension']) && $configs['file_extension']) {
				$fileExtension = $configs['file_extension'];
			} else {
				
				$fileExtension = 'html';
				
				if($wpExtend->is_feed()) {
					$fileExtension = 'xml';
				} else {
					if(preg_match('#^.+\.xml\??[^\?]*$#',$uri)) {
						$fileExtension = 'xml';
					} else {
						$fileExtension = 'html';
					}
				}
			}
			
			$filePath = $folderPath.'index'.$scheme;
			
			if('html' === $fileExtension) {
				
				$filePath .= '-sw_';
				
				if(class_exists('\WPOptimizeByxTraffic\Application\Service\OptimizeImages')) {
					$tmp = \WPOptimizeByxTraffic\Application\Service\OptimizeImages::getOption();
					
					if(isset($tmp['optimize_images_auto_resize_images_enable']) && ('on' === $tmp['optimize_images_auto_resize_images_enable'])) {
						$device = $this->di->getShared('device');
						$screenWidth = $device->get_device_screen_width();
						if($screenWidth && ($screenWidth>0)) {
							$filePath .= $screenWidth;
						}
					}
					unset($tmp);
				}
				
			}
			
			$filePath .= '.'.$fileExtension;
			
			return $filePath;
		}
		
		return false;
	}
	
	public function isRequestCacheable($options) 
	{
		$k = Utils::hashKey(array('isRequestCacheable'));
		
		if(isset($this->_tempData[$k])) {
			return $this->_tempData[$k];
		}
		
		$optimizeSpeed = $this->di->getShared('optimizeSpeed');
		
		$isCreateCacheStatus = $optimizeSpeed->checkOptionIsRequestCacheable($options);
		
		unset($options);
		
		if($isCreateCacheStatus) {
			
			//$device = $this->di->getShared('device');
			//$request = $this->di->getShared('request');
			$wpExtend = $this->di->getShared('wpExtend');
			
			if($wpExtend->is_user_logged_in()) {
				$isCreateCacheStatus = false;
			}
		}
		
		$this->_tempData[$k] = $isCreateCacheStatus;
		
		return $this->_tempData[$k];
		
	}
	
	
	
	public function flush_http_headers($input_parameters)
	{
		if (headers_sent()) {
			return false;
		}
		
		if(isset($input_parameters['isNotModifiedStatus'])) {
			ob_clean();
		}
		
		//header('X-Powered-By: '.WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_NAME.'/'.WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_VERSION,true);
		
		if(isset($input_parameters['content_type']) && $input_parameters['content_type']) {
			header('Content-Type: '.$input_parameters['content_type'],true);
		}
		
		if(isset($input_parameters['cache_timeout']) && $input_parameters['cache_timeout']) {
			header('Cache-Control: public, max-age='.$input_parameters['cache_timeout'],true);
			header('Pragma: public',true);
		}
		
		if(isset($input_parameters['cache_timeout']) && $input_parameters['cache_timeout']) {
			
			if(isset($input_parameters['etag'])) {
				header('Etag: '.$input_parameters['etag'],true);
			}
			
			$lastModifiedTime = PepVN_Data::$defaultParams['requestTime'];
			if(isset($input_parameters['last_modified_time']) && $input_parameters['last_modified_time']) {
				if($input_parameters['last_modified_time'] > 0) {
					$lastModifiedTime = $input_parameters['last_modified_time'];
				}
			}
			$lastModifiedTime = (int)$lastModifiedTime;
			
			header('Expires: '.PepVN_Data::gmdate_gmt($lastModifiedTime + $input_parameters['cache_timeout']),true);
			
			header('Last-Modified: '.PepVN_Data::gmdate_gmt($lastModifiedTime),true);
			
		}
		
		if(isset($input_parameters['isNotModifiedStatus'])) {
			header('HTTP/1.1 304 Not Modified',true,304);
			ob_end_flush();
			exit();
		}
		
	}
	
	public function remove_cache_for_web_server() 
	{
		$folderPath = $this->get_cache_host_folder_path();
		
		System::rmdirR($folderPath,true);
	}
	
	public function set_cache_for_web_server($data, $configs = null) 
	{
		if($data && is_string($data) && !empty($data)) {
			
			$data = trim($data);
			
			if($data && !empty($data)) {
				
				$options = OptimizeSpeed::getOption();
				
				$isCreateCacheStatus = $this->isRequestCacheable($options);
				
				if(true === $isCreateCacheStatus) {
					
					$cacheFilePath = $this->get_cache_file_path(null, $options);
					
					if($cacheFilePath) {
						
						if(is_file($cacheFilePath)) {
							if(isset($configs['force_override']) && $configs['force_override']) {
								
							} else {
								$isCreateCacheStatus = false;
							}
						}
						
						if($isCreateCacheStatus) {
							return file_put_contents($cacheFilePath, $data);
						}
						
					}
				}
				
			}
		}
		
		return false;
	}
}