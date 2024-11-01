<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Module\Backend\Service;

use WpPepVN\DependencyInjection
;

class AdminMenu
{
	public $admin_menu_page = false;
	
	public $di = false;
	
	public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
	}
	
	public function init_admin_menu() 
    {
		global $wpOptimizeByxTraffic;
		
		$url = $wpOptimizeByxTraffic->di->getShared('url');
		
		$adminPage = $wpOptimizeByxTraffic->di->getShared('adminPage');
		
		$dashboard_menu_slug = WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_NS_SHORT.'_dashboard';
		
		// Sub menu pages
		$submenu_pages = array(
			
			array( 
				
				$dashboard_menu_slug //parent_slug
				, 'Optimize Speed'	//page_title
				, 'Optimize Speed'	//menu_title
				, 'manage_options'	//capability
				, WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_NS_SHORT.'_optimize_speed'	//menu_slug
				, array( &$adminPage, 'handle' )	//function
				
			)
			
		);
		
		if ( !empty( $submenu_pages ) ) {
			foreach ( $submenu_pages as $submenu_page ) {
				// Add submenu page
				$admin_page = add_submenu_page( $submenu_page[0], $submenu_page[1], $submenu_page[2], $submenu_page[3], $submenu_page[4], $submenu_page[5] );
			}
		}
		
		return true;
	}
	
}