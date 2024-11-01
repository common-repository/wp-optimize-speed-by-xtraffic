<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	,WPOptimizeByxTraffic\Application\Service\PepVN_Data
	,WPOptimizeByxTraffic\Application\Service\PepVN_Cache
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\ObjectCache as OptimizeCache_ObjectCache
;

class WPObjectCacheWrapper
{
	/*
	* Wp Code
	*/
	
	/**
	 * Holds the cached objects
	 *
	 * @var array
	 * @access private
	 * @since 2.0.0
	 */
	private $cache = array();
	/**
	 * The amount of times the cache data was already stored in the cache.
	 *
	 * @since 2.5.0
	 * @access private
	 * @var int
	 */
	private $cache_hits = 0;
	/**
	 * Amount of times the cache did not have the request in cache
	 *
	 * @var int
	 * @access public
	 * @since 2.0.0
	 */
	public $cache_misses = 0;
	/**
	 * List of global groups
	 *
	 * @var array
	 * @access protected
	 * @since 3.0.0
	 */
	protected $global_groups = array();
	/**
	 * The blog prefix to prepend to keys in non-global groups.
	 *
	 * @var int
	 * @access private
	 * @since 3.5.0
	 */
	private $blog_prefix;
	/**
	 * Holds the value of `is_multisite()`
	 *
	 * @var bool
	 * @access private
	 * @since 3.5.0
	 */
	private $multisite;
	/**
	 * Make private properties readable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		return $this->$name;
	}
	/**
	 * Make private properties settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name  Property to set.
	 * @param mixed  $value Property value.
	 * @return mixed Newly-set property.
	 */
	public function __set( $name, $value ) {
		return $this->$name = $value;
	}
	/**
	 * Make private properties checkable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}
	/**
	 * Make private properties un-settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to unset.
	 */
	public function __unset( $name ) {
		unset( $this->$name );
	}
	/**
	 * Adds data to the cache if it doesn't already exist.
	 *
	 * @uses WP_Object_Cache::_exists Checks to see if the cache already has data.
	 * @uses WP_Object_Cache::set Sets the data after the checking the cache
	 *		contents existence.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key What to call the contents in the cache
	 * @param mixed $data The contents to store in the cache
	 * @param string $group Where to group the cache contents
	 * @param int $expire When to expire the cache contents
	 * @return bool False if cache key and group already exist, true on success
	 */
	 
	 
	/*
	* Custom Code
	*/
	public $wppepvn_objectcache_init_status = true;
	
	private $_wppepvn_wpExtend = false;
	
	private static $_wppepvn_tempData = array();
	
	private static $_wppepvn_configs = array();
	
	private $_wppepvn_can_cache_dynamic = null;
	
	private $_wppepvn_cacheable = false;
	
	private $_wppepvn_di = false;
	
	private $_nonpersistent_groups = array();
	private $_nonpersistent_groups_flipped = array();
	
	/**
	 * Sets up object properties; PHP 5 style constructor
	 *
	 * @since 2.0.8
	 *
     * @global int $blog_id
	 */
	public function __construct(DependencyInjection $di, $wp_object_cache) 
	{
		global $blog_id;
		$this->multisite = is_multisite();
		$this->blog_prefix =  $this->multisite ? $blog_id . ':' : '';
		/**
		 * @todo This should be moved to the PHP4 style constructor, PHP5
		 * already calls __destruct()
		 */
		register_shutdown_function( array( $this, '__destruct' ) );
		
		
		/*
		* Custom Code
		*/
		global $_wp_using_ext_object_cache;
		
		$this->_wppepvn_di = $di;
		
		$this->_wppepvn_wpExtend = $this->_wppepvn_di->getShared('wpExtend');
		
		self::$_wppepvn_configs['group_can_cache'] = array(
			'transient' => true
			, 'site-transient' => true
		);
		
		$_wp_using_ext_object_cache = true;
		
		$tmp = array(
			'cache'
			,'cache_hits'
			,'cache_misses'
			,'global_groups'
			,'blog_prefix'
			,'multisite'
		);
		
		foreach($tmp as $name) {
			if(isset($wp_object_cache->$name)) {
				if(is_object($wp_object_cache->$name)) {
					$this->$name = clone $wp_object_cache->$name;
				} else {
					$this->$name = $wp_object_cache->$name;
				}
			}
		}
		
		$this->global_groups = array_merge($this->global_groups, array(
			'users'
			,'userlogins'
			,'usermeta'
			,'user_meta'
			,'site-transient'
			,'site-options'
			,'site-lookup'
			,'blog-lookup'
			,'blog-details'
			,'rss'
			,'global-posts'
			,'wppepvn-global'
		));
		
		//Groups can cache
		$this->global_groups = array_unique($this->global_groups);
		
		//Groups not cache
		$this->_wppepvn_add_nonpersistent_groups(array(
			'comment'
			,'counts'
			,'plugins'
			,'nonpersistent'
		));
		
	}
	
	
	private function _wppepvn_get_cache($keyCache) 
	{
		$resultData = null;
		
		$cacheTags = $this->_wppepvn_get_current_cache_tags();
		
		if(isset(self::$_wppepvn_tempData[$keyCache])) {
			$resultData = self::$_wppepvn_tempData[$keyCache];
		}
		
		if(null === $resultData) {
			$resultData = OptimizeCache_ObjectCache::$wppepvn_cache_object->get_cache($keyCache);
			
			if(null !== $resultData) {
				self::$_wppepvn_tempData[$keyCache] = $resultData;
			}
		}
		
		return $resultData;
	}
	
	private function _wppepvn_set_cache($keyCache, $data)
	{
		$cacheTags = $this->_wppepvn_get_current_cache_tags();
		
		self::$_wppepvn_tempData[$keyCache] = $data;
		
		OptimizeCache_ObjectCache::$wppepvn_cache_object->set_cache($keyCache, $data, $cacheTags, false, array(
			//'merge_tags' => true
		));
		
		//PepVN_Data::$cacheFileByTagObject->set_cache($keyCache, $data, $cacheTags, false, array('merge_tags' => true));
		
	}
	
	private function _wppepvn_delete_cache($keyCache)
	{
		if(isset(self::$_wppepvn_tempData[$keyCache])) {
			unset(self::$_wppepvn_tempData[$keyCache]);
		}
		
		OptimizeCache_ObjectCache::$wppepvn_cache_object->delete_cache($keyCache);
		
		//PepVN_Data::$cacheFileByTagObject->delete_cache($keyCache);
	}
	
	private function _wppepvn_clean_all_cache()
	{
		self::$_wppepvn_tempData = array();
	}
	
	private function _wppepvn_get_current_cache_tags()
	{
		/*
		$optimizeSpeed = $this->_wppepvn_di->getShared('optimizeSpeed');
		
		$cacheTags = $optimizeSpeed->getCacheTags();
		*/
		
		$cacheTags = array();
		
		$cacheTags[] = 'tp-others';
		
		//$cacheTags = array_values($cacheTags);
		//$cacheTags = array_unique($cacheTags);
		
		return $cacheTags;
	}
	
	
    /**
     * Returns if we can cache, that condition can change in runtime
     *
     * @param $group
     * @return boolean
     */
    private function _wppepvn_is_can_cache_runtime($group) 
	{
        //Need to be handled in wp admin as well as frontend
		
        if(isset(self::$_wppepvn_configs['group_can_cache'][$group])) {
            return true;
		}
		
		if ($this->_wppepvn_can_cache_dynamic !== null) {
            return $this->_wppepvn_can_cache_dynamic;
		}
		
		if ($this->_wppepvn_cacheable) {
            if(
				$this->_wppepvn_wpExtend->is_admin()
				|| $this->_wppepvn_wpExtend->isLoginPage()
			) {	//not cache admin page
                $this->_wppepvn_can_cache_dynamic = false;
                return $this->_wppepvn_can_cache_dynamic;
            }
        }

        return $this->_wppepvn_cacheable;
		
    }
	
	private function _wppepvn_is_group_can_cache($group) 
	{
		if($this->_wppepvn_is_can_cache_runtime($group)) {
			if(!isset($this->_nonpersistent_groups_flipped[$group])) {
				return true;
			}
		}
		
		return false;
	}
	
	private function _wppepvn_get_key($key, $group = 'default') 
	{
		if ( empty( $group ) ) {
			$group = 'default';
		}
		
		return Utils::hashKey(array($key, $group));
	}
	
	private function _wppepvn_add_nonpersistent_groups($groups)
	{
		$groups = (array) $groups;
		
        $this->_nonpersistent_groups = array_merge($this->_nonpersistent_groups, $groups);
        $this->_nonpersistent_groups = array_unique($this->_nonpersistent_groups);
		
		$this->_nonpersistent_groups_flipped = array_flip($this->_nonpersistent_groups);
	}
	
	public function add( $key, $data, $group = 'default', $expire = 0 ) 
	{
		if ( wp_suspend_cache_addition() )
			return false;
		if ( empty( $group ) )
			$group = 'default';
		$id = $key;
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$id = $this->blog_prefix . $key;
		
		if ( $this->_exists( $id, $group ) )
			return false;
		
		return $this->set( $key, $data, $group, (int) $expire );
	}
	
	/**
	 * Sets the list of global groups.
	 *
	 * @since 3.0.0
	 *
	 * @param array $groups List of groups that are global.
	 */
	public function add_global_groups( $groups ) {
		$groups = (array) $groups;
		$groups = array_fill_keys( $groups, true );
		$this->global_groups = array_merge( $this->global_groups, $groups );
	}
	
	/**
	 * Decrement numeric cache item's value
	 *
	 * @since 3.3.0
	 *
	 * @param int|string $key The cache key to increment
	 * @param int $offset The amount by which to decrement the item's value. Default is 1.
	 * @param string $group The group the key is in.
	 * @return false|int False on failure, the item's new value on success.
	 */
	
	public function decr( $key, $offset = 1, $group = 'default' ) {
		if ( empty( $group ) )
			$group = 'default';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$key = $this->blog_prefix . $key;
		if ( ! $this->_exists( $key, $group ) )
			return false;
		if ( ! is_numeric( $this->cache[ $group ][ $key ] ) )
			$this->cache[ $group ][ $key ] = 0;
		$offset = (int) $offset;
		$this->cache[ $group ][ $key ] -= $offset;
		if ( $this->cache[ $group ][ $key ] < 0 )
			$this->cache[ $group ][ $key ] = 0;
		return $this->cache[ $group ][ $key ];
	}
	/**
	 * Remove the contents of the cache key in the group
	 *
	 * If the cache key does not exist in the group, then nothing will happen.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key What the contents in the cache are called
	 * @param string $group Where the cache contents are grouped
	 * @param bool $deprecated Deprecated.
	 *
	 * @return bool False if the contents weren't deleted and true on success
	 */
	public function delete( $key, $group = 'default', $deprecated = false ) {
		if ( empty( $group ) )
			$group = 'default';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$key = $this->blog_prefix . $key;
		if ( ! $this->_exists( $key, $group ) )
			return false;
		if(isset($this->cache[$group][$key])) 
			unset( $this->cache[$group][$key] );
		
		if($this->_wppepvn_is_group_can_cache($group)) {
			$pepKeyCache = $this->_wppepvn_get_key($key,$group);
			//OptimizeCache_ObjectCache::$wppepvn_cache_object->delete_cache($pepKeyCache);
			$this->_wppepvn_delete_cache($pepKeyCache);
		}
		
		return true;
	}
	/**
	 * Clears the object cache of all data
	 *
	 * @since 2.0.0
	 *
	 * @return true Always returns true
	 */
	public function flush() {
		$this->cache = array();
		$this->_wppepvn_clean_all_cache();
		return true;
	}
	/**
	 * Retrieves the cache contents, if it exists
	 *
	 * The contents will be first attempted to be retrieved by searching by the
	 * key in the cache group. If the cache is hit (success) then the contents
	 * are returned.
	 *
	 * On failure, the number of cache misses will be incremented.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key What the contents in the cache are called
	 * @param string $group Where the cache contents are grouped
	 * @param string $force Whether to force a refetch rather than relying on the local cache (default is false)
	 * @return false|mixed False on failure to retrieve contents or the cache
	 *		               contents on success
	 */
	 
	public function get( $key, $group = 'default', $force = false, &$found = null ) {
		if ( empty( $group ) )
			$group = 'default';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$key = $this->blog_prefix . $key;
		if ( $this->_exists( $key, $group ) ) {
			$found = true;
			$this->cache_hits += 1;
			if ( is_object($this->cache[$group][$key]) )
				return clone $this->cache[$group][$key];
			else
				return $this->cache[$group][$key];
		}
		$found = false;
		$this->cache_misses += 1;
		return false;
	}
	
	/**
	 * Increment numeric cache item's value
	 *
	 * @since 3.3.0
	 *
	 * @param int|string $key The cache key to increment
	 * @param int $offset The amount by which to increment the item's value. Default is 1.
	 * @param string $group The group the key is in.
	 * @return false|int False on failure, the item's new value on success.
	 */
	public function incr( $key, $offset = 1, $group = 'default' ) {
		if ( empty( $group ) )
			$group = 'default';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$key = $this->blog_prefix . $key;
		if ( ! $this->_exists( $key, $group ) )
			return false;
		if ( ! is_numeric( $this->cache[ $group ][ $key ] ) )
			$this->cache[ $group ][ $key ] = 0;
		$offset = (int) $offset;
		$this->cache[ $group ][ $key ] += $offset;
		if ( $this->cache[ $group ][ $key ] < 0 )
			$this->cache[ $group ][ $key ] = 0;
		return $this->cache[ $group ][ $key ];
	}
	/**
	 * Replace the contents in the cache, if contents already exist
	 *
	 * @since 2.0.0
	 * @see WP_Object_Cache::set()
	 *
	 * @param int|string $key What to call the contents in the cache
	 * @param mixed $data The contents to store in the cache
	 * @param string $group Where to group the cache contents
	 * @param int $expire When to expire the cache contents
	 * @return bool False if not exists, true if contents were replaced
	 */
	public function replace( $key, $data, $group = 'default', $expire = 0 ) {
		if ( empty( $group ) )
			$group = 'default';
		$id = $key;
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$id = $this->blog_prefix . $key;
		if ( ! $this->_exists( $id, $group ) )
			return false;
		return $this->set( $key, $data, $group, (int) $expire );
	}
	/**
	 * Reset keys
	 *
	 * @since 3.0.0
	 * @deprecated 3.5.0
	 */
	public function reset() {
		_deprecated_function( __FUNCTION__, '3.5', 'switch_to_blog()' );
		// Clear out non-global caches since the blog ID has changed.
		$groups = array_keys( $this->cache );
		foreach ( $groups as $group ) {
			if ( ! isset( $this->global_groups[ $group ] ) ) {
				unset( $this->cache[ $group ] );
			}
			
			if($this->_wppepvn_is_group_can_cache($group)) {
				OptimizeCache_ObjectCache::$wppepvn_cache_object->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_MATCHING_ANY_TAG
					,'tags' => $group
				));
			}
			
		}
		
		global $_wp_using_ext_object_cache;

        $_wp_using_ext_object_cache = $this->_wppepvn_cacheable;

        return true;
	}
	/**
	 * Sets the data contents into the cache
	 *
	 * The cache contents is grouped by the $group parameter followed by the
	 * $key. This allows for duplicate ids in unique groups. Therefore, naming of
	 * the group should be used with care and should follow normal function
	 * naming guidelines outside of core WordPress usage.
	 *
	 * The $expire parameter is not used, because the cache will automatically
	 * expire for each time a page is accessed and PHP finishes. The method is
	 * more for cache plugins which use files.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key What to call the contents in the cache
	 * @param mixed $data The contents to store in the cache
	 * @param string $group Where to group the cache contents
	 * @param int $expire Not Used
	 * @return true Always returns true
	 */
	public function set( $key, $data, $group = 'default', $expire = 0 ) {
		if ( empty( $group ) )
			$group = 'default';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) )
			$key = $this->blog_prefix . $key;
		if ( is_object( $data ) )
			$data = clone $data;
		$this->cache[$group][$key] = $data;
		
		if($this->_wppepvn_is_group_can_cache($group)) {
			$pepKeyCache = $this->_wppepvn_get_key($key,$group);
			
			$pepCacheData = OptimizeCache_ObjectCache::$wppepvn_cache_object->set_cache($pepKeyCache, $data, (array)$group, (int)$expire);
		}
		
		return true;
	}
	/**
	 * Echoes the stats of the caching.
	 *
	 * Gives the cache hits, and cache misses. Also prints every cached group,
	 * key and the data.
	 *
	 * @since 2.0.0
	 */
	public function stats() {
		echo "<p>";
		echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
		echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
		echo "</p>";
		echo '<ul>';
		foreach ($this->cache as $group => $cache) {
			echo "<li><strong>Group:</strong> $group - ( " . number_format( strlen( serialize( $cache ) ) / 1024, 2 ) . 'k )</li>';
		}
		echo '</ul>';
	}
	/**
	 * Switch the interal blog id.
	 *
	 * This changes the blog id used to create keys in blog specific groups.
	 *
	 * @since 3.5.0
	 *
	 * @param int $blog_id Blog ID
	 */
	public function switch_to_blog( $blog_id ) {
		$blog_id = (int) $blog_id;
		$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';
	}
	/**
	 * Utility function to determine whether a key exists in the cache.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 * @param string $key
	 * @param string $group
	 * @return bool
	 */
	protected function _exists( $key, $group ) {
		//return isset( $this->cache[ $group ] ) && ( isset( $this->cache[ $group ][ $key ] ) || array_key_exists( $key, $this->cache[ $group ] ) );
		
		if(isset( $this->cache[ $group ] ) && ( isset( $this->cache[ $group ][ $key ] ) || array_key_exists( $key, $this->cache[ $group ] ) )) {
			return true;
		} else {
			if($this->_wppepvn_is_group_can_cache($group)) {
				
				$pepKeyCache = $this->_wppepvn_get_key($key,$group);
				
				$data = $this->_wppepvn_get_cache($pepKeyCache);
				//$data = OptimizeCache_ObjectCache::$wppepvn_cache_object->get_cache($pepKeyCache);
				
				if(null !== $data) {
					$this->cache[$group][$key] = $data;
					return true;
				}
			}
			
		}
		
		return false;
	}
	
	/**
	 * Will save the object cache before object is completely destroyed.
	 *
	 * Called upon object destruction, which should be when PHP ends.
	 *
	 * @since  2.0.8
	 *
	 * @return true True value. Won't be used by PHP
	 */
	public function __destruct() {
		return true;
	}
	
}
