<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service;

class WpRegisterStyleScript
{
	private $_tempData = array();
	
	public function __construct() 
    {
		
	}
    
	
    /*
        @wp_register_style
        Use the wp_enqueue_scripts action to call this function. Calling it outside of an action can lead to problems
        wp_enqueue_scripts, admin_enqueue_scripts, login_enqueue_scripts
        add_action( 'wp_enqueue_scripts', array(&$this,'wp_register_script') );
    */
    public function wp_register_style() 
    {
        if(!isset($this->_tempData['wp_register_style_status'])) {
            $this->_tempData['wp_register_style_status'] = true;
            
        }
    }
    
    /*
        @wp_register_script
        Use the wp_enqueue_scripts action to call this function. Calling it outside of an action can lead to problems
        wp_enqueue_scripts, admin_enqueue_scripts, login_enqueue_scripts
        add_action( 'wp_enqueue_scripts', array(&$this,'wp_register_script') );
		https://codex.wordpress.org/Function_Reference/wp_enqueue_script
    */
    public function wp_register_script() 
    {
        if(!isset($this->_tempData['wp_register_script'])) {
            $this->_tempData['wp_register_script'] = true;
            
            $slug = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG;
            $version = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_VERSION;
            
            $urlFileTemp = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_URI.'public/js/frontend.'.(WP_PEPVN_DEBUG ? '' : 'min.').'js';
            $handleRegister = $slug.'-frontend';
            wp_register_script($handleRegister , $urlFileTemp, array('jquery'), $version, true);
			
        }
    }
    
	public function admin_enqueue_scripts() 
    {
		
		if(!isset($this->_tempData['admin_enqueue_scripts'])) {
            $this->_tempData['admin_enqueue_scripts'] = true;
            
		}
		
	}
	
	public function frontend_enqueue_scripts() 
    {
		
		if(!isset($this->_tempData['frontend_enqueue_scripts'])) {
            $this->_tempData['frontend_enqueue_scripts'] = true;
            
            $slug = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG;
            $version = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_VERSION;
			
			$this->wp_register_style();
			$this->wp_register_script();
			
			$handleRegister = $slug.'-frontend';
			wp_enqueue_script( $handleRegister );
			
		}
		
	}
	
	
}