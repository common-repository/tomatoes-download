<?php
/**
    Plugin Name: Tomatoes Download
    Plugin URI: http://zourbuth.com/?p=904
    Description: Give your biggest fans another way to download your digital files for premium or free. A powerfull plugin for selling digital goods with PayPal. Easy to use and no need for doing additional codes.
    Version: 0.0.2
    Author: zourbuth
    Author URI: http://zourbuth.com
    License: GPL2
	
	Copyright 2013 zourbuth.com (email : zourbuth@gmail.com)

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

 
/**
 * Exit if accessed directly
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Launch the plugin
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'tomatoes_plugin_loaded' );


/**
 * Initializes the plugin and it's features with the 'plugins_loaded' action
 * Creating custom constan variable and load necessary file for this plugin
 * Attach the widget on plugin load
 * @since 1.0.0
 */
function tomatoes_plugin_loaded() {

	// Set constant variable
	define( 'TOMATOES_VERSION', '0.0.2' );
	define( 'TOMATOES_DIR', plugin_dir_path( __FILE__ ) );
	define( 'TOMATOES_URL', plugin_dir_url( __FILE__ ) );
	define( 'TOMATOES_NAME', 'Tomatoes' );
	define( 'TOMATOES_SLUG', 'tomatoes' );
	define( 'TOMATOES_LANG', 'tomatoes-download' );
		
	// Define the location of the sdk_config.ini file
	// This is needed by the REST SDK 
	// define( 'PP_CONFIG_PATH', plugin_dir_path( __FILE__ ) );
	
	// Load files	
	// require_once( TOMATOES_DIR . 'vendor/autoload.php' );
	// require_once( TOMATOES_DIR . 'util.php' );
	// require_once( TOMATOES_DIR . 'paypal.php' );
	
	require_once( TOMATOES_DIR . 'options.php' );
	require_once( TOMATOES_DIR . 'class.php' );		
	require_once( TOMATOES_DIR . 'post-meta.php' );	
	require_once( TOMATOES_DIR . 'dashboard.php' );	
	
	new Tomatoes();
	new Tomatoes_Options();
	new Tomatoes_Post_Meta();
	new Tomatoes_User();
		
	// $downloader = get_post_meta( 1016, "_tomatoes_downloader", true );
	//$data = get_user_meta( 2, TOMATOES_SLUG, true );
	// print_r( $downloader );
	//$data['1018'] = '1HR86511FM009834T';	
	//$data['1016'] = '1FM0865109834T1HR';	
	//update_user_meta( 2, TOMATOES_SLUG, $data );	
	
	load_plugin_textdomain( 'tomatoes-download', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}