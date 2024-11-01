<?php 

class wppepvn_wpdb extends wpdb
{
	public $wppepvn_wpdb_init_status = true;
	
	private static $_wppepvn_tempData = array();
	
	public static $wppepvn_cache_object = false;
	
	public static $wppepvn_configs = array();
	
    public function __construct( $dbuser, $dbpassword, $dbname, $dbhost )
    {
		parent::__construct($dbuser, $dbpassword, $dbname, $dbhost);
		self::wppepvn_init();
	}
	
	public static function wppepvn_init() 
	{
		self::wppepvn_init_cache_object();
	}
	
	public static function wppepvn_init_cache_object() 
	{
		if(false == self::$wppepvn_cache_object) {
			self::$wppepvn_cache_object = self::_wppepvn_init_cache_object(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR . '/wp-optimize-speed-by-xtraffic/Application/includes/storages/cache/db/', crc32(__CLASS__ . __METHOD__));
		}
	}
	
	private static function _wppepvn_init_cache_object($dir_cache, $input_key_salt = 0) 
	{
		$cacheObject = false;
		
		$dir_cache = wppepvn_trailingslashdir($dir_cache);
		
		if(!is_dir($dir_cache)) {
			wppepvn_mkdir($dir_cache);
		}

		if(is_dir($dir_cache) && is_readable($dir_cache) && is_writable($dir_cache)) {
			
			$options = \WPOptimizeSpeedByxTraffic\AdvancedCache::getOption();
			
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
					isset($options['optimize_cache_database_cache_methods']['apc'])
					&& ($options['optimize_cache_database_cache_methods']['apc'])
					&& ('apc' === $options['optimize_cache_database_cache_methods']['apc'])
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
					isset($options['optimize_cache_database_cache_methods']['memcache'])
					&& ($options['optimize_cache_database_cache_methods']['memcache'])
					&& ('memcache' === $options['optimize_cache_database_cache_methods']['memcache'])
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
				,'gzcompress_level' => 2	// should be greater than 0 (>0, 2 is best) to save RAM in case of using Memcache, APC, ...
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
	
	private function _wppepvn_is_admin()
	{
		$key_check = '_wppepvn_is_admin';
		
		if(!isset(self::$_wppepvn_tempData[$key_check])) {
			self::$_wppepvn_tempData[$key_check] = is_admin();
		}
		
		return self::$_wppepvn_tempData[$key_check];
	}
	
	private function _wppepvn_is_ajax()
	{
		$key_check = '_wppepvn_is_ajax';
		
		if(!isset(self::$_wppepvn_tempData[$key_check])) {
			self::$_wppepvn_tempData[$key_check] = wppepvn_is_ajax();
		}
		
		return self::$_wppepvn_tempData[$key_check];
	}
	
	private function _wppepvn_get_cache($keyCache) 
	{
		$resultData = null;
		
		if(isset(self::$_wppepvn_tempData[$keyCache])) {
			$resultData = self::$_wppepvn_tempData[$keyCache];
		}
		
		if(null === $resultData) {
			$resultData = self::$wppepvn_cache_object->get_cache($keyCache);
			
			if(null !== $resultData) {
				self::$_wppepvn_tempData[$keyCache] = $resultData;
			}
		}
		
		return $resultData;
	}
	
	private function _wppepvn_get_current_cache_tags()
	{
		
		$cacheTags = array('tp-others');
		//$cacheTags[] = 'db';
		
		return $cacheTags;
	}
	
	private function _wppepvn_set_cache($keyCache, $data)
	{
		self::$_wppepvn_tempData[$keyCache] = $data;
		
		self::$wppepvn_cache_object->set_cache($keyCache, $data, $this->_wppepvn_get_current_cache_tags(), false, array(
			//'merge_tags' => true
		));
	}
	
	private function _wppepvn_is_query_cachable($query)
	{
	
		$isCachableStatus = true;
		
		if($isCachableStatus) {
			if(isset(self::$wppepvn_configs['disable_cache']) && self::$wppepvn_configs['disable_cache']) {
				$isCachableStatus = false;
			}
		}
		
		if($isCachableStatus) {
			$isCachableStatus = false;
			
			global $wpOptimizeSpeedByxTraffic_AdvancedCache;
			if(isset($wpOptimizeSpeedByxTraffic_AdvancedCache) && $wpOptimizeSpeedByxTraffic_AdvancedCache) {
				$isCachableStatus = $wpOptimizeSpeedByxTraffic_AdvancedCache->checkOptionIsRequestCacheable();
			}
		}
		
		if($isCachableStatus) {
			if(preg_match('#^[\s \t\(]*SELECT[\s \t(]+#is',$query)) {
				if(preg_match('#(FOUND_ROWS|SQL_CALC_FOUND_ROWS)#is',$query)) {
					$isCachableStatus = false;
				}
			} else {
				$isCachableStatus = false;
			}
		}
		
		if($isCachableStatus) {
			
			if(!isset(self::$_wppepvn_tempData['_patternsQueryStringNotCache1'])) {
				$tmp = array(
					'_transient_'
					,'cron'
					,'update_core'
					,'core_update'
				);
				
				self::$_wppepvn_tempData['_patternsQueryStringNotCache1'] = '#('.implode('|',$tmp).')#is';
			}
			
			if(preg_match(self::$_wppepvn_tempData['_patternsQueryStringNotCache1'], $query)) {	//not cache
				$isCachableStatus = false;
			}
		}
		
		return $isCachableStatus;
	}
	
	public function query($query) 
	{
		
		if ( ! $this->ready ) {
			return parent::query( $query );
		}
		
		/**
		 * Filter the database query.
		 *
		 * Some queries are made before the plugins have been loaded,
		 * and thus cannot be filtered with this method.
		 *
		 * @since 2.1.0
		 *
		 * @param string $query Database query.
		 */
		
		$this->flush();
		
		$query = trim($query);
		
		$this->last_query = $query;
		
		$dbCacheData = null;
		
		$cachableStatus = $this->_wppepvn_is_query_cachable($query);
		
		if($cachableStatus) {
			
			$keyCacheQuery = wppepvn_hash_key(array(
				__CLASS__ . __METHOD__
				, md5($query)
			));
			
			$dbCacheData = $this->_wppepvn_get_cache($keyCacheQuery);
		}
		
		if(null !== $dbCacheData) {
			
			$this->last_error = '';
			
			$this->last_query = $dbCacheData['last_query'];
			$this->last_result = $dbCacheData['last_result'];
			$this->col_info = $dbCacheData['col_info'];
			$this->num_rows = $dbCacheData['num_rows'];
			$return_val = $dbCacheData['return_val'];
			
		} else {
			
			$return_val = parent::query( $query );
			
			if ( $return_val === false ) { // error executing sql query
				return false;
			} else {
				
				if($cachableStatus) {
					
					$this->_wppepvn_set_cache($keyCacheQuery,array(
						'last_query' => $this->last_query
						,'last_result' => $this->last_result
						,'col_info' => $this->col_info
						,'num_rows' => $this->num_rows
						,'return_val' => $return_val
					));
					
				}
			}
		}
		
		return $return_val;
	}
	
}
