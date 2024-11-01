<?php 
namespace WPOptimizeSpeedByxTraffic;
use WPOptimizeByxTraffic\Application\Service\PepVN_Cache
;
class AdvancedCache
{
	
	private static $_tempData = array();
	
	protected static $_configs = array();
	
	protected static $_mobileDetectObject = array();
	
	public static $cachePageObject = false;
	
    public function __construct() 
    {
		
		self::$_configs = array();
		
		self::$_configs['static_options_dir_path'] = WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-speed-by-xtraffic/Application/includes/storages/cache/options/';
		
		self::$_configs['cache_pages_dir_path'] = WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-speed-by-xtraffic/Application/includes/storages/cache/pages/';
		
		self::$_configs['parsed_current_uri'] = wppepvn_parse_url(wppepvn_current_uri());
		
		self::$_mobileDetectObject = new \WPOptimizeByxTraffic\Application\Service\Mobile_Detect();
		
		self::$cachePageObject = self::initMultiCacheObject(self::$_configs['cache_pages_dir_path']);
		
	}
    
	public static function getOption()
	{
		if(!isset(self::$_configs['options'])) {
			self::$_configs['options'] = array();
			
			$filePath = self::$_configs['static_options_dir_path'].'optimize_speed.php';
			if(is_file($filePath)) {
				self::$_configs['options'] = include($filePath);
			}
			
		}
		
		return self::$_configs['options'];
	}
	
	public static function initMultiCacheObject($dir_cache, $input_key_salt = 0) 
	{
		$cacheObject = false;
		
		$dir_cache = wppepvn_trailingslashdir($dir_cache);
		
		if(!is_dir($dir_cache)) {
			wppepvn_mkdir($dir_cache);
		}

		if(is_dir($dir_cache) && is_readable($dir_cache) && is_writable($dir_cache)) {
			
			$options = self::getOption();
			
			if(defined('WP_PEPVN_CACHE_TIMEOUT_NORMAL')) {
				$optimize_cache_cachetimeout = WP_PEPVN_CACHE_TIMEOUT_NORMAL;
			} else {
				$optimize_cache_cachetimeout = 86400;
			}
			
			if(isset($options['optimize_cache_cachetimeout']) && $options['optimize_cache_cachetimeout']) {
				$options['optimize_cache_cachetimeout'] = (int)$options['optimize_cache_cachetimeout'];
				if($options['optimize_cache_cachetimeout']>0) {
					$optimize_cache_cachetimeout = $options['optimize_cache_cachetimeout'];
				}
			}
			
			if($optimize_cache_cachetimeout < 900) {
				$optimize_cache_cachetimeout = 900;
			}
			
			$cacheMethods = array();
			
			if(
				(
					isset($options['optimize_cache_object_cache_methods']['apc'])
					&& ($options['optimize_cache_object_cache_methods']['apc'])
					&& ('apc' === $options['optimize_cache_object_cache_methods']['apc'])
				)
			) {
				if(wppepvn_is_has_apc()) {
					$cacheTimeoutTemp = ceil($optimize_cache_cachetimeout / 3);
					$cacheTimeoutTemp = (int)$cacheTimeoutTemp;
					$cacheMethods['apc'] = array(
						'cache_timeout' => $cacheTimeoutTemp
					);
				}
			}
			
			if(
				(
					isset($options['optimize_cache_object_cache_methods']['memcache'])
					&& ($options['optimize_cache_object_cache_methods']['memcache'])
					&& ('memcache' === $options['optimize_cache_object_cache_methods']['memcache'])
				)
			) {
				if(
					isset($options['memcache_servers'])
					&& ($options['memcache_servers'])
				) {
					$options['memcache_servers'] = wppepvn_clean_array($options['memcache_servers']);
					if(!empty($options['memcache_servers'])) {
						if(wppepvn_is_has_memcached() || wppepvn_is_has_memcache()) {
							$memcacheServers = array();
							
							foreach($options['memcache_servers'] as $server) {
								if($server) {
									$server = explode(':',$server,2);
									$serverTemp = array(
										'host' => $server[0]
									);
									if(isset($server[1])) {
										$serverTemp['port'] = $server[1];
									}
									$memcacheServers[] = $serverTemp;
								}
							}
							
							if(!empty($memcacheServers)) {
								$cacheTimeoutTemp = ceil($optimize_cache_cachetimeout / 2);
								$cacheTimeoutTemp = (int)$cacheTimeoutTemp;
								$cacheMethods['memcache'] = array(
									'cache_timeout' => $cacheTimeoutTemp
									,'object' => false
									,'servers' => $memcacheServers
								);
							}
						}
					}
				}
			}
			
			$cacheMethods['file'] = array(
				'cache_timeout' => $optimize_cache_cachetimeout
				, 'cache_dir' => $dir_cache 
			);
			
			$hash_key_salt = wppepvn_crc32b(wppepvn_get_site_salt() . $dir_cache . $input_key_salt);
			
			$cacheObject = new \WPOptimizeByxTraffic\Application\Service\PepVN_Cache(array(
				'cache_timeout' => $optimize_cache_cachetimeout		//seconds
				,'hash_key_method' => 'crc32b'		//best is crc32b
				,'hash_key_salt' => $hash_key_salt
				,'gzcompress_level' => 5	// should be greater than 0 (>0, 2 is best) to save RAM in case of using Memcache, APC, ...
				,'key_prefix' => 'mtc_'
				,'cache_methods' => $cacheMethods
			));
			
			unset($cacheMethods);
		}
		
		if(!$cacheObject) {
			$cacheObject = new \WPOptimizeByxTraffic\Application\Service\PepVN_Cache(array());
		}
		
		return $cacheObject;
		
	}
	
	public function getPatternsExcludeCacheUrls($excludeUrls = array(), $options = false) 
	{
		if(!$options) {
			$options = self::getOption();
		}
		
		$excludeUrls = array_merge($excludeUrls, wppepvn_get_uri_not_cache());
		
		if(isset($options['optimize_cache_exclude_url']) && ($options['optimize_cache_exclude_url'])) {
			$options['optimize_cache_exclude_url'] = trim($options['optimize_cache_exclude_url']);
			$tmp = preg_replace('#[\,\;]+#',';',$options['optimize_cache_exclude_url']);
			$tmp = explode(';',$tmp);
			$tmp = wppepvn_clean_array($tmp);
			if(!empty($tmp)) {
				$excludeUrls = array_merge($excludeUrls,$tmp);
			}
			unset($tmp);
		}
		
		$excludeUrls = array_values($excludeUrls);
		
		$excludeUrls = array_unique($excludeUrls);
		
		return $excludeUrls;
		
	}
	
	public function getPatternsExcludeCacheCookies($excludeCookies = array(), $options = false) 
	{
		if(!$options) {
			$options = self::getOption();
		}
		
		$excludeCookies = array_merge($excludeCookies, wppepvn_get_cookies_not_cache());
		
		if(isset($options['optimize_cache_exclude_cookie']) && ($options['optimize_cache_exclude_cookie'])) {
			$options['optimize_cache_exclude_cookie'] = trim($options['optimize_cache_exclude_cookie']);
			$tmp = preg_replace('#[\,\;]+#',';',$options['optimize_cache_exclude_cookie']);
			$tmp = explode(';',$tmp);
			$tmp = wppepvn_clean_array($tmp);
			if(!empty($tmp)) {
				$excludeCookies = array_merge($excludeCookies,$tmp);
			}
			unset($tmp);
		}
		
		$excludeCookies = array_values($excludeCookies);
		
		$excludeCookies = array_unique($excludeCookies);
		
		return $excludeCookies;
		
	}
	
	public static function isCurrentRequestCacheable() 
	{
		$k = crc32(__CLASS__ . __METHOD__);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		}
		
		$isCacheableStatus = true;
		
		if($isCacheableStatus) {
			if(defined('WPPEPVN_NOCACHE')) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			if(wppepvn_is_ajax()) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			if(!wppepvn_is_request_method('GET')) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			if(wppepvn_is_preview()) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			if(is_admin()) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			$httpResponseCode = http_response_code();
			if($httpResponseCode) {
				$httpResponseCode = (int)$httpResponseCode;
				if($httpResponseCode !== 200) {
					$isCacheableStatus = false;
				}
			}
		}
		
		if($isCacheableStatus) {
			if(wppepvn_is_loginpage()) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			if(wppepvn_is_pagenow(array(
				'wp-cron.php'
				,'wp-signup.php'
				,'xmlrpc.php'
				,'wp-activate.php'
				,'wp-trackback.php'
				,'wp-comments-post.php'
				,'wp-mail.php'
				,'wp-trackback.php'
				,'edit.php'
				,'post-new.php'
				,'edit-tags.php'
				,'upload.php'
				,'media-new.php'
				,'edit-comments.php'
				,'themes.php'
				,'customize.php'
				,'widgets.php'
				,'nav-menus.php'
				,'theme-editor.php'
				,'plugins.php'
				,'plugin-install.php'
				,'plugin-editor.php'
				,'users.php'
				,'user-new.php'
				,'profile.php'
				,'tools.php'
				,'import.php'
				,'export.php'
				,'options-general.php'
				,'options-writing.php'
				,'options-reading.php'
				,'options-discussion.php'
				,'options-media.php'
				,'options-permalink.php'
			))) {
				$isCacheableStatus = false;
			}
		}
		
		self::$_tempData[$k] = $isCacheableStatus;
		
		if(!$isCacheableStatus) {
			defined('WPPEPVN_NOCACHE') || define('WPPEPVN_NOCACHE', true);
		}
		
		return self::$_tempData[$k];
	}
	
	public function checkOptionIsRequestCacheable($options = false) 
	{
		$k = crc32(__CLASS__ . __METHOD__);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		}
		
		if(!$options) {
			$options = self::getOption();
		}
		
		$isCacheableStatus = false;
		
		if(isset($options['optimize_cache_enable']) && ('on' === $options['optimize_cache_enable'])) {
			$isCacheableStatus = true;
		}
		
		if($isCacheableStatus) {
			if(defined('WPPEPVN_NOCACHE')) {
				$isCacheableStatus = false;
			}
		}
		
		if($isCacheableStatus) {
			$isCacheableStatus = self::isCurrentRequestCacheable();
		}
		
		/*
		disabled because issue with db & object cache
		if($isCacheableStatus) {
			if(isset($options['optimize_cache_logged_users_cache_enable']) && ('on' === $options['optimize_cache_logged_users_cache_enable'])) {
				
			} else {
				if(wppepvn_is_user_logged_in_via_cookie()) {
					$isCacheableStatus = false;
				}
			}
		}
		*/
		
		if($isCacheableStatus) {	//not set cache with GET query
			
			if(isset($options['optimize_cache_url_get_query_cache_enable']) && ('on' === $options['optimize_cache_url_get_query_cache_enable'])) {
				
			} else {
				if(
					isset(self::$_configs['parsed_current_uri']['parameters']) 
					&& self::$_configs['parsed_current_uri']['parameters']
					&& !empty(self::$_configs['parsed_current_uri']['parameters'])
				) {
					$isCacheableStatus = false;
				}
			}
		}
		
		if($isCacheableStatus) {
			if(isset($options['optimize_cache_mobile_device_cache_enable']) && ('on' === $options['optimize_cache_mobile_device_cache_enable'])) {
				
			} else {
				if ( self::$_mobileDetectObject->isMobile() || self::$_mobileDetectObject->isTablet() ) {	//no cache with mobile
					$isCacheableStatus = false;
				}
			}
		}
		
		if($isCacheableStatus) {
			
			$scheme = wppepvn_request_scheme();
			
			if(
				('http' !== $scheme)
				&& ('https' !== $scheme)
			) {
				$isCacheableStatus = false;
			} else {
				if(
					('https' === $scheme)
				) {
					if(isset($options['optimize_cache_ssl_request_cache_enable']) && ('on' === $options['optimize_cache_ssl_request_cache_enable'])) {
				
					} else {
						$isCacheableStatus = false;
					}
				}
			}
			
		}
		
		if($isCacheableStatus) {
			
			$fullUri = wppepvn_current_uri();
			
            $tmp = $this->getPatternsExcludeCacheUrls(array(), $options);
			
			$tmp = wppepvn_clean_preg_patterns_array($tmp);
			$tmp = implode('|',$tmp);
			if(preg_match('#('.$tmp.')#i',$fullUri)) {
				$isCacheableStatus = false;
			}
			unset($tmp);
		}
		
		if($isCacheableStatus) {
			if(
				isset($_COOKIE)
				&& ($_COOKIE)
				&& is_array($_COOKIE)
				&& !empty($_COOKIE)
			) {
				
				$tmp = $this->getPatternsExcludeCacheCookies(array(), $options);
			
				$tmp = wppepvn_clean_preg_patterns_array($tmp);
				$tmp = implode('|',$tmp);
				
				$tmp2 = $_COOKIE;
				$tmp2 = (array)$tmp2;
				$tmp2 = implode(';',$tmp2);
				
				if(preg_match('#\;?('.$tmp.')\;?#i',$tmp2)) {
					$isCacheableStatus = false;
				}
				unset($tmp,$tmp2);
			}
		}
		
		self::$_tempData[$k] = $isCacheableStatus;
		
		if(!$isCacheableStatus) {
			defined('WPPEPVN_NOCACHE') || define('WPPEPVN_NOCACHE', true);
		}
		
		return self::$_tempData[$k];
		
	}
	
	private function _checkAndGetSetPageCache_GetKeyCache()
	{
		$k = crc32(__CLASS__ . __METHOD__);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		}
		
		$keyCache = array();
		
		$keyCache[] = wppepvn_current_uri();
		$keyCache[] = wppepvn_get_current_user_hash_via_cookie();
		$keyCache[] = 'sw-'.wppepvn_get_device_screen_width();
		
		$keyCache = wppepvn_hash_key($keyCache);
		
		self::$_tempData[$k] = $keyCache;
		
		return $keyCache;
	}
	
	public function checkAndGetPageCache()
	{
		$options = self::getOption();
		
		$isCacheableStatus = $this->checkOptionIsRequestCacheable($options);
		
		if($isCacheableStatus) {
			if(isset($options['optimize_cache_logged_users_cache_enable']) && ('on' === $options['optimize_cache_logged_users_cache_enable'])) {
				
			} else {
				if(wppepvn_is_user_logged_in_via_cookie()) {
					$isCacheableStatus = false;
				}
			}
		}
		
		if($isCacheableStatus) {
			
			$keyCache = $this->_checkAndGetSetPageCache_GetKeyCache();
			
			$cacheData = self::$cachePageObject->get_cache($keyCache);
			
			if(null !== $cacheData) {
				
				$this->flush_http_headers($cacheData);
				header('X-Cache: HIT (PHP - xTraffic)',true);
				echo $cacheData['text'];
				
				ob_end_flush();
				
				exit();
				
			}
		}
		
	}
	
	public function checkAndSetPageCache($data,$cache_tags = array())
	{
		$options = self::getOption();
		
		$isCacheableStatus = $this->checkOptionIsRequestCacheable($options);
		
		if($isCacheableStatus) {
			if(isset($options['optimize_cache_logged_users_cache_enable']) && ('on' === $options['optimize_cache_logged_users_cache_enable'])) {
				
			} else {
				if(wppepvn_is_user_logged_in_via_cookie()) {
					$isCacheableStatus = false;
				}
			}
		}
		
		if($isCacheableStatus) {
			$keyCache = $this->_checkAndGetSetPageCache_GetKeyCache();
		
			self::$cachePageObject->set_cache($keyCache, $data, $cache_tags);
		}
		
	}
	
	public function flush_http_headers($cacheData)
	{
		if(!isset($cacheData['http_headers'])) {
			$cacheData['http_headers'] = array();
		}
		
		$isNotModifiedStatus = false;
		
		$options = self::getOption();
		
		if(isset($options['optimize_cache_browser_cache_enable']) && ('on' === $options['optimize_cache_browser_cache_enable'])) {
			
			$current_user_hash = wppepvn_get_current_user_hash_via_cookie();
			
			$etag = md5(ceil($cacheData['last_modified_time'] / $cacheData['cache_timeout']) . $current_user_hash);
			
			$cacheData['http_headers'][] = 'Etag: '.$etag;
			
			//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
			$etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
			
			if(false !== $etagHeader) {
				if($etagHeader === $etag) {
					$isNotModifiedStatus = true;
				}
			}
			
			if(false === $isNotModifiedStatus) {
				//get the HTTP_IF_MODIFIED_SINCE header if set
				$ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
				
				if(false !== $ifModifiedSince) {
					$ifModifiedSince = strtotime($ifModifiedSince);
					if($ifModifiedSince) {
						if($ifModifiedSince === $cacheData['last_modified_time']) {
							$isNotModifiedStatus = true;
						}
					}
				}
			}
			
			if(0 === $current_user_hash) {
				$cacheData['http_headers'][] = 'Expires: '.wppepvn_gmdate_gmt($cacheData['last_modified_time'] + $cacheData['cache_timeout']);

				$cacheData['http_headers'][] = 'Last-Modified: '.wppepvn_gmdate_gmt($cacheData['last_modified_time']);
				
				$cacheData['http_headers'][] = 'Cache-Control: public, max-age='.$cacheData['cache_timeout'].', s-maxage='.$cacheData['cache_timeout'];
			} else {
				$cacheData['http_headers'][] = 'Cache-Control: private, max-age='.$cacheData['cache_timeout'].', s-maxage=0';
			}
			
		}
		
		if(!empty($cacheData['http_headers'])) {
			
			wppepvn_http_headers($cacheData['http_headers'], 'flush');
			
			if($isNotModifiedStatus) {
				header('HTTP/1.1 304 Not Modified',true,304);
			}
			
		}
	}
	
	public function clean_cache($mode, $tags = array())
	{
		
		if($mode === PepVN_Cache::CLEANING_MODE_ALL) {
			self::$cachePageObject->clean_all_methods();
		}
		
		self::$cachePageObject->clean(array(
			'clean_mode' => $mode
			,'tags' => $tags
		));
		
	}
	
}

