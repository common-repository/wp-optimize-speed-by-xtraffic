<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	,WPOptimizeByxTraffic\Application\Service\PepVN_Data
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\Database as OptimizeCache_Database
;

class WPDBWrapper
{
	public $wppepvn_wpdbwrapper_init_status = true;
	
	private $_wppepvn_wpdbwrapper_wpDbObj = false;
	
	private $_wppepvn_wpdbwrapper_di = false;
	
	private $_wppepvn_wpdbwrapper_wpExtend = false;
	
	private static $_wppepvn_wpdbwrapper_tempData = array();
	
    public function __construct(DependencyInjection $di, $wpdbObj) 
    {
		$this->_wppepvn_wpdbwrapper_di = $di;
		
		$this->_wppepvn_wpdbwrapper_wpExtend = $this->_wppepvn_wpdbwrapper_di->getShared('wpExtend');
		
		$this->_wppepvn_wpdbwrapper_wpDbObj = $wpdbObj;
		
	}
    
	public function __isset( $name ) {
		return isset( $this->_wppepvn_wpdbwrapper_wpDbObj->$name );
	}
	
	public function __set( $name, $value ) 
	{
		$this->_wppepvn_wpdbwrapper_wpDbObj->$name = $value;
	}
	
	public function __unset( $name ) 
	{
		unset( $this->_wppepvn_wpdbwrapper_wpDbObj->$name );
	}
	
	public function __get($varname)
    {
		return $this->_wppepvn_wpdbwrapper_wpDbObj->$varname;
    }
	
	public static function __callStatic($method, $args)
    {
		return $this->_wppepvn_wpdbwrapper_process_call_wpdb_method($method,$args); 
    }
	
	public function __call($method,$args)
    {
		return $this->_wppepvn_wpdbwrapper_process_call_wpdb_method($method,$args);
    }
	
	private function _wppepvn_wpdbwrapper_get_cache($keyCache) 
	{
		$resultData = null;
		
		if(isset(self::$_wppepvn_wpdbwrapper_tempData[$keyCache])) {
			$resultData = self::$_wppepvn_wpdbwrapper_tempData[$keyCache];
		}
		
		if(null === $resultData) {
			$resultData = OptimizeCache_Database::$wppepvn_cache_object->get_cache($keyCache);
			
			if(null !== $resultData) {
				self::$_wppepvn_wpdbwrapper_tempData[$keyCache] = $resultData;
			}
		}
		
		return $resultData;
	}
	
	private function _wppepvn_wpdbwrapper_set_cache($keyCache, $data)
	{
		
		self::$_wppepvn_wpdbwrapper_tempData[$keyCache] = $data;
		
		OptimizeCache_Database::$wppepvn_cache_object->set_cache($keyCache, $data, $this->_wppepvn_wpdbwrapper_get_current_cache_tags());
		
	}
	
	private function _wppepvn_wpdbwrapper_get_current_cache_tags()
	{
		/*
		$optimizeSpeed = $this->_wppepvn_wpdbwrapper_di->getShared('optimizeSpeed');
		
		$cacheTags = $optimizeSpeed->getCacheTags();
		*/
		
		$cacheTags = array();
		
		$cacheTags[] = 'tp-others';
		
		//$cacheTags = array_values($cacheTags);
		//$cacheTags = array_unique($cacheTags);
		
		return $cacheTags;
		
	}
	
	private function _wppepvn_wpdbwrapper_is_process_call_wpdb_method($method) 
	{
		$key_check = 'z'.crc32('_wppepvn_wpdbwrapper_is_process_call_wpdb_method_'.$method);
		
		if(!isset(self::$_wppepvn_wpdbwrapper_tempData[$key_check])) {
			
			self::$_wppepvn_wpdbwrapper_tempData[$key_check] = false;
			
			if(
				!method_exists($this,$method)
				&& method_exists($this->_wppepvn_wpdbwrapper_wpDbObj,$method)
			) {
				self::$_wppepvn_wpdbwrapper_tempData[$key_check] = true;
			}
		}
		
		return self::$_wppepvn_wpdbwrapper_tempData[$key_check];
	}
	
	private function _wppepvn_wpdbwrapper_process_call_wpdb_method($method,$args) 
	{
		
		$resultData = null;
		
		if(
			$this->_wppepvn_wpdbwrapper_is_process_call_wpdb_method($method)
		) {
			
			if(0 === strpos($method,'escape_by_ref')) {
				return $this->_wppepvn_wpdbwrapper_wpDbObj->escape_by_ref($args[0]);
			}
			
			$keyCache = Utils::hashKey(array(
				'WPDBWrapper'
				,'_wppepvn_wpdbwrapper_process_call_wpdb_method'
				,$method
				,$args
			));
			
			$isCachableStatus = $this->_wppepvn_wpdbwrapper_is_cachable($method,$args,$keyCache);
			
			if($isCachableStatus) {
				$resultData = $this->_wppepvn_wpdbwrapper_get_cache($keyCache);
			}
			
			if(null === $resultData) {
				$resultData = call_user_func_array(array($this->_wppepvn_wpdbwrapper_wpDbObj, $method), $args);
				
				if($isCachableStatus) {
					if(null !== $resultData) {
						$this->_wppepvn_wpdbwrapper_set_cache($keyCache,$resultData);
					}
					
				}
				
			}
			
		}
		
		return $resultData;
	}
	
	public function query($query) 
	{
		return $this->_wppepvn_wpdbwrapper_query( $query );
	}
	
	private function _wppepvn_wpdbwrapper_query( $query ) 
	{
		if ( ! $this->_wppepvn_wpdbwrapper_wpDbObj->ready ) {
			return false;
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
		
		$this->_wppepvn_wpdbwrapper_wpDbObj->flush();
		
		$this->_wppepvn_wpdbwrapper_wpDbObj->last_query = $query;
		
		$keyCacheQuery = Utils::hashKey(array(
			__METHOD__
			, $query
		));
		
		$dbData = $this->_wppepvn_wpdbwrapper_get_cache($keyCacheQuery);
		
		if(null !== $dbData) {
			
			$this->_wppepvn_wpdbwrapper_wpDbObj->last_error = '';
			
			$this->_wppepvn_wpdbwrapper_wpDbObj->last_query = $dbData['last_query'];
			$this->_wppepvn_wpdbwrapper_wpDbObj->last_result = $dbData['last_result'];
			$this->_wppepvn_wpdbwrapper_wpDbObj->col_info = $dbData['col_info'];
			$this->_wppepvn_wpdbwrapper_wpDbObj->num_rows = $dbData['num_rows'];
			
			unset($dbData);
			
			$return_val = $this->_wppepvn_wpdbwrapper_wpDbObj->num_rows;
			
		} else {
			
			$return_val = $this->_wppepvn_wpdbwrapper_wpDbObj->query( $query );
			
			if ( $return_val === false ) { // error executing sql query
				return false;
			} else {
				if(preg_match('#^([\s \t\(])*?SELECT[\s \t]+#is',$query)) {
					if(!preg_match('#(FOUND_ROWS)#is',$query)) {
						
						$dbData = array(
							'last_query' => $this->_wppepvn_wpdbwrapper_wpDbObj->last_query
							,'last_result' => $this->_wppepvn_wpdbwrapper_wpDbObj->last_result
							,'col_info' => $this->_wppepvn_wpdbwrapper_wpDbObj->col_info
							,'num_rows' => $this->_wppepvn_wpdbwrapper_wpDbObj->num_rows
						);
						
						$this->_wppepvn_wpdbwrapper_set_cache($keyCacheQuery,$dbData);
						
						unset($dbData);
						
					}
				}
			}
		}
		
		return $return_val;
	}
	
	public function get_var( $query = null, $x = 0, $y = 0 ) 
	{
		$this->_wppepvn_wpdbwrapper_wpDbObj->func_call = "\$db->get_var(\"$query\", $x, $y)";
		
		if ( $query ) {
			$this->query( $query );
		}
		// Extract var out of cached results based x,y vals
		if ( !empty( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ) ) {
			$values = array_values( get_object_vars( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ) );
		}
		// If there is a value return it else return null
		return ( isset( $values[$x] ) && $values[$x] !== '' ) ? $values[$x] : null;
	}
	
	public function get_row( $query = null, $output = OBJECT, $y = 0 ) 
	{
		$this->_wppepvn_wpdbwrapper_wpDbObj->func_call = "\$db->get_row(\"$query\",$output,$y)";
		
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}
		if ( !isset( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ) )
			return null;
		if ( $output == OBJECT ) {
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ? $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ? get_object_vars( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ) : null;
		} elseif ( $output == ARRAY_N ) {
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ? array_values( get_object_vars( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ) ) : null;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] ? $this->_wppepvn_wpdbwrapper_wpDbObj->last_result[$y] : null;
		} else {
			$this->_wppepvn_wpdbwrapper_wpDbObj->print_error( " \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N" );
		}
	}
	
	public function get_col( $query = null , $x = 0 ) 
	{
		
		if ( $query ) {
			$this->query( $query );
		}
		
		$new_array = array();
		// Extract the column values
		for ( $i = 0, $j = count( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result ); $i < $j; $i++ ) {
			$new_array[$i] = $this->get_var( null, $x, $i );
		}
		return $new_array;
	}
	
	
	public function get_results( $query = null, $output = OBJECT ) 
	{
		$this->func_call = "\$db->get_results(\"$query\", $output)";
				
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}
		$new_array = array();
		if ( $output == OBJECT ) {
			// Return an integer-keyed array of row objects
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result;
		} elseif ( $output == OBJECT_K ) {
			// Return an array of row objects with keys from column 1
			// (Duplicates are discarded)
			foreach ( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result as $row ) {
				$var_by_ref = get_object_vars( $row );
				$key = array_shift( $var_by_ref );
				if ( ! isset( $new_array[ $key ] ) )
					$new_array[ $key ] = $row;
			}
			return $new_array;
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			// Return an integer-keyed array of...
			if ( $this->_wppepvn_wpdbwrapper_wpDbObj->last_result ) {
				foreach( (array) $this->_wppepvn_wpdbwrapper_wpDbObj->last_result as $row ) {
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
			return $this->_wppepvn_wpdbwrapper_wpDbObj->last_result;
		}
		return null;
	}
}
