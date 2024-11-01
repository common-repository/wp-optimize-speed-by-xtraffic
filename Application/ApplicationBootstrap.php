<?php
namespace WPOptimizeSpeedByxTraffic\Application;

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php');

use WPOptimizeSpeedByxTraffic\Application\Service\PluginDependencyManager
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
;

class ApplicationBootstrap extends Bootstrap
{
    //@SLUG : Slug of this plugin
	const SLUG = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG;
    
    //@VERSION : Version of this plugin
    const VERSION = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_VERSION;
	
	public $pluginDependencyManager = false;
	
	private $_noticesStore = array();
	
	public $di = false;
	
	protected $_isPluginActivationStatus = false;
	
    public function __construct() 
    {
		parent::__construct();
	}
    
	public function init() 
    {
        
        $this->registerLoaderDirs(array(
            WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_LIBS_DIR
        ));
        
        $this->registerNamespaces(array(
            'WPOptimizeSpeedByxTraffic\\Application\\' => WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR
        ));
        
		parent::init();
        
        include_once(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR . 'configs' . DIRECTORY_SEPARATOR . 'constant.php');
        
		
		add_action('init', array($this,'wp_action_init'), 11);
		
	}
    
    /*
    * @wp_action_init_first : add_action@init : load order : 1 (first)
    Runs after WordPress has finished loading but before any headers are sent. Useful for intercepting $_GET or $_POST triggers.
    Typically used by plugins to initialize. The current user is already authenticated by this time.
    Fires after WordPress has finished loading but before any headers are sent.
    Most of WP is loaded at this stage, and the user is authenticated. WP continues to load on the init hook that follows (e.g. widgets), and many plugins instantiate themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
    init is useful for intercepting $_GET or $_POST triggers.
    load_plugin_textdomain calls should be made during init, otherwise users cannot hook into it.
    If you wish to plug an action once WP is loaded, use the wp_loaded hook.         
    */
    public function wp_action_init() 
    {
		/*
		* Check if core plugin "WP Optimize By xTraffic" is loaded. This core plugin is required to run.
		* Details here : https://wordpress.org/plugins/wp-optimize-by-xtraffic/
		*/
		$this->pluginDependencyManager = new PluginDependencyManager();
		
		$this->pluginDependencyManager->setCurrentPluginName(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAME);
		$this->pluginDependencyManager->setCurrentPluginSlug(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG);
		
		$rsOne = $this->pluginDependencyManager->checkActionManagePluginsForDependency();
		if(isset($rsOne['notice']['success'])) {
			foreach($rsOne['notice']['success'] as $valueOne) {
				$this->_noticesStore[] = array(
					'text' => $valueOne
					, 'type' => 'success'
				);
			}
		}
		unset($rsOne);
		
		$this->pluginDependencyManager->setRequiredPluginDependency(array(
			'plugins' => array(
				'wp-optimize-by-xtraffic' => array(
					'name' => 'WP Optimize By xTraffic'
					, 'slug' => 'wp-optimize-by-xtraffic'
					, 'wp_plugin_url' => 'https://wordpress.org/plugins/wp-optimize-by-xtraffic/'
					, 'version' => '>=5.1.6'
					, 'check' => array(
						'variable_name' => 'wpOptimizeByxTraffic'
						,'constant_version_name' => 'WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_VERSION'
						,'method_init_status' => 'initStatus'
					)
				)
			)
		));
		
		$continueStatus = true;
		
		$rsCheckRequiredPluginDependency = $this->pluginDependencyManager->checkRequiredPluginDependency();
		
		foreach($rsCheckRequiredPluginDependency as $keyOne => $valueOne) {
			if(isset($valueOne['notice']['error'])) {
				foreach($valueOne['notice']['error'] as $valueTwo) {
					$this->_noticesStore[] = array(
						'text' => $valueTwo
						, 'type' => 'error'
					);
				}
			}
			
			if($valueOne['status'] !== PluginDependencyManager::VALID_SUCCESS_STATUS) {
				$continueStatus = false;
			}
		}
		
		if($continueStatus) {
			
			global $wpOptimizeByxTraffic;
			
			$di = new \WpPepVN\DependencyInjection\FactoryDefault();
			
			include_once(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR . 'configs' . DIRECTORY_SEPARATOR . 'service.php');
			
			$this->di = $di;
			
			$wpExtend = $this->di->getShared('wpExtend');
			
			if($wpExtend->is_admin()) {
				if($wpExtend->isCurrentUserCanManagePlugin()) {
					$module = new \WPOptimizeSpeedByxTraffic\Application\Module\Backend\Module();
					$module->init($this->di);
				}
			} else {
				$module = new \WPOptimizeSpeedByxTraffic\Application\Module\Frontend\Module();
				$module->init($this->di);
			}
			
			add_action('wp_loaded', array($this,'wp_action_wp_loaded'), 11);
			
			add_action('wp', array($this,'wp_action_wp'), 11);
			
			add_action('shutdown', array($this,'wp_action_shutdown'), 11);
			
		}
		
		if(is_admin()) {
			add_action('admin_notices', array($this, 'wp_action_admin_notices') );
		}
		
    }
	
	
	/*
	*	@wp_action_wp_loaded
	*	This action hook is fired once WordPress, all plugins, and the theme are fully loaded and instantiated.
	*/
	public function wp_action_wp_loaded() 
    {
		
	}
	
	/*
		Executes after the query has been parsed and post(s) loaded, but before any template execution
			, inside the main WordPress function wp(). 
		Useful if you need to have access to post data but can't use templates for output. 
		Action function argument: WP object ($wp) by reference.
	*/
	public function wp_action_wp() 
    {
		
	}
	
	/*
	*	@wp_action_shutdown
	*	This action hook is fired once WordPress, all plugins, and the theme are fully loaded and instantiated.
	*/
	public function wp_action_shutdown() 
    {
		$wpExtend = $this->di->getShared('wpExtend');
		if($wpExtend->is_admin()) {
			if($wpExtend->isCurrentUserCanManagePlugin()) {
				$this->wp_plugin_activation_hook();
				$this->wp_plugin_deactivation_hook();
			}
		}
	}
	
	public function wp_action_admin_notices() 
    {
		foreach($this->_noticesStore as $keyOne => $valueOne) {
			
			unset($this->_noticesStore[$keyOne]);
			
			$class = '';
			
			if('success' === $valueOne['type']) {
				$class = 'updated';
			} else if('info' === $valueOne['type']) {
				$class = 'updated';
			} else if('warning' === $valueOne['type']) {
				$class = 'update-nag';
			} else if('error' === $valueOne['type']) {
				$class = 'error';
			}
			
			echo '<div class="'.$class.'" style="padding: 1%;">'.$valueOne['text'].'</div>';
		}
		
	}
	
	public function wp_plugin_activation_hook() 
    {
		$sessionKey = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG.'-plugin-activation-status';
		
		$session = $this->di->getShared('session');
		
		if($session->has($sessionKey)) {
			if('y' === $session->get($sessionKey)) {
				$session->set($sessionKey, 0);
				$session->remove($sessionKey);
				
				$optimizeSpeed = $this->di->getShared('optimizeSpeed');
				$optimizeSpeed->wp_plugin_activation();
				
			}
		}
		
	}
	
	public function wp_register_activation_hook() 
    {
		
		global $wpOptimizeByxTraffic;
		
		if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
			if(isset($wpOptimizeByxTraffic->di) && is_object($wpOptimizeByxTraffic->di)) {
				$session = $wpOptimizeByxTraffic->di->getShared('session');
				$session->start();
				
				$sessionKey = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG.'-plugin-activation-status';
				$session->set($sessionKey, 'y');
			}
		}
	}
	
	
	public function wp_plugin_deactivation_hook() 
    {
		$sessionKey = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG.'-plugin-deactivation-status';
		
		$session = $this->di->getShared('session');
		
		if($session->has($sessionKey)) {
			if('y' === $session->get($sessionKey)) {
				$session->set($sessionKey, 0);
				$session->remove($sessionKey);
				
				$optimizeSpeed = $this->di->getShared('optimizeSpeed');
				$optimizeSpeed->wp_plugin_deactivation();
				
			}
		}
		
	}
	
	public function wp_register_deactivation_hook() 
    {
		
		global $wpOptimizeByxTraffic;
		
		if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
			if(isset($wpOptimizeByxTraffic->di) && is_object($wpOptimizeByxTraffic->di)) {
				$session = $wpOptimizeByxTraffic->di->getShared('session');
				$session->start();
				
				$sessionKey = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG.'-plugin-deactivation-status';
				$session->set($sessionKey, 'y');
			}
		}
	}
}