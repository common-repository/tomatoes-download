<?php
/**
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

class Tomatoes {

	/**
	 * Additional variables for class
	 * @since 1.0
	 */
	var $textdomain;
	var $slug;
	var $name;
	
	
	/**
	 * Class constructor
	 */	
	function __construct() {
		$this->textdomain = TOMATOES_LANG;
		$this->slug	= TOMATOES_SLUG;
		$this->name	= TOMATOES_NAME;
		
		add_shortcode( 'download',  array( &$this, 'shortcode' ), 999 );
		add_action( 'wp_ajax_nopriv_paypal_listener', array( $this, 'paypal_listener' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 100 );
		add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_scripts' ), 100 );
	}
	

	function query_vars( $vars ) {
		$vars[] = 'download';
		return $vars;
	}

		
	function template_redirect(){
		global $wp_query;
		
		if( isset( $wp_query->query['download'] ) ) {
			$post_id = $wp_query->query['download'];
			$option = get_option( TOMATOES_SLUG );
			$price = get_post_meta( $post_id, "{$this->slug}_price", true );
			
			if ( 0 == $price || empty( $price ) ) {
				if ( isset( $option['free_for_registered_only'] ) ) {
					if ( is_user_logged_in() )
						$this->get_file( $post_id );
					else
						die( 'Please login to download this file.' );
				} else {
					$this->get_file( $post_id );
				}
			} else {
				$user_id = get_current_user_id();
				if( ! $user_id )
					die('You don\'t have permission to download this file. Please login!');
					
				$purchase_data = get_user_meta( $user_id, $this->slug, true );
				if ( ( $purchase_data && array_key_exists( $wp_query->query['download'], $purchase_data ) ) or current_user_can( 'manage_options' ) ) {
					$this->get_file( $post_id );
				}
			}
						
			exit;
		}
	}
	
	
	/*
	 * Create the download file
	 * @since 0.0.1
	**/
	function get_file( $post_id ) {
		$file = get_post_meta( $post_id, "{$this->slug}_file", true );
		$upload_dir = wp_upload_dir();				
		$filepath = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file );
		$filename = explode( '.', basename( $file ) );
		$rename = str_replace( $filename[0], uniqid( '', false ), basename( $file ) );
		$filesize = filesize( $filepath );

		if ( file_exists( $filepath ) ) {
			$this->download_counter( $post_id );
			
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=$rename");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . $filesize);

			readfile( $filepath );	// The file source
			die();
		} else {	
			die( 'File not found' );
		}	
	}
	
	
	/*
	 * Update the count for the current item
	 * Check the current item if a free item
	 * Save IP address (::1 is localhost for IPv6) or user ID (if logged in) for free file for public download
	 * Save user ID for premium file
	 * Downloader format [user_id or ip adress] => total downloads
	 * @since 0.0.1
	**/
	function download_counter( $post_id ) {
		$option = get_option( TOMATOES_SLUG );
		$price = get_post_meta( $post_id, "{$this->slug}_price", true );
		$downloader = get_post_meta( $post_id, "_{$this->slug}_downloader", true );
		$downloads = get_post_meta( $post_id, "{$this->slug}_downloads", true );
		$user_id = get_current_user_id();
		
		// Check for free file or premium
		if ( 0 == $price || empty( $price ) ) {
			
			// Check if the user need to be registered and logged id
			if ( isset( $option['free_for_registered_only'] ) ) {
				if ( ! isset( $downloader[$user_id] ) )
					$downloader[$user_id] = 0;
				
				$downloader[$user_id]++;
				$downloader[$user_id] = $downloader[$user_id];
			} else {
				if ( ! isset( $downloader[$_SERVER['REMOTE_ADDR']] ) )
					$downloader[$_SERVER['REMOTE_ADDR']] = 0;
				
				$downloader[$_SERVER['REMOTE_ADDR']]++;
				$downloader[$_SERVER['REMOTE_ADDR']] = $downloader[$_SERVER['REMOTE_ADDR']];
			}
						
			update_post_meta( $post_id, "_{$this->slug}_downloader", $downloader );
			
			// Update the downloads count
			$downloads++;
			update_post_meta( $post_id, "{$this->slug}_downloads", (int) $downloads );	
			
		} else {
			// Don't update downloader if admin downloads the file
			if ( ! current_user_can( 'manage_options' ) ) {

				// Update the downloads count if purchaser has not downloaded the file
				if( ! isset( $downloader[$user_id] ) ) {
					$downloads++;
					update_post_meta( $post_id, "{$this->slug}_downloads", (int) $downloads );					
				}
				
				// Update the downloader data with total number of download from current user
				if ( ! isset( $downloader[$user_id] ) )
					$downloader[$user_id] = 0;
				
				$downloader[$user_id]++;
				$downloader[$user_id] = $downloader[$user_id];
				update_post_meta( $post_id, "_{$this->slug}_downloader", $downloader );				
			}
		}	
	}
	
	
	/*
	 * Main function to generate shortcode using total_users_pro() function
	 * See $defaults arguments for using total_users_pro() function
	 * Shortcode does not generate the custom style and script 
	 * @since 0.0.1
	**/	
	function shortcode( $atts, $content ) {
		global $post;
		
		if ( ! $post->ID )
			return __( 'There is no download file for this post.', $this->textdomain );
		
		$option = get_option( TOMATOES_SLUG );
		$title = get_post_meta( $post->ID, "{$this->slug}_title", true );
		$description = get_post_meta( $post->ID, "{$this->slug}_description", true );
		$price = get_post_meta( $post->ID, "{$this->slug}_price", true );
		$file = get_post_meta( $post->ID, "{$this->slug}_file", true );
		$file_url = add_query_arg( array( 'download' => $post->ID ), get_home_url() );
		$user_id = get_current_user_id();
		
		$data = get_user_meta( $user_id, $this->slug, true );
		
		// Check if current item is free or premium download
		if ( 0 == $price || empty( $price ) ) {
			
			if ( isset( $option['free_for_registered_only'] ) ) {
				if ( is_user_logged_in() ) {
					$html = "<a class='{$this->slug}-button' href='$file_url'><span>Download $title</span></a>";
				} else {					
					$html = "<a class='{$this->slug}-button' href='". wp_login_url( get_permalink() ) . "'><span>Download $title</span></a>
							<span class='{$this->slug}-description'>". __( '* Login or register to download this item.', $this->textdomain ) ."</span>";
				}
			} else {
				$html = "<a class='{$this->slug}-button' href='$file_url'><span>Download $title</span></a>";
			}

		} else {

			// Check if the user is logged in
			if ( is_user_logged_in() ) {
			
				// Check if the current user have purchased this item
				$purchase_data = get_user_meta( $user_id, $this->slug, true );
				
				if ( ( $purchase_data && array_key_exists( $post->ID, $purchase_data ) ) or current_user_can( 'manage_options' ) ) {					
					$html = "<a class='{$this->slug}-button' href='$file_url'><span>". sprintf( __( 'Download file %s', $this->textdomain ), $title ) . "</span></a>";
				} else {
					$environment = $this->paypal_environment();
					$return = get_permalink();
					$notify_url = add_query_arg( array( 'action' => 'paypal_listener' ), admin_url('admin-ajax.php') );
					$custom = implode(",", array( $user_id, $post->ID ) );

					// <input type='submit' name='submit' value=' {$option['currency']} $price'>
					$html = "<form class='{$this->slug}-form' action='{$environment['url']}' method='post'>
								<input type='hidden' name='cmd' value='_xclick'>
								<input type='hidden' name='business' value='{$environment['username']}'>
								<input type='hidden' name='currency_code' value='{$environment['currency']}'>
								<input type='hidden' name='item_name' value='$title'>
								<input type='hidden' name='amount' value='$price'>
								<input type='hidden' name='return' value='$return'>
								<input type='hidden' name='notify_url' value='$notify_url'>
								<input type='hidden' name='custom' value='$custom'>
								<button class='{$this->slug}-button' type='submit'><span class='purchase'>{$option['button_text']}</span><span class='price'><span>{$option['currency']}</span> $price</span></button>			
							</form>";
				}

			} else {
				// $html = '<a href="' . wp_login_url( get_permalink() ) . '">' . __( 'Login or register to download or purchase this item.', $this->textdomain ) . '</a>';
				$html = "<a class='{$this->slug}-button' href='". wp_login_url( get_permalink() ) . "'><span class='purchase'>{$option['button_text']}</span><span class='price'><span>{$option['currency']}</span> $price</span></a>";
			}
		}
		
		return $html;
	}
		
			
			
	/*
	 * Check if the post has a shortcode(s) used in the current post content with stripos PHP function
	 * Add !empty($cur_post->post_content) if the post has no content
	 * @return bool true, default false
	 * @since 0.0.1
	*/
	function has_shortcode() {
		global $post;
		$cur_post = get_post( $post->ID );  
		$shortcode = 'download';

		// Check the post content if has shortcode 
		if ( !empty($cur_post->post_content) && stripos($cur_post->post_content, '[' . $shortcode) !== false )
			return true;
		
		return false;
	}
		
		
	/*
	 * Enqueue additional script with 'wp_enqueue_scripts' if the current post has tcp shortcode
	 * If it found, enqueue the scripts or styles using 'wp_enqueue_scripts' hook
	 * before the wp_head
	 * @since 0.0.1
	 */
	function enqueue_scripts() {
		if ( $this->has_shortcode() ) {		
			wp_enqueue_style( $this->slug, TOMATOES_URL . 'css/tomatoes.css' );
		}
	}
	
		
	/**
	 * Throw an action based off the transaction type of the message
	 */
	function paypal_listener() {
		if( $this->validate_message() ) {
			
			$this->debug_mail( 'PayPal Listener' );
			
			if( ! isset( $_POST['custom'] ) )
				return;
			
			// Extract the custom message with format 'user,post id'
			$custom = explode( ',', $_POST['custom'] );
			$user_id = $custom[0];
			$post_id = $custom[1];				
			$txn_id = $_POST['txn_id'];	// The transaction ID
			
			// get the user previous data if has purchased other item
			$data = get_user_meta( $user_id, $this->slug, true );
			$data[$post_id] = $txn_id;

			// Save data to users table			
			update_user_meta( $user_id, $this->slug, $data );
		}
		
		exit;
	}
	
	
	/**
	 * If the response was valid, check to see if the request was valid
	 * @since 0.0.1
	**/
	private function validate_message() {
		// Set the command that is used to validate the message
		$_POST['cmd'] = "_notify-validate";

		// We need to send the message back to PayPal just as we received it
		$params = array(
			'body' => $_POST,
			'sslverify' => apply_filters( "{$this->slug}_premium_sslverify", false ),
			'timeout' 	=> 30,
		);

		// Send the request for validation
		$environment = $this->paypal_environment();
		$resp = wp_remote_post( $environment['url'], $params );
		
		$this->debug_mail('PayPal Validation');
		
		if ( ! is_wp_error($resp) && $resp['response']['code'] >= 200 && $resp['response']['code'] < 300 && (strcmp( $resp['body'], "VERIFIED") == 0))
			return true;
		else
			return false;
	}
	
	
	/**
	 * Sending mail function for debuggin mode
	 * Explode by comma separator
	 * @since 0.0.1
	 */
	private function debug_mail( $title ) {
		$option = get_option( TOMATOES_SLUG );
		
		if ( $option['debug_mode'] && ! empty ( $option['debug_emails'] ) ) {
			$emails = explode( ',', $option['debug_emails'] );
			foreach( $emails as $key => $val ) {
				if ( is_email( trim( $val ) ) )
					$to[] = trim( $val );
			}
			
			wp_mail( $to, $title, "\r\n" . print_r($resp, true) );			
		}
	}
	

	/**
	 * Function to generate the PayPal credential for using in forms
	 * Using options from plugin settings page.
	 */	
	private function paypal_environment() {
		$option = get_option( TOMATOES_SLUG );
		$env = $option['environment'];
		$data = array();
		$data['username'] = $option[$env . '_username'];
		$data['password'] = $option[$env . '_password'];
		$data['signature'] = $option[$env . '_signature'];
		$data['currency'] = $option['currency'];
		
		if( 'sandbox' === $env || 'beta-sandbox' === $env ) {
			$data['url'] 	  = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			$data['endpoint'] = "https://api-3t.sandbox.paypal.com/nvp";
		} else {
			$data['url']	  = "https://www.paypal.com/cgi-bin/webscr";
			$data['endpoint'] = "https://api-3t.paypal.com/nvp";		
		}
		
		return $data;
	}
}
?>