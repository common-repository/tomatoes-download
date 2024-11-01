<?php
/*
    Plugin Option Page
	
	Copyright 2013  zourbuth.com  (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! class_exists( 'Total_Options' ) )
    require_once( TOMATOES_DIR . 'lib/options.php' );
	
class Tomatoes_Options extends Total_Options {
		
	/**
	 * Construct	 
	 * @since 1.0
	 */
	function __construct() {
		
		add_action( 'admin_menu', array( &$this, 'menu_page' ) );
		add_action( 'admin_head', array( &$this, 'menu_icon' ) );
        parent::__construct( array(
			'sections'	=> array (
							'general'	=> __( 'General', $this->textdomain ),
							'paypal'	=> __( 'Paypal', $this->textdomain ),							
							'advanced'	=> __( 'Advanced', $this->textdomain )
						),
			'lang'		=> TOMATOES_LANG,
			'title'		=> TOMATOES_NAME,
			'slug'		=> TOMATOES_SLUG
		));
	}
	
	
	/**
	 * Before section info
	 * add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position )
	 * add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function )
	 * @since 1.0
	 */
	function menu_page() {
		add_menu_page( TOMATOES_NAME, TOMATOES_NAME, 'manage_options', $this->slug, array( &$this, 'menu_page_content' ) );
		$submenu = add_submenu_page( $this->slug, 'All Downloads', 'Downloads', 'manage_options', $this->slug );
		add_action( "admin_print_styles-$submenu", array( &$this, 'styles' ) );
		add_action( "admin_print_scripts-$submenu", array( &$this, 'scripts' ) );		
	}
	
	
	/**
	 * Before section info
	 * add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position )
	 * add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function )
	 * @since 1.0
	 */
	function menu_icon() {	
		$icon_url = TOMATOES_URL . 'img/menu.png';
		echo'
		<style type="text/css" media="screen">
			body #adminmenu #toplevel_page_'.$this->slug.' div.wp-menu-image { background: url( "'. $icon_url .'" ) no-repeat -4px -35px transparent; }
			body #adminmenu #toplevel_page_'.$this->slug.':hover div.wp-menu-image  { background-position: -4px -3px; }
		</style>';
	}
	
	
	/**
	 * Before section info
	 * @since 1.0
	 */
	function menu_page_content() {
		echo '<div class="wrap"><div class="icon32 icon-media"></div><h2>'. __('All Downloads', $this->textdomain ) . '</h2>';
			require_once( TOMATOES_DIR . 'downloads-list.php' );
			$download_list = new Tomatoes_List();
			$download_list->prepare_items();
			$download_list->display();
		echo '</div>'; 	
	}
	
	
	/**
	 * Before section info
	 * @since 1.0
	 */
	function before_section() {		
		echo '<div id="totalFooter">
				<p class="totalInfo">
					<a target="_blank" href="#">' . $this->title . ' ' . TOMATOES_VERSION . '</a> | 					
					<a target="_blank" href="https://twitter.com/zourbuth">@zourbuth</a> | 
					<a target="_blank" href="#">' . __('Licenses', $this->textdomain) . '</a>
				</p>
			  </div>';
	}
	
	
	/**
	 * Create options and defaults
	 * @since 1.0
	 */
	function create_options() {
		/* General Sections
		===========================================*/
		$this->options['post_types'] = array(
			'section' 	=> 'general',
			'title'   	=> __( 'Metabox', $this->textdomain ),
			'desc'    	=> __( 'Select the post type(s) to enable the download feature.', $this->textdomain ),
			'type'    	=> 'checkbox',
			'opts'		=> array( 'post', 'page' ) + get_post_types( array( '_builtin' => false, 'public' => true ), 'names' ),
			'std'		=> array( 'post' )
		);
		$this->options['button_text'] = array(
			'section' 	=> 'general',
			'title'   	=> __( 'Button Text', $this->textdomain ),
			'desc'    	=> __( 'The button text for purchasing item.', $this->textdomain ),
			'type'    	=> 'text',
			'std'		=> __( 'Purchase Now', $this->textdomain ),
		);
		$this->options['free_for_registered_only'] = array(
			'section' => 'general',
			'title'   => __( 'Free Download User', $this->textdomain ),
			'desc'    => __( 'User need to be registered and logged in to download the free item.', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => false
		);
		
		/* PayPal Sections
		===========================================*/		
		$this->options['live_username'] = array(
			'section' => 'paypal',
			'title'   => __( 'Live API Username', $this->textdomain ),
			'desc'    => __( 'Your live PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['live_password'] = array(
			'section' => 'paypal',
			'title'   => __( 'Live API Password', $this->textdomain ),
			'desc'    => __( 'Your live PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['live_signature'] = array(
			'section' => 'paypal',
			'title'   => __( 'Live API Signature', $this->textdomain ),
			'desc'    => __( 'Your live PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['sandbox_username'] = array(
			'section' => 'paypal',
			'title'   => __( 'Sandbox API Username', $this->textdomain ),
			'desc'    => __( 'Your test sandbox PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['sandbox_password'] = array(
			'section' => 'paypal',
			'title'   => __( 'Sandbox API Password', $this->textdomain ),
			'desc'    => __( 'Your test sandbox PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['sandbox_signature'] = array(
			'section' => 'paypal',
			'title'   => __( 'Sandbox API Signature', $this->textdomain ),
			'desc'    => __( 'Your test sandbox PayPal API username.', $this->textdomain ),
			'type'    => 'text'
		);
		
		$this->options['currency'] = array(
			'section' => 'paypal',
			'title'   => __( 'Currency', $this->textdomain ),
			'desc'    => __( 'Currencies supported by Express Checkout and Direct Payment.', $this->textdomain ),
			'type'    => 'select',
			'opts'    => array(
				'AUD'	=> __( 'Australian Dollar', 'paypal-framework' ),
				'CAD'	=> __( 'Canadian Dollar', 'paypal-framework' ),
				'CZK'	=> __( 'Czech Koruna', 'paypal-framework' ),
				'DKK'	=> __( 'Danish Krone', 'paypal-framework' ),
				'EUR'	=> __( 'Euro', 'paypal-framework' ),
				'HKD'	=> __( 'Hong Kong Dollar', 'paypal-framework' ),
				'HUF'	=> __( 'Hungarian Forint', 'paypal-framework' ),
				'ILS'	=> __( 'Israeli New Sheqel', 'paypal-framework' ),
				'JPY'	=> __( 'Japanese Yen', 'paypal-framework' ),
				'MXN'	=> __( 'Mexican Peso', 'paypal-framework' ),
				'NOK'	=> __( 'Norwegian Krone', 'paypal-framework' ),
				'PLN'	=> __( 'Polish Zloty', 'paypal-framework' ),
				'GBP'	=> __( 'Pound Sterling', 'paypal-framework' ),
				'SGD'	=> __( 'Singapore Dollar', 'paypal-framework' ),
				'SEK'	=> __( 'Swedish Krona', 'paypal-framework' ),
				'CHF'	=> __( 'Swiss Franc', 'paypal-framework' ),
				'USD'	=> __( 'U.S. Dollar', 'paypal-framework' )
			),
			'std'     => 'USD'
		);
		
		$this->options['environment'] = array(
			'section' => 'paypal',
			'title'   => __( 'Environment', $this->textdomain ),
			'desc'    => __( 'Select the PayPal environment mode. Live is the real environment, if you want to test it, please use sandbox mode with your sandbox account details.', $this->textdomain ),
			'type'    => 'radio',
			'opts'    => array ( 'live' => 'Live', 'sandbox' => 'Sandbox' ),
			'std'     => 'sandbox'
		);
		$this->options['debug_mode'] = array(
			'section' => 'paypal',
			'title'   => __( 'Debug Mode', $this->textdomain ),
			'desc'    => __( 'If checked, debugging messages will be sent to the email address set below. ', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => false
		);
		$this->options['debug_emails'] = array(
			'section' => 'paypal',
			'title'   => __( 'Debugging Emails', $this->textdomain ),
			'desc'    => __( 'This is a comma separated list of email addresses that will receive the debug messages.', $this->textdomain ),
			'type'    => 'text'
		);		
		
		
		/* Advanced Sections
		===========================================*/
		$this->options['custom'] = array(
			'section' => 'advanced',
			'title'   => __( 'Custom Style & Script', $this->textdomain ),
			'desc'    => __( 'Use this option to add additional styles or script with the tag included.', $this->textdomain ),
			'type'    => 'textarea',
			'std'     => ''
		);
		$this->options['enable_custom'] = array(
			'section' => 'advanced',
			'title'   => '',
			'desc'    => 'Enable custom style & script above.',
			'type'    => 'checkbox',
			'std'     => false
		);		
	}
	
	
	/**
	 * Push the custom styles or scripts to the front end
	 * Check if the custom option is enable and not empty
	 * Use the wp_head action.
	 * @since 0.0.1
	 */	
	function print_custom() {
		$option = get_option( $this->slug );		
		if ( isset( $option['enable_custom'] ) && ! empty( $option['custom'] ) )
			echo $option['custom'];
	}		
} // end class.
?>