<?php
/*
    Grouping Widget Settings
	
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

class Tomatoes_User {
		
	var $textdomain;
	
	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	function __construct() {
		$this->textdomain = TOMATOES_LANG;
		$this->title = TOMATOES_NAME;
		$this->slug = TOMATOES_SLUG;	
		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_head', array( &$this, 'menu_icon' ) );
	}
	
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	function display_page() {
		require_once( TOMATOES_DIR . 'downloads-list.php' );
		$purchase_list = new Tomatoes_List();
		echo '<div class="wrap"><div class="icon32 icon-media"></div><h2>'. __('My Purchase List', $this->textdomain ) . '</h2>';
		$purchase_list->prepare_items();
		$purchase_list->display();
		echo '</div>'; 
	}
	
	
	/**
	 * Add download page
	 * Using add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	 * @since 1.0
	 */
	function add_pages() {
		if( ! current_user_can( 'manage_options' ) )
			$admin_page = add_menu_page( __('My Purchase List', $this->textdomain ), __('Download', $this->textdomain ), 'read', $this->slug.'-purchase', array( &$this, 'display_page' ) );
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
			body #adminmenu #toplevel_page_digital_premium_download div.wp-menu-image { background: url( "' . $icon_url . '" ) no-repeat -4px -35px transparent; }
			body #adminmenu #toplevel_page_digital_premium_download:hover div.wp-menu-image  { background-position: -4px -3px; }
		</style>';
	}	

} // end class.
?>