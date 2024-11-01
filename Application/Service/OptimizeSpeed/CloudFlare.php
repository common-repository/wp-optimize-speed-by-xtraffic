<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	, WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\TempDataAndCacheFile
	
;

class CloudFlare
{
	public $di = false;
	
	private $_tempData = array();
	
	private $_configs = array();
	
	private $_staticVarObject = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$this->_configs['urls_api']['v1'] = 'https://www.cloudflare.com/api_json.html';
		$this->_configs['urls_api']['v4'] = 'https://api.cloudflare.com/client/v4/';
		
		$this->_configs['urls_need_purge'] = array();
		
		$tmp = array(
			'domains' => array()
		);
		$this->_staticVarObject = new ServiceStaticVar(md5('WPOptimizeSpeedByxTraffic/Application/Service/OptimizeSpeed/CloudFlare/construct'), $tmp);
		
		add_action('shutdown', array($this,'wp_action_shutdown'), WP_PEPVN_PRIORITY_LAST);
	}
    
	
	/**
	 * Get unique zone data for a domain.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param string $type Type of data for a CloudFlare zone current domain belongs to.
	 * @return string $zone_data Data of a CloudFlare zone current domain belongs to.
	 */
	private function _get_zone_data( $type ) 
	{
		$domain = PepVN_Data::$defaultParams['domainName'];
		
		// If not cached, get raw
		$keyCache = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$type
			,$domain
		));
		
		$zone_data = TempDataAndCacheFile::get_cache($keyCache,true,true);
		
		if(null === $zone_data) {
			
			if($domain) {
				
				$response = $this->api_v4_request( 'zones?name=' . $domain, array( 'method' => 'GET' ) );
				// Response should have appropiate code
				if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
					return '';
				}
				
				$zone_id = '';
				
				$response_body = json_decode( wp_remote_retrieve_body( $response ) );
				
				if($response_body && isset($response_body->result[0]->id)) {
					$zone_id   = $response_body->result[0]->id;
				}
				
				$zone_data = array( 'zone_id' => $zone_id );
				// Save to cache for an hour
				TempDataAndCacheFile::set_cache($keyCache, $zone_data,true,true);
			}
		}
		
		if ( isset( $zone_data[ $type ] ) ) {
			return $zone_data[ $type ];
		} else {
			return '';
		}
	}
	
	/**
	 * Get unique zone ID for a domain.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return int $zone_id ID of a CloudFlare zone current domain belongs to.
	 */
	public function get_zone_id() 
	{
		return $this->_get_zone_data( 'zone_id' );
	}
	
	/*
		https://api.cloudflare.com/#requests
		Rate limiting
		The CloudFlare API sets a maximum of 1,200 requests in a five minute period.
	*/
	
	public function can_request() 
	{
		
		$options = OptimizeSpeed::getOption();
		
		$canRequestStatus = false;
		
		$status1 = false;
		
		if(isset($options['cdn_enable']) && ('on' === $options['cdn_enable'])) {
			if(
				isset($options['cdn_cloudflare_email']) && ($options['cdn_cloudflare_email'])
				&& isset($options['cdn_cloudflare_api_key']) && ($options['cdn_cloudflare_api_key'])
			) {
				$status1 = true;
			}
		}
		
		if(!$status1) {
			return $canRequestStatus;
		}
		
		$staticVarData = $this->_staticVarObject->get();
		
		if(!isset($staticVarData['last_time_reset_request'])) {
			$staticVarData['last_time_reset_request'] = 1;
		}
		$staticVarData['last_time_reset_request'] = (int)$staticVarData['last_time_reset_request'];
		
		if(!isset($staticVarData['last_number_request_before_reset'])) {
			$staticVarData['last_number_request_before_reset'] = 1;
		}
		$staticVarData['last_number_request_before_reset'] = (int)$staticVarData['last_number_request_before_reset'];
		
		if(($staticVarData['last_time_reset_request'] + (5 * 60)) < PepVN_Data::$defaultParams['requestTime']) {	//is timeout 
			$canRequestStatus = true;
			$staticVarData['last_time_reset_request'] = PepVN_Data::$defaultParams['requestTime'];
			$staticVarData['last_number_request_before_reset'] = 1;
		} else if($staticVarData['last_number_request_before_reset'] < 1200) {
			$canRequestStatus = true;
		}
		
		if($canRequestStatus) {
			$staticVarData['last_number_request_before_reset']++;
		}
		
		$this->_staticVarObject->save($staticVarData,'m');
		
		return $canRequestStatus;
	}
	
	/**
	 * Make CloudFlare API request.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $endpoint Endpoint of CloudFlare API URL.
	 * @param array  $args     Request arguments.
	 * @return WP_Error|array $response The response or WP_Error on failure.
	 */
	public function api_v4_request( $endpoint, $args ) 
	{
		// Have we passed limit
		$status = $this->can_request();
		if ( ! $status ) {
			return new \WP_Error( 'cloudflare-requests-limit', __( 'Requests limit passed.' ), 429 );
		}
		
		$options = OptimizeSpeed::getOption();
		
		$defaults = array(
			'headers' => array(
				'X-Auth-Email' => (isset($options['cdn_cloudflare_email']) ? $options['cdn_cloudflare_email'] : ''),
				'X-Auth-Key'   => (isset($options['cdn_cloudflare_api_key']) ? $options['cdn_cloudflare_api_key'] : ''),
				'Content-Type' => 'application/json',
			),
		);
		
		$r = wp_parse_args( $args, $defaults );
		
		$response = wp_remote_request( $this->_configs['urls_api']['v4'] . $endpoint, $r );
		
		return $response;
	}
	
    public function api_v1_request($action, $params) 
    {
		// Have we passed limit
		$status = $this->can_request();
		if ( ! $status ) {
			return new \WP_Error( 'cloudflare-requests-limit', __( 'Requests limit passed.' ), 429 );
		}
		
		$options = OptimizeSpeed::getOption();
		
		$request_params = array(
			'a' => $action
			,'tkn' => (isset($options['cdn_cloudflare_api_key']) ? $options['cdn_cloudflare_api_key'] : '')
			,'email' => (isset($options['cdn_cloudflare_email']) ? $options['cdn_cloudflare_email'] : '')
			,'z' => PepVN_Data::$defaultParams['domainName']
			
		);
		
		$request_params = array_merge($request_params, $params);
		
		$remote = $this->di->getShared('remote');
		
		$response = $remote->request($this->_configs['urls_api']['v1'], array(
			'method' => 'POST'
			,'timeout' => 2
			,'headers' => array()
			,'body' => $request_params
		));
		
		return $response;
	}
	
	/*
		This function will purge CloudFlare of any cached files. It may take up to 48 hours for the cache to rebuild and optimum performance to be achieved so this function should be used sparingly.
		There is a limit for cache purges of 5 per minute. Exceeding this limit will return an error in the JSON response.
	*/
	public function purge_all() 
    {
		
		$staticVarData = $this->_staticVarObject->get();
		
		$runStatus = true;
		
		if($runStatus) {
			if(isset($staticVarData['last_time_run_purge']) && $staticVarData['last_time_run_purge']) {
				$runStatus = false;
				if(($staticVarData['last_time_run_purge'] + (1 * 30)) < PepVN_Data::$defaultParams['requestTime']) {	//is timeout 
					$runStatus = true;
				}
			}
		}
		
		if($runStatus) {
			
			$options = OptimizeSpeed::getOption();
			
			if(isset($options['cdn_enable']) && ('on' === $options['cdn_enable'])) {
				if(
					isset($options['cdn_cloudflare_email']) && ($options['cdn_cloudflare_email'])
					&& isset($options['cdn_cloudflare_api_key']) && ($options['cdn_cloudflare_api_key'])
				) {
					
					$staticVarData['last_time_run_purge'] = PepVN_Data::$defaultParams['requestTime'];
					$this->_staticVarObject->save($staticVarData,'m');
					
					$response = '';
					
					/*
					$params = array('v' => 1);
					$response = $this->api_v1_request('fpurge_ts', $params);
					usleep(750 * 1000);	//miliseconds
					*/
					
					$get_zone_id = $this->get_zone_id();

					if($get_zone_id) {
						$args = array(
							'method' => 'DELETE',
							'body'   => json_encode(
								array(
									'purge_everything' => true,
								)
							),
						);
						
						$response = $this->api_v4_request( 'zones/' . $get_zone_id . '/purge_cache', $args );
						usleep(750 * 1000);	//miliseconds
					}
					
					return $response;
				}
				
			}
			
		}
		
	}
	
	/*
		https://www.cloudflare.com/docs/client-api.html
		This function will purge a single file from CloudFlare's cache.
		There is a rate limit for file purges of 100 per minute. Exceeding this limit will return an error in the JSON response.
	*/
	
	public function purge_url($url) 
    {
		
		$options = OptimizeSpeed::getOption();
		
		if(isset($options['cdn_enable']) && ('on' === $options['cdn_enable'])) {
			if(
				isset($options['cdn_cloudflare_email']) && ($options['cdn_cloudflare_email'])
				&& isset($options['cdn_cloudflare_api_key']) && ($options['cdn_cloudflare_api_key'])
			) {
				$this->_configs['urls_need_purge'][] = $url;
				/*
				$params = array('url' => $url);
				$response = $this->api_v1_request('zone_file_purge', $params);
				usleep(750 * 1000);	//miliseconds
				*/
			}
			
		}
	}
	
	public function wp_action_shutdown() 
    {
		if(!empty($this->_configs['urls_need_purge'])) {
			$urls_need_purge = array_unique($this->_configs['urls_need_purge']);
			$this->_configs['urls_need_purge'] = array();
			
			$get_zone_id = $this->get_zone_id();
			
			if($get_zone_id) {
				
				$urls_need_purge = array_chunk($urls_need_purge, 25);
				
				foreach($urls_need_purge as $key1 => $value1) {
					unset($urls_need_purge[$key1]);
					
					$args = array(
						'method' => 'DELETE',
						'body'   => json_encode(
							array(
								'files' => $value1,
							)
						),
					);
					
					$response = $this->api_v4_request( 'zones/' . $get_zone_id . '/purge_cache', $args );
					
					unset($response,$args);
					
					usleep(750 * 1000);	//miliseconds
				}
			}
			
		}
	}
	
	
}