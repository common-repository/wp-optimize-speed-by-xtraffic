<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	, WpPepVN\DependencyInjection
	, WpPepVN\System
	, WpPepVN\Hash
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\PepVN_Cache
	, wppepvn_wpdb
	, WP_Object_Cache
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\Database as OptimizeCache_Database
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\ObjectCache as OptimizeCache_ObjectCache
;

class CacheManager
{
	public $di = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$this->_init();
	}
    
    private function _init() 
    {
		$wpExtend = $this->di->getShared('wpExtend');
		$hook = $this->di->getShared('hook');
		
		$hook->add_action('save_post_publish', array($this,'action_save_post_publish'), WP_PEPVN_PRIORITY_LAST);
		
		$hook->add_action('change_post_status', array($this,'change_post_status'), WP_PEPVN_PRIORITY_LAST);
		
		$hook->add_action('update_attachment', array($this,'action_update_attachment'), WP_PEPVN_PRIORITY_LAST);
		
		$hook->add_action('queue_jobs', array($this,'queue_jobs'), WP_PEPVN_PRIORITY_LAST);
		
		$hook->add_action('clean_cache', array($this,'action_clean_cache'), WP_PEPVN_PRIORITY_LAST);
		
	}
	
    private function _register_clean_cache($cacheTags) 
    {
		$cacheTags = array_values($cacheTags);
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		$optimizeSpeed = $this->di->getShared('optimizeSpeed');
		
		$tmp = $optimizeSpeed->getCacheTags();
		
		if($tmp && !empty($tmp)) {
			$cacheTags = array_merge($tmp, $cacheTags);
		}
		
		unset($tmp);
		
		$cacheTags[] = 'tp-home';
		$cacheTags[] = 'tp-others';
		$cacheTags[] = 'tp-error_404';
		$cacheTags[] = 'db';
		
		$cacheTags = array_unique($cacheTags);
		
		$urlsNeedClean = array();
		
		foreach($cacheTags as $key1 => $value1) {
			$tmp1 = explode('-',$value1,2);
			if(isset($tmp1[0]) && isset($tmp1[1])) {
				if('post_id' === $tmp1[0]) {
					$tmp2 = $wpExtend->getAndParsePostByPostId($tmp1[1]);
					if(isset($tmp2['postPermalink']) && $tmp2['postPermalink']) {
						$urlsNeedClean[] = $tmp2['postPermalink'];
					}
					unset($tmp2);
				} else if('term_id' === $tmp1[0]) {
					$tmp2 = $wpExtend->getTermsTaxonomiesByTermId($tmp1[1]);
					if($tmp2 && !empty($tmp2)) {
						foreach($tmp2 as $key2 => $value2) {
							unset($tmp2[$key2]);
							if(isset($value2['termLink']) && $value2['termLink']) {
								$urlsNeedClean[] = $value2['termLink'];
							}
							unset($key2,$value2);
						}
					}
					
					unset($tmp2);
				}
				
				
			}
		}
		
		$urlsNeedClean[] = $wpExtend->get_home_url();
		
		$urlsNeedClean2 = array();
		
		$urlsNeedClean = array_unique($urlsNeedClean);
		
		if(!empty($urlsNeedClean)) {
			foreach($urlsNeedClean as $url) {
				$url = Utils::removeScheme($url);
				
				$urlsNeedClean2[] = 'http:'.$url;
				$urlsNeedClean2[] = 'https:'.$url;
				
				$urlsNeedClean2[] = wppepvn_trailingslashurl('http:'.$url).'feed/';
				$urlsNeedClean2[] = wppepvn_trailingslashurl('https:'.$url).'feed/';
				
			}
		}
		
		unset($urlsNeedClean);
		
		if(!empty($urlsNeedClean2)) {
			$queue = $this->di->getShared('queue');
			$queue->add(
				'clean_cache_by_urls'
				, array(
					'urls' => $urlsNeedClean2
				)
			);
			
			foreach($urlsNeedClean2 as $url) {
				$cacheTags[] = 'pmlh-'.Hash::crc32b($url);
			}
		}
		
		$cacheTags = array_values($cacheTags);
		$cacheTags = array_unique($cacheTags);
		
		wppepvn_register_clean_cache(',common,', array(
			'cache_tags' => $cacheTags
			,'urls' => $urlsNeedClean2
		));
		
	}
	
	public function queue_jobs($params) 
    {
		
		if(isset($params['job_name'])) {
			
			if('clean_cache_by_urls' === $params['job_name']) {
				
				if(isset($params['job_data']['urls']) && $params['job_data']['urls'] && !empty($params['job_data']['urls'])) {
					
					$cloudFlare = $this->di->getShared('cloudFlare');
					
					foreach($params['job_data']['urls'] as $url) {
						$cloudFlare->purge_url($url);
					}
				}
				
				$wpExtend = $this->di->getShared('wpExtend');
				
				$remote = $this->di->getShared('remote');
				
				$arrayUrlNeedRequest = array(
					array(
						'url' => $wpExtend->get_home_url()
						,'config' => array(
							'method' => 'PURGE',
							'headers' => array( 
								'host' => PepVN_Data::$defaultParams['fullDomainName'], 
								'X-Purge-Method' => 'default'
							),
							'timeout'     => 1,
							'redirection'     => 1,
						)
					)
					
					, array(
						'url' => ($wpExtend->is_ssl() ? 'https://' : 'http://').'127.0.0.1/'
						,'config' => array(
							'method' => 'PURGE',
							'headers' => array( 
								'host' => PepVN_Data::$defaultParams['fullDomainName'], 
								'X-Purge-Method' => 'default'
							),
							'timeout'     => 1,
							'redirection'     => 1,
						)
					)
				);
				
				foreach($arrayUrlNeedRequest as $value1) {
					$remote->request($value1['url'], $value1['config']);
				}
				
			}
		}
	}
	
	public function action_clean_cache($params) 
    {
		$cacheRequestUri = $this->di->getShared('cacheRequestUri');
		$wpExtend = $this->di->getShared('wpExtend');
		$cloudFlare = $this->di->getShared('cloudFlare');
		
		$filesPathNeedRemove = array();
		
		if(
			isset($params['type']['all'])
		) {
			$folderPath = $cacheRequestUri->get_cache_host_folder_path($wpExtend->get_home_url());
		
			System::rmdirR($folderPath,true);
			
			$cloudFlare->purge_all();
		} else {
			
			$folderPath = $cacheRequestUri->get_cache_host_folder_path($wpExtend->get_home_url());
			if(is_dir($folderPath)) {
				$tmp = glob($folderPath.'*.html');
				if($tmp && !empty($tmp)) {
					$filesPathNeedRemove = array_merge($filesPathNeedRemove,$tmp);
				}
				
				$tmp = glob($folderPath.'*.xml');
				if($tmp && !empty($tmp)) {
					$filesPathNeedRemove = array_merge($filesPathNeedRemove,$tmp);
				}
			}
			
			if(
				isset($params['data']['urls'])
				&& $params['data']['urls']
				&& !empty($params['data']['urls'])
			) {
				foreach($params['data']['urls'] as $url) {
					$folderPath = $cacheRequestUri->get_cache_full_folder_path($url);
					if(is_dir($folderPath)) {
						$tmp = glob($folderPath.'*.html');
						if($tmp && !empty($tmp)) {
							$filesPathNeedRemove = array_merge($filesPathNeedRemove,$tmp);
						}
						
						$tmp = glob($folderPath.'*.xml');
						if($tmp && !empty($tmp)) {
							$filesPathNeedRemove = array_merge($filesPathNeedRemove,$tmp);
						}
					}
					
				}
			}
		}
		
		if(!empty($filesPathNeedRemove)) {
			$filesPathNeedRemove = array_unique($filesPathNeedRemove);
			foreach($filesPathNeedRemove as $filePath) {
				if($filePath) {
					System::unlink($filePath);
				}
			}
		}
		
		global $wpOptimizeSpeedByxTraffic_AdvancedCache;
		
		if(
			isset($params['type']['all'])
		) {
			if(isset($wpOptimizeSpeedByxTraffic_AdvancedCache) && $wpOptimizeSpeedByxTraffic_AdvancedCache) {
				$wpOptimizeSpeedByxTraffic_AdvancedCache->clean_cache(
					PepVN_Cache::CLEANING_MODE_ALL
					, array()
				);
			}
			
			if(isset(OptimizeCache_Database::$wppepvn_cache_object) && OptimizeCache_Database::$wppepvn_cache_object) {
				OptimizeCache_Database::$wppepvn_cache_object->clean_all_methods();
				OptimizeCache_Database::$wppepvn_cache_object->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
				));
			}
			
			if(isset(OptimizeCache_ObjectCache::$wppepvn_cache_object) && OptimizeCache_ObjectCache::$wppepvn_cache_object) {
				OptimizeCache_ObjectCache::$wppepvn_cache_object->clean_all_methods();
				OptimizeCache_ObjectCache::$wppepvn_cache_object->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
				));
			}
			
		} else if(
			isset($params['data']['cache_tags'])
		) {
			
			if(isset($wpOptimizeSpeedByxTraffic_AdvancedCache) && $wpOptimizeSpeedByxTraffic_AdvancedCache) {
				$wpOptimizeSpeedByxTraffic_AdvancedCache->clean_cache(
					PepVN_Cache::CLEANING_MODE_MATCHING_ANY_TAG
					, $params['data']['cache_tags']
				);
			}
			
			if(isset(OptimizeCache_Database::$wppepvn_cache_object) && OptimizeCache_Database::$wppepvn_cache_object) {
				OptimizeCache_Database::$wppepvn_cache_object->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_MATCHING_ANY_TAG
					,'tags' => $params['data']['cache_tags']
				));
			}
			
			if(isset(OptimizeCache_ObjectCache::$wppepvn_cache_object) && OptimizeCache_ObjectCache::$wppepvn_cache_object) {
				OptimizeCache_ObjectCache::$wppepvn_cache_object->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_MATCHING_ANY_TAG
					,'tags' => $params['data']['cache_tags']
				));
			}
			
		}
		
		OptimizeSpeed::setStaticOption();
	}
	
	public function action_save_post_publish($params) 
    {
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		if(isset($params['post']) && $params['post'] && is_object($params['post'])) {
			$params['post'] = $wpExtend->parsePostData($params['post']);
			
			$cacheTagsNeedClean = array();
			
			if(isset($params['post']['cacheTags']) && !empty($params['post']['cacheTags'])) {
				$cacheTagsNeedClean = array_merge($cacheTagsNeedClean, $params['post']['cacheTags']);
				$cacheTagsNeedClean[] = 'autid-'.$params['post']['post_author'];
			}
			
			if(!empty($cacheTagsNeedClean)) {
				$this->_register_clean_cache($cacheTagsNeedClean);
			}
			
		}
		
	}
	
	public function change_post_status($params) 
    {
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		if(isset($params['post']) && $params['post'] && is_object($params['post'])) {
			$params['post'] = $wpExtend->parsePostData($params['post']);
			
			$cacheTagsNeedClean = array();
			
			if(isset($params['post']['cacheTags']) && !empty($params['post']['cacheTags'])) {
				$cacheTagsNeedClean = array_merge($cacheTagsNeedClean, $params['post']['cacheTags']);
				$cacheTagsNeedClean[] = 'autid-'.$params['post']['post_author'];
			}
			
			if(isset($params['post']['post_type']) && ('attachment' === $params['post']['post_type'])) {//attachment
				$this->action_update_attachment($params['post']['ID']);
			}
			
			if(!empty($cacheTagsNeedClean)) {
				$cacheTagsNeedClean = array_unique($cacheTagsNeedClean);
				$this->_register_clean_cache($cacheTagsNeedClean);
			}
			
		}
		
	}
	
	public function action_update_attachment($post_id) 
    {
		$post_id = (int)$post_id;
		
		$cacheTagsNeedClean = array();
		
		$wpExtend = $this->di->getShared('wpExtend');

		$post = $wpExtend->getAndParsePostByPostId($post_id);
		
		if(isset($post['cacheTags']) && !empty($post['cacheTags'])) {
			$cacheTagsNeedClean = array_merge($cacheTagsNeedClean, $post['cacheTags']);
			$cacheTagsNeedClean[] = 'autid-'.$post['post_author'];
		}
		
		if(isset($post['post_parent']) && $post['post_parent']) {
			$post['post_parent'] = (int)$post['post_parent'];
			
			$post_parent = $wpExtend->getAndParsePostByPostId($post['post_parent']);
			
			if(isset($post_parent['cacheTags']) && !empty($post_parent['cacheTags'])) {
				$cacheTagsNeedClean = array_merge($cacheTagsNeedClean, $post_parent['cacheTags']);
				$cacheTagsNeedClean[] = 'autid-'.$post_parent['post_author'];
			}
		}
		
		unset($post,$post_parent);
		
		if(!empty($cacheTagsNeedClean)) {
			
			$cacheTagsNeedClean = array_unique($cacheTagsNeedClean);
			
			if(class_exists('wppepvn_wpdb')) {
				wppepvn_wpdb::$wppepvn_configs['disable_cache'] = true;
			}
			
			wppepvn_clean_cache(',common,', array(
				'cache_tags' => $cacheTagsNeedClean
			));
			
		}
		
		$this->_register_clean_cache($cacheTagsNeedClean);
		
	}
	
}