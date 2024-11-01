<?php
/*
	Post Meta Class
	@since 0.0.1
	
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

class Total_Post_Meta {
	
	var $post_types;
	var $textdomain;
	var $title;
	var $slug;
	
	/**
	 * Class constructor
	 * @return void
	 * @since 1.0.0
	**/
	function __construct( $args = array() ) {
		if( ! is_admin() )
			return;
			
		$args = wp_parse_args( $args, array(
			'post_types' => '',
			'textdomain' => '',
			'title' 	 => '',
			'slug' 		 => false,
		) );	
		
		$this->post_types = $args['post_types'];
		$this->textdomain = $args['textdomain'];
		$this->title = $args['title'];
		$this->slug = $args['slug'];
		$this->url = plugin_dir_url( __FILE__ );
		
		add_action( 'admin_head', array( &$this, 'admin_head' ), 2 );
		add_action( 'admin_init', array( &$this, 'add_metabox' ) );
		add_action( 'save_post', array( &$this, 'save_post' ) );
	}


	/**
	 * Creating the metabox
	 * Check if the current user can edit post or other post type
	 * Add the meta box if current custom post type is selected
	 * add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
	 * $id (string) (required) HTML 'id' attribute of the edit screen section. Default: None 
	 * $title (string) (required) Title of the edit screen section, visible to user. Default: None 
	 * $callback (callback) (required) Function that prints out the HTML for the edit screen section. 
	 *		The function name as a string, or, within a class, an array to call one of the class's methods. 
	 *		The callback can accept up to two arguments, see Callback args. See the second example under Example below.
	 *		Default: None 
	 * $post_type (string) (required) The type of Write screen on which to show the edit screen section ('post', 'page', 
	 *		'link', 'attachment' or 'custom_post_type' where custom_post_type is the custom post type slug)
	 *		Default: None 
	 * $context (string) (optional) The part of the page where the edit screen section should be shown ('normal', 'advanced', 
	 *		or 'side'). (Note that 'side' doesn't exist before 2.7)
     *    	Default: 'advanced' 

	 * $priority (string) (optional) The priority within the context where the boxes should show ('high', 'core', 'default' or 'low'). Default: 'default' 
	 * $callback_args (array) (optional) Arguments to pass into your callback function. 
	 *		The callback will receive the $post object and whatever parameters are passed through this variable.
	 *		Default: null 
	 * @since 1.5
	**/
	function add_metabox() {
		die( 'function Total_Post_Meta::add_metabox() must be over-ridden in a sub-class.' );
	}
	

	/**
	 * Creating the metabox fields
	 * We don't find any match to use the fields as a global variable, manually but best at least for now
	 * Using the name field [] for array results
	 * @param string $post_id
	 * @since 1.5
	**/
	function metabox_options() {
		die( 'function Total_Options::metabox_options() must be over-ridden in a sub-class.' );
	}
	
	
	/**
	 * Creating the metabox fields
	 * We don't find any match to use the fields as a global variable, manually but best at least for now
	 * Using the name field [] for array results
	 * @param string $post_id
	 * @since 1.5
	**/
	function save_metabox( $post_id ) {
		die( 'function Total_Options::save_metabox() must be over-ridden in a sub-class.' );
	}


	/**
	 * Saving metabox data on save action
	 * Checking the nonce, make sure the current post type have sidebar option enable
	 * Save the post metadata with update_post_meta for the current $post_id in array
	 * @param string $post_id
	 * @since 1.5
	**/
	function save_post( $post_id ) {

		// Check permissions if this post type is use the sidebar meta option
		// Example array value [cpt] => Array ( 0 => post 1 => page )
		// First we need to check if the current user is authorised to do this action. 
		if ( 'page' == $_REQUEST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
		
		// Verify this came from the our screen with proper authorization,
		// because save_post can be triggered at other times
		if ( ! isset( $_POST[$this->slug . '_nonce']) || ! wp_verify_nonce( $_POST[$this->slug . '_nonce'], $this->slug ) )
			return;
		
		// Verify if this is an auto save routine. If our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;

		// Alright folks, we're authenticated, let process
		if ( $parent_id = wp_is_post_revision($post_id) )
			$post_id = $parent_id;

		// Process child class data
		$this->save_metabox( $post_id );
	}


	/**
	 * Load custom style or script to the current page admin
	 * Enqueue the jQuery library including UI, colorpicker, 
	 * the popup window and some custom styles/scripts
	 * @param string $hook.
	 * @since 1.5
	**/
	function admin_head() {
		global $post_type;
		
		if ( in_array( $post_type, $this->post_types ) ) {
			wp_enqueue_style( "{$this->slug}-post-meta", "{$this->url}options.css", array( 'farbtastic', 'thickbox' ) );
			wp_enqueue_script( "{$this->slug}-post-meta", "{$this->url}jquery.options.js", array( 'jquery', 'farbtastic', 'media-upload', 'admin-widgets', 'thickbox') );
		}			
	}
}
?>