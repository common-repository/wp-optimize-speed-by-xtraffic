<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service;

use WpPepVN\Mvc\Controller as MvcController
	, WpPepVN\DependencyInjection
;

class ControllerBase extends MvcController
{
	const PLUGIN_SLUG = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG;
	
	public function __construct() 
    {
		parent::__construct();
		
	}
	
	public function init(DependencyInjection $di)
	{
		parent::init($di);
		
	}
	
	protected function _addNoticeSavedSuccess() 
    {
		$this->view->adminNotice->add_notice($this->view->translate->_('Options were saved successfully.'), 'success');
	}
	
	protected function _doAfterUpdateOptions() 
    {
		wppepvn_register_clean_cache(',all,');
	}
}