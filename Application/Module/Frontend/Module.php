<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Module\Frontend;

use WPOptimizeSpeedByxTraffic\Application\Service\CacheRequestUri
	, WpPepVN\DependencyInjection
;

class Module extends \WpPepVN\Mvc\Module
{
    const MODULE_DIR = __DIR__;
    
    public function __construct() 
    {
        parent::__construct();
    }
    
    public function init(DependencyInjection $di) 
    {
		global $wpOptimizeByxTraffic;
		
        parent::init($di);
		
        $this->di = $di;
		
		add_action( 'wp', array($this, 'add_action_wp'), WP_PEPVN_PRIORITY_LAST );
		
	}
	
	
	public function add_action_wp()
	{
		
		$wpRegisterStyleScript = $this->di->getShared('wpRegisterStyleScript');
		$wpRegisterStyleScript->frontend_enqueue_scripts();
		
		$optimizeSpeed = $this->di->getShared('optimizeSpeed');
		$optimizeSpeed->initFrontend();
		
	}
	
	
	
}