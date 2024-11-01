<?php 

class wppepvn_wpdb
{
	public $wppepvn_wpdb_init_status = true;
	
	private static $_wppepvn_wpdb_object = false;
	
	private static $_wppepvn_tempData = array();
	
	public static $wppepvn_cache_object = false;
	
	public function __construct() 
	{
		self::$_wppepvn_wpdb_object = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
		self::wppepvn_init();
	}
	
	public static function wppepvn_init() 
	{
		self::wppepvn_init_cache_object();
	}
	
	public function __isset( $name ) {
		return isset( self::$_wppepvn_wpdb_object->$name );
	}
	
	public function __set( $name, $value ) 
	{
		self::$_wppepvn_wpdb_object->$name = $value;
	}
	
	public function __unset( $name ) 
	{
		unset( self::$_wppepvn_wpdb_object->$name );
	}
	
	public function __get($varname)
    {
		return self::$_wppepvn_wpdb_object->$varname;
    }
	
	public static function __callStatic($method, $args)
    {
		if(0 === strpos($method,'escape_by_ref')) {
			return self::$_wppepvn_wpdb_object->escape_by_ref($args[0]);
		}
		
		return call_user_func_array(array(self::$_wppepvn_wpdb_object, $method), $args);
    }
	
	public function __call($method,$args)
    {
		if(0 === strpos($method,'escape_by_ref')) {
			return self::$_wppepvn_wpdb_object->escape_by_ref($args[0]);
		}
		
		return call_user_func_array(array(self::$_wppepvn_wpdb_object, $method), $args);
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
	
	private function _wppepvn_is_query_cachable($query)
	{
	
		$isCachableStatus = true;
		
		if($isCachableStatus) {
			if(
				wppepvn_is_loginpage()
			) {	//not cache login/register page
				$isCachableStatus = false;
			}
		}
		
		if($isCachableStatus) {
			if(
				wppepvn_is_preview()
			) {
				$isCachableStatus = false;
			}
		}
		
		if($isCachableStatus) {
			if(preg_match('#^([\s \t\(])*?SELECT[\s \t]+#is',$query)) {
				if(preg_match('#(FOUND_ROWS)#is',$query)) {
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
				);
				
				self::$_wppepvn_tempData['_patternsQueryStringNotCache1'] = '#('.implode('|',$tmp).')#is';
			}
			
			if(preg_match(self::$_wppepvn_tempData['_patternsQueryStringNotCache1'], $query)) {	//not cache
				$isCachableStatus = false;
			}
		}
		
		return $isCachableStatus;
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
		$cacheTags = wppepvn_get_cachetags_current_request(true);
		
		$cacheTags[] = 'db';
		
		return $cacheTags;
	}
	
	private function _wppepvn_set_cache($keyCache, $data)
	{
		self::$_wppepvn_tempData[$keyCache] = $data;
		
		self::$wppepvn_cache_object->set_cache($keyCache, $data, $this->_wppepvn_get_current_cache_tags(), false, array(
			'merge_tags' => true
		));
	}
	
	
	private function _wppepvn_flush()
	{
		self::$_wppepvn_wpdb_object->flush();
		
		self::$_wppepvn_wpdb_object->last_result = array();
		self::$_wppepvn_wpdb_object->col_info    = null;
		self::$_wppepvn_wpdb_object->last_query  = null;
		self::$_wppepvn_wpdb_object->rows_affected = 0;
		self::$_wppepvn_wpdb_object->num_rows = 0;
		self::$_wppepvn_wpdb_object->last_error  = '';
	}
	
	public function query($query) 
	{
		//return $this->_wppepvn_query( $query );
		
		if ( ! self::$_wppepvn_wpdb_object->ready ) {
			return self::$_wppepvn_wpdb_object->query( $query );
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
		
		$query = apply_filters( 'query', $query );
		
		$this->_wppepvn_flush();
		
		self::$_wppepvn_wpdb_object->func_call = "\$db->query(\"$query\")";
		
		self::$_wppepvn_wpdb_object->last_query = $query;
		
		$keyCacheQuery = wppepvn_hash_key(array(
			__CLASS__ . __METHOD__
			, $query
		));
		
		$dbData = $this->_wppepvn_get_cache($keyCacheQuery);
		
		if(null !== $dbData) {
			$this->_wppepvn_flush();
			
			self::$_wppepvn_wpdb_object->last_error = '';
			
			self::$_wppepvn_wpdb_object->last_query = $dbData['last_query'];
			self::$_wppepvn_wpdb_object->last_result = $dbData['last_result'];
			self::$_wppepvn_wpdb_object->col_info = $dbData['col_info'];
			self::$_wppepvn_wpdb_object->num_rows = $dbData['num_rows'];
			self::$_wppepvn_wpdb_object->rows_affected = $dbData['rows_affected'];
			$return_val = $dbData['return_val'];
			
			unset($dbData);
			
		} else {
			
			$this->_wppepvn_flush();
			
			$return_val = self::$_wppepvn_wpdb_object->query( $query );
			
			if (false === $return_val) { // error executing sql query
				return false;
			} else {
				if($this->_wppepvn_is_query_cachable($query)) {
					if(self::$_wppepvn_wpdb_object->num_rows && (self::$_wppepvn_wpdb_object->num_rows > 0)) {
						$dbData = array(
							'last_query' => self::$_wppepvn_wpdb_object->last_query
							,'last_result' => self::$_wppepvn_wpdb_object->last_result
							,'col_info' => self::$_wppepvn_wpdb_object->col_info
							,'num_rows' => self::$_wppepvn_wpdb_object->num_rows
							,'rows_affected' => self::$_wppepvn_wpdb_object->rows_affected
							,'return_val' => $return_val
						);
						
						$this->_wppepvn_set_cache($keyCacheQuery,$dbData);
						
						unset($dbData);
					}
				}
			}
		}
		
		return $return_val;
	}
	
	
	
	
	
	/**
	 * Retrieve one variable from the database.
	 *
	 * Executes a SQL query and returns the value from the SQL result.
	 * If the SQL result contains more than one column and/or more than one row, this function returns the value in the column and row specified.
	 * If $query is null, this function returns the value in the specified column and row from the previous SQL result.
	 *
	 * @since 0.71
	 *
	 * @param string|null $query Optional. SQL query. Defaults to null, use the result from the previous query.
	 * @param int         $x     Optional. Column of value to return. Indexed from 0.
	 * @param int         $y     Optional. Row of value to return. Indexed from 0.
	 * @return string|null Database query result (as string), or null on failure
	 */
	public function get_var( $query = null, $x = 0, $y = 0 ) {
		self::$_wppepvn_wpdb_object->func_call = "\$db->get_var(\"$query\", $x, $y)";
		
		if ( $query ) {
			$this->query( $query );
		}

		// Extract var out of cached results based x,y vals
		if ( !empty( self::$_wppepvn_wpdb_object->last_result[$y] ) ) {
			$values = array_values( get_object_vars( self::$_wppepvn_wpdb_object->last_result[$y] ) );
		}

		// If there is a value return it else return null
		return ( isset( $values[$x] ) && $values[$x] !== '' ) ? $values[$x] : null;
	}

	/**
	 * Retrieve one row from the database.
	 *
	 * Executes a SQL query and returns the row from the SQL result.
	 *
	 * @since 0.71
	 *
	 * @param string|null $query  SQL query.
	 * @param string      $output Optional. one of ARRAY_A | ARRAY_N | OBJECT constants.
	 *                            Return an associative array (column => value, ...),
	 *                            a numerically indexed array (0 => value, ...) or
	 *                            an object ( ->column = value ), respectively.
	 * @param int         $y      Optional. Row to return. Indexed from 0.
	 * @return array|object|null|void Database query result in format specified by $output or null on failure
	 */
	public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
		self::$_wppepvn_wpdb_object->func_call = "\$db->get_row(\"$query\",$output,$y)";
		
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}

		if ( !isset( self::$_wppepvn_wpdb_object->last_result[$y] ) )
			return null;

		if ( $output == OBJECT ) {
			return self::$_wppepvn_wpdb_object->last_result[$y] ? self::$_wppepvn_wpdb_object->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			return self::$_wppepvn_wpdb_object->last_result[$y] ? get_object_vars( self::$_wppepvn_wpdb_object->last_result[$y] ) : null;
		} elseif ( $output == ARRAY_N ) {
			return self::$_wppepvn_wpdb_object->last_result[$y] ? array_values( get_object_vars( self::$_wppepvn_wpdb_object->last_result[$y] ) ) : null;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return self::$_wppepvn_wpdb_object->last_result[$y] ? self::$_wppepvn_wpdb_object->last_result[$y] : null;
		} else {
			$this->print_error( " \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N" );
		}
	}

	/**
	 * Retrieve one column from the database.
	 *
	 * Executes a SQL query and returns the column from the SQL result.
	 * If the SQL result contains more than one column, this function returns the column specified.
	 * If $query is null, this function returns the specified column from the previous SQL result.
	 *
	 * @since 0.71
	 *
	 * @param string|null $query Optional. SQL query. Defaults to previous query.
	 * @param int         $x     Optional. Column to return. Indexed from 0.
	 * @return array Database query result. Array indexed from 0 by SQL result row number.
	 */
	public function get_col( $query = null , $x = 0 ) {
		
		if ( $query ) {
			$this->query( $query );
		}

		$new_array = array();
		// Extract the column values
		for ( $i = 0, $j = count( self::$_wppepvn_wpdb_object->last_result ); $i < $j; $i++ ) {
			$new_array[$i] = $this->get_var( null, $x, $i );
		}
		return $new_array;
	}

	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 *
	 * Executes a SQL query and returns the entire SQL result.
	 *
	 * @since 0.71
	 *
	 * @param string $query  SQL query.
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *                       With one of the first three, return an array of rows indexed from 0 by SQL result row number.
	 *                       Each row is an associative array (column => value, ...), a numerically indexed array (0 => value, ...), or an object. ( ->column = value ), respectively.
	 *                       With OBJECT_K, return an associative array of row objects keyed by the value of each row's first column's value.
	 *                       Duplicate keys are discarded.
	 * @return array|object|null Database query results
	 */
	public function get_results( $query = null, $output = OBJECT ) {
		self::$_wppepvn_wpdb_object->func_call = "\$db->get_results(\"$query\", $output)";
		
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}

		$new_array = array();
		if ( $output == OBJECT ) {
			// Return an integer-keyed array of row objects
			return self::$_wppepvn_wpdb_object->last_result;
		} elseif ( $output == OBJECT_K ) {
			// Return an array of row objects with keys from column 1
			// (Duplicates are discarded)
			foreach ( self::$_wppepvn_wpdb_object->last_result as $row ) {
				$var_by_ref = get_object_vars( $row );
				$key = array_shift( $var_by_ref );
				if ( ! isset( $new_array[ $key ] ) )
					$new_array[ $key ] = $row;
			}
			return $new_array;
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			// Return an integer-keyed array of...
			if ( self::$_wppepvn_wpdb_object->last_result ) {
				foreach( (array) self::$_wppepvn_wpdb_object->last_result as $row ) {
					if ( $output == ARRAY_N ) {
						// ...integer-keyed row arrays
						$new_array[] = array_values( get_object_vars( $row ) );
					} else {
						// ...column name-keyed row arrays
						$new_array[] = get_object_vars( $row );
					}
				}
			}
			return $new_array;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return self::$_wppepvn_wpdb_object->last_result;
		}
		return null;
	}

	
	
	
	
	
}
