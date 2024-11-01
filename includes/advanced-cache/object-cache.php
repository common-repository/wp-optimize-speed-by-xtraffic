<?php
/**
 * Object Cache API
 *
 * @link https://codex.wordpress.org/Function_Reference/WP_Cache
 *
 * @package WordPress
 * @subpackage Cache
 */

/**
 * Adds data to the cache, if the cache key doesn't already exist.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key The cache key to use for retrieval later
 * @param mixed $data The data to add to the cache store
 * @param string $group The group to add the cache to
 * @param int $expire When the cache data should be expired
 * @return bool False if cache key and group already exist, true on success
 */
function wp_cache_add( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->add( $key, $data, $group, (int) $expire );
}

/**
 * Closes the cache.
 *
 * This function has ceased to do anything since WordPress 2.5. The
 * functionality was removed along with the rest of the persistent cache. This
 * does not mean that plugins can't implement this function when they need to
 * make sure that the cache is cleaned up after WordPress no longer needs it.
 *
 * @since 2.0.0
 *
 * @return true Always returns True
 */
function wp_cache_close() {
	return true;
}

/**
 * Decrement numeric cache item's value
 *
 * @since 3.3.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key The cache key to increment
 * @param int $offset The amount by which to decrement the item's value. Default is 1.
 * @param string $group The group the key is in.
 * @return false|int False on failure, the item's new value on success.
 */
function wp_cache_decr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->decr( $key, $offset, $group );
}

/**
 * Removes the cache contents matching key and group.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key What the contents in the cache are called
 * @param string $group Where the cache contents are grouped
 * @return bool True on successful removal, false on failure
 */
function wp_cache_delete($key, $group = '') {
	global $wp_object_cache;

	return $wp_object_cache->delete($key, $group);
}

/**
 * Removes all cache items.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool False on failure, true on success
 */
function wp_cache_flush() {
	global $wp_object_cache;

	return $wp_object_cache->flush();
}

/**
 * Retrieves the cache contents from the cache by key and group.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key What the contents in the cache are called
 * @param string $group Where the cache contents are grouped
 * @param bool $force Whether to force an update of the local cache from the persistent cache (default is false)
 * @param bool &$found Whether key was found in the cache. Disambiguates a return of false, a storable value.
 * @return bool|mixed False on failure to retrieve contents or the cache
 *		              contents on success
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	global $wp_object_cache;

	return $wp_object_cache->get( $key, $group, $force, $found );
}

/**
 * Increment numeric cache item's value
 *
 * @since 3.3.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key The cache key to increment
 * @param int $offset The amount by which to increment the item's value. Default is 1.
 * @param string $group The group the key is in.
 * @return false|int False on failure, the item's new value on success.
 */
function wp_cache_incr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->incr( $key, $offset, $group );
}

/**
 * Sets up Object Cache Global and assigns it.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 */
function wp_cache_init() {
	$GLOBALS['wp_object_cache'] = new WP_Object_Cache();
}

/**
 * Replaces the contents of the cache with new data.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key What to call the contents in the cache
 * @param mixed $data The contents to store in the cache
 * @param string $group Where to group the cache contents
 * @param int $expire When to expire the cache contents
 * @return bool False if not exists, true if contents were replaced
 */
function wp_cache_replace( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->replace( $key, $data, $group, (int) $expire );
}

/**
 * Saves the data to the cache.
 *
 * @since 2.0.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int|string $key What to call the contents in the cache
 * @param mixed $data The contents to store in the cache
 * @param string $group Where to group the cache contents
 * @param int $expire When to expire the cache contents
 * @return bool False on failure, true on success
 */
function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->set( $key, $data, $group, (int) $expire );
}

/**
 * Switch the interal blog id.
 *
 * This changes the blog id used to create keys in blog specific groups.
 *
 * @since 3.5.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param int $blog_id Blog ID
 */
function wp_cache_switch_to_blog( $blog_id ) {
	global $wp_object_cache;

	$wp_object_cache->switch_to_blog( $blog_id );
}

/**
 * Adds a group or set of groups to the list of global groups.
 *
 * @since 2.6.0
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @param string|array $groups A group or an array of groups to add
 */
function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;

	$wp_object_cache->add_global_groups( $groups );
}

/**
 * Adds a group or set of groups to the list of non-persistent groups.
 *
 * @since 2.6.0
 *
 * @param string|array $groups A group or an array of groups to add
 */
function wp_cache_add_non_persistent_groups( $groups ) {
	// Default cache doesn't persist so nothing to do here.
}

/**
 * Reset internal cache keys and structures. If the cache backend uses global
 * blog or site IDs as part of its cache keys, this function instructs the
 * backend to reset those keys and perform any cleanup since blog or site IDs
 * have changed since cache init.
 *
 * This function is deprecated. Use wp_cache_switch_to_blog() instead of this
 * function when preparing the cache for a blog switch. For clearing the cache
 * during unit tests, consider using wp_cache_init(). wp_cache_init() is not
 * recommended outside of unit tests as the performance penality for using it is
 * high.
 *
 * @since 2.6.0
 * @deprecated 3.5.0
 *
 * @global WP_Object_Cache $wp_object_cache
 */
function wp_cache_reset() {
	_deprecated_function( __FUNCTION__, '3.5' );

	global $wp_object_cache;

	$wp_object_cache->reset();
}

/**
 * WordPress Object Cache
 *
 * The WordPress Object Cache is used to save on trips to the database. The
 * Object Cache stores all of the cache data to memory and makes the cache
 * contents available by using a key, which is used to name and later retrieve
 * the cache contents.
 *
 * The Object Cache can be replaced by other caching mechanisms by placing files
 * in the wp-content folder which is looked at in wp-settings. If that file
 * exists, then this file will not be included.
 *
 * @package WordPress
 * @subpackage Cache
 * @since 2.0.0
 */
class WP_Object_Cache {

	private static $_wppepvn_tempData = array();
	
	private static $_wppepvn_configs = array();
	
	public static $wppepvn_cache_object = false;
	
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
	public function add( $key, $data, $group = 'default', $expire = 0 ) {
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

		unset( $this->cache[$group][$key] );
		
		if($this->_wppepvn_is_group_can_cache($group)) {
			$wppepvn_KeyCache = $this->_wppepvn_get_key($key,$group);
			$this->_wppepvn_delete_cache($wppepvn_KeyCache);
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
		foreach ( array_keys( $this->cache ) as $group ) {
			if ( ! isset( $this->global_groups[ $group ] ) )
				unset( $this->cache[ $group ] );
		}
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
			$wppepvn_KeyCache = $this->_wppepvn_get_key($key,$group);
			$this->_wppepvn_set_cache($wppepvn_KeyCache,$data,(int)$expire);
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
		
		$status = false;
		
		if(isset( $this->cache[ $group ] ) && ( isset( $this->cache[ $group ][ $key ] ) || array_key_exists( $key, $this->cache[ $group ] ) )) {
			$status = true;
		}
		
		if(!$status) {
			
			if($this->_wppepvn_is_group_can_cache($group)) {
				$wppepvn_KeyCache = $this->_wppepvn_get_key($key,$group);
				
				$data = $this->_wppepvn_get_cache($wppepvn_KeyCache);
				
				if(null !== $data) {
					$this->cache[$group][$key] = $data;
					$status = true;
				}
			}
			
		}
		
		return $status;
		
	}

	/**
	 * Sets up object properties; PHP 5 style constructor
	 *
	 * @since 2.0.8
	 *
     * @global int $blog_id
	 */
	public function __construct() {
		global $blog_id;

		$this->multisite = is_multisite();
		$this->blog_prefix =  $this->multisite ? $blog_id . ':' : '';
		
		self::_wppepvn_init_configs();
		
		self::wppepvn_init_cache_object();
		
		/**
		 * @todo This should be moved to the PHP4 style constructor, PHP5
		 * already calls __destruct()
		 */
		register_shutdown_function( array( $this, '__destruct' ) );
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
	
	/*
		Custom WPPEPVN
	*/
	private static function _wppepvn_init_configs() 
	{
		if(empty(self::$_wppepvn_configs)) {
			self::$_wppepvn_configs = array();
			
			self::$_wppepvn_configs['persistent_groups'] = array(
				'transient' => true
				, 'site-transient' => true
				, 'site-options' => true
			);
			
			self::$_wppepvn_configs['options'] = \WPOptimizeSpeedByxTraffic\AdvancedCache::getOption();
		}
	}
	
	
	private static function _getOption() 
	{
		return self::$_wppepvn_configs['options'];
	}
	
	private function _wppepvn_is_group_can_cache($group) 
	{
		if(!$group) {
			$group = 'default';
		}
		
		$cacheable = false;
		
		$options = self::$_wppepvn_configs['options']; 
		
		if(isset($options['optimize_cache_enable']) && ('on' === $options['optimize_cache_enable'])) {
			if(isset($options['optimize_cache_object_cache_enable']) && ('on' === $options['optimize_cache_object_cache_enable'])) {
				$cacheable = true;
			}
		}
		
		if($cacheable) {
			if(!isset(self::$_wppepvn_configs['persistent_groups'][$group])) {
				$cacheable = false;
			}
		}
		
		if($cacheable) {
			$cacheable = false;
			
			global $wpOptimizeSpeedByxTraffic_AdvancedCache;
			if(isset($wpOptimizeSpeedByxTraffic_AdvancedCache) && $wpOptimizeSpeedByxTraffic_AdvancedCache) {
				$cacheable = $wpOptimizeSpeedByxTraffic_AdvancedCache->checkOptionIsRequestCacheable();
			}
		}
		
		return $cacheable;
	}
	
	public static function wppepvn_init_cache_object() 
	{
		if(false == self::$wppepvn_cache_object) {
			self::$wppepvn_cache_object = self::_wppepvn_init_cache_object(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR . '/wp-optimize-speed-by-xtraffic/Application/includes/storages/cache/wpobj/', crc32(__CLASS__ . __METHOD__));
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
	
	private function _wppepvn_get_key($key, $group = 'default') 
	{
		if (!$group) {
			$group = 'default';
		}
		
		return wppepvn_hash_key(array($key, $group));
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
	
	private function _wppepvn_set_cache($keyCache, $data, $expire = 0)
	{
		self::$_wppepvn_tempData[$keyCache] = $data;
		
		$cacheTags = $this->_wppepvn_get_current_cache_tags();
		
		$expire = (int)$expire;
		
		$expire = abs($expire);
		
		self::$wppepvn_cache_object->set_cache($keyCache, $data, $cacheTags, $expire, array('merge_tags' => true));
	}
	
	private function _wppepvn_delete_cache($keyCache)
	{
		if(isset(self::$_wppepvn_tempData[$keyCache])) {
			unset(self::$_wppepvn_tempData[$keyCache]);
		}
		
		self::$wppepvn_cache_object->delete_cache($keyCache);
		
	}
	
	private function _wppepvn_clean_all_cache()
	{
		self::$_wppepvn_tempData = array();
	}
	
	private function _wppepvn_get_current_cache_tags()
	{
		global $wpOptimizeByxTraffic;
		
		$cacheTags = array();
		
		if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
			if(isset($wpOptimizeByxTraffic->di) && $wpOptimizeByxTraffic->di) {
				if(isset($wpOptimizeByxTraffic->initialized['wp'])) {
					$cacheTags = wppepvn_get_cachetags_current_request(true);
				}
			}
		}
		
		if(empty($cacheTags)) {
			$cacheTags = array('tp-others');
		}
		
		//$cacheTags[] = 'wpobj';
		
		return $cacheTags;
	}
	
}
