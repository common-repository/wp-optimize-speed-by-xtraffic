<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Module\Backend\Controller;

use WPOptimizeSpeedByxTraffic\Application\Service\ControllerBase as ServiceControllerBase
	, WpPepVN\DependencyInjection
;

class ControllerBase extends ServiceControllerBase
{
	public function __construct() 
    {
		parent::__construct();
	}
	
	
	public function init(DependencyInjection $di)
	{
		parent::init($di);
		
		$this->view->adminNotice = $this->di->getShared('adminNotice');
	}
	
}