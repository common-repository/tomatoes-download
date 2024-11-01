<?php
/*
	Digital Download Post Meta
	
	Copyright 2013 zourbuth.com (zourbuth@gmail.com)

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

if( ! class_exists( 'Total_Post_Meta' ) )
    require_once( TOMATOES_DIR . 'lib/post-meta.php' );
	
class Tomatoes_Post_Meta extends Total_Post_Meta {

	/**
	 * Class constructor
	 * @return void
	 * @since 1.0.0
	**/
	function __construct() {
		$option = get_option( TOMATOES_SLUG );
        parent::__construct( array(
			'post_types' => $option['post_types'],
			'textdomain' => TOMATOES_LANG,
			'title'		 => TOMATOES_NAME,
			'slug'		 => TOMATOES_SLUG
		));
	}

	
	/**
	 * Creating the metabox fields
	 * We don't find any match to use the fields as a global variable, manually but best at least for now
	 * Using the name field [] for array results
	 * @param string $post_id
	 * @since 1.0.0
	**/
	function metabox_options() {
		global $post, $post_id;

		echo "<div id='{$this->slug}' class='totalControls tabbable tabs-left'>";
			
			wp_nonce_field( $this->slug, "{$this->slug}_nonce" );
						
			echo '<ul class="tab-content">';

				echo '<li>';
					echo '<ul>';			

						echo '<li>';
							$dp_title = get_post_meta( $post_id, "{$this->slug}_title", true );
							$title = $dp_title ? $dp_title : '';
							echo '<label for="dp-title">' . __( 'Title', $this->textdomain ) . '</label>';						
							echo "<input id='dp-title' name='{$this->slug}[title]' type='text' value='$title' class='widefat' />";									
						echo '</li>';

						echo '<li>';
							$dp_description = get_post_meta( $post_id, "{$this->slug}_description", true );
							$description = $dp_description ? $dp_description : '';
							echo '<label for="dp-description">' . __( 'Description', $this->textdomain ). '</label>';			
							echo "<textarea class='widefat' id='dp-description' name='{$this->slug}[description]' rows='2'>$description</textarea>";				
							echo '<span class="controlDesc">' . __( 'The file download description, supports HTML tags.', $this->textdomain ) . '</span>';
						echo '</li>';
						
						echo '<li>';
							$dp_price = get_post_meta( $post_id, "{$this->slug}_price", true );
							$price = $dp_price ? $dp_price : 0;
							echo '<label for="dp-price">' . __( 'Price', $this->textdomain ) . '</label>';
							echo "<input id='dp-price' name='{$this->slug}[price]' type='text' value='$price' class='smallfat' />";
							echo '<span class="controlDesc">' . __( 'Set the price. Leave 0 for free download.', $this->textdomain ) . '</span>';
						echo '</li>';
						
						echo '<li>';							
							$dp_file = get_post_meta( $post_id, "{$this->slug}_file", true );
							$file = $dp_file ? esc_url( $dp_file ) : '';
							echo '<label>' . __( 'File Download', $this->textdomain ) . '</label>';
							echo '<a class="filelink" target="_blank" style="display:block;margin-bottom: 13px;" href="' . $file . '">' . basename( $file ) . '</a>';
							echo '<a href="#" class="addFile button">' . __( 'Add file', $this->textdomain ) . '</a>&nbsp;';
							$removeclass = $dp_file ? 'removeImage button' : 'removeImage button hidden';
							echo '<a href="#" class="'.$removeclass.'">' . __( 'Remove', $this->textdomain ) . '</a>';
							echo "<input type='hidden' id='dp-file' name='{$this->slug}[file]' value='$file' />";
						echo '</li>';
						
						echo '<li>';
							echo '<span class="controlDesc">' . __( 'Add [download] shortcode to your content. This meta data will be saved if file is provided.', $this->textdomain ) . '</span>';
						echo '</li>';				
						
					echo '</ul>';
				echo '</li>';
			echo '</ul>';
		echo '</div>';
	}
	

	/**
	 * Creating the metabox
	 * add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );	 
	 * @since 1.5
	**/
	function add_metabox() {
		if( ! current_user_can( 'edit_others_posts' ) )
			return;
		
		if ( is_array( $this->post_types ) )
			foreach( $this->post_types as $post_type )
				add_meta_box( TOMATOES_SLUG, TOMATOES_NAME, array( &$this, 'metabox_options' ), $post_type, 'side', 'default' );
	}
	
	
	/**
	 * Save the post meta data if file added only to reduce database usage
	 * Need to sanitize data
	 * @since 1.5
	**/
	function save_metabox( $post_id ) {
		$data = isset( $_POST[$this->slug] ) ? $_POST[$this->slug] : '';

		if ( isset( $data['file'] ) && ! empty( $data['file'] ) ) {
			foreach ( $data as $k => $v ) {
				update_post_meta( $post_id, "{$this->slug}_$k", $v );
			}
		}
	}
}
?>