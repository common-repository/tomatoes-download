<?php
/*    
    Options Class
	@since 0.0.1
	
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

class Total_Options {
	
	private $sections;
	private $checkboxes;
	private $sidebar;
	
	var $options;	
	var $textdomain;
	var $title;
	var $slug;
	
	
	/**
	 * Construct
	 * @since 0.0.1
	 */
	function __construct( $args = array() ) {
	
		$args = wp_parse_args( $args, array(
			'lang' => '',
			'title' => '',
			'slug' => false,
		) );	
	
		$this->textdomain = $args['lang'];
		$this->title = $args['title'];
		$this->slug = $args['slug'];
		$this->url = plugin_dir_url( __FILE__ );
		
		// This will keep track of the checkbox options for the validate_settings function.
		$this->checkboxes = array();
		$this->sidebar = array();
		$this->options = array();
		$this->sections = array();
		$this->create_options();
		$this->sections = $args['sections'];

		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'add_settings_section' ) );		
		add_action( 'wp_head', array( &$this, 'print_custom') );
		
		if ( ! get_option( $this->slug ) )
			$this->initialize_options();
	}
	
	
	/**
	 * Create settings field
	 * @since 0.0.1
	 */
	function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      	=> 'default_field',
			'title'   	=> __( 'Default Field', $this->textdomain ),
			'desc'    	=> __( 'This is a default description.', $this->textdomain ),
			'std'     	=> '',
			'type'    	=> 'text',
			'section' 	=> 'general',
			'opts' 		=> array(),
			'slide'		=> array(),
			'class'   	=> ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'section' 	=> $section,
			'desc'      => $desc,
			'std'       => $std,
			'opts'   	=> $opts,
			'slide'   	=> $slide,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), $this->slug, $section, $field_args );
	}
	
	
	/**
	 * Print custom HTML before all section
	 * @since 0.0.1
	 */
	function before_section() {}
	function after_section() {}
	
	
	/**
	 * Display options page
	 * @since 0.0.1
	 */
	function display_page() {

		$options = get_option( $this->slug ); //print_r( $options );	
		
		echo '<div class="wrap"><div class="icon32 icon-settings"></div><h2>' . $this->title . '</h2>';		
			
			echo '<div id="totalForm" class="totalControls tabbable tabs-left">';

				$this->before_section();
				  
				echo '<ul class="nav nav-tabs">';					
					$i = 0;
					foreach ( $this->sections as $slug => $section ) {
					
						if ( ! isset( $options['tab'] ) )
							$class = $i == 0 ? 'active' : '';
						else
							$class = $slug == $options['tab'] ? 'active' : '';
						
						$val = isset($options['tab'][$i]) ? $options['tab'][$i] : '';
						
						echo '<li class="' . $class . '">';
							echo $section . "<input type='hidden' name='{$this->slug}[tab][]' value='$val' />";
						echo '</li>';

						$i++;
					}
				echo '</ul>';
					
				echo '<div class="tab-content">';
					foreach ( $this->sections as $slug => $section ) {
						echo '<div id="' . $slug . '-section" class="tab-pane">';
						
						if( "shortcodes" != $slug ) 
							echo '<form action="options.php" method="post">';													
						
						settings_fields( $this->slug );
						echo "<input type='hidden' name='{$this->slug}[tab]' value='$slug' />";
						
						echo '<table class="form-table">';
						do_settings_fields( $this->slug, $slug );
						
						if( "shortcodes" != $slug ) {
							echo '<tr valign="top">
									<th scope="row">&nbsp;</th>
									<td>
										<input id="submit" class="button-primary" type="submit" value="' . __( 'Save Changes', $this->textdomain ) . '" name="submit">
									</td>
								  </tr>';
							echo '</form>';
						}
						
						echo '</table>';
						echo '</div>';
					}
				echo '</div>';
				
				$this->after_section();

			echo '</div>';		
			
		echo '</div>';
		echo '<script type="text/javascript">
			jQuery(document).ready(function($) {
				var sections = [];';
				$i = 0;
				foreach ( $this->sections as $slug => $value ) {
					echo "sections['$i'] = '$slug';";
					$i++;
				}

				$options = get_option( $this->slug );
				$tab = isset( $options['tab'] ) ? $options['tab'] : 'general';
				
				echo '
				$(".tab-pane").each(function(index) {
					$(this).attr("id", sections[index]+\'-section\');
					if (sections[index] == "' . $tab . '")
						$(this).addClass("active");

				});
				
				$("ul.nav-tabs li").each(function(i) { // Tabs function
					$(this).bind("click", function(){
						var liIndex = $(this).index();
						var content = $(this).parent("ul").next().children(".tab-pane").eq(liIndex);
						$(this).addClass("active").siblings("li").removeClass("active");
						$(content).show().addClass("active").siblings().hide().removeClass("active");
	
						$(this).parent("ul").find("input").val(0);
						$("input", this).val(1);
						return false;
					});
				});
			});
		</script>';
	}
	
	
	/**
	 * Description for section
	 * @since 0.0.1
	 */
	function display_section() {}
	
	
	/**
	 * HTML output for text field
	 * @since 0.0.1
	 */
	function display_setting( $args = array() ) {
		extract( $args );

		$options = get_option( $this->slug );

		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;

		$field_class = $class ? $class : 'regular-text';
		
		switch ( $type ) {
			
			case 'checkbox':
				if( $opts ) {
					foreach( $opts as $val ) {
						echo "<label style='font-weight: normal'><input class='checkbox' type='checkbox' name='{$this->slug}[$id][]' value='$val'" . checked( in_array($val, $options[$id]), true, false ) . " />$val</label>";
					}
					if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				} else {
					echo "<label style='font-weight: normal'><input class='checkbox' id='$id' type='checkbox' name='{$this->slug}[$id]' value='1'" . checked( $options[$id], 1, false ) . " />$desc</label>";
				}
				break;
			
			case 'select':
				echo "<select class='select{$field_class}' name='{$this->slug}[$id]'>";
				foreach ( $opts as $key => $value ) {
					$key = esc_attr( $key );
					echo "<option value='$key'" . selected( $options[$id], $key, false ) . ">$value</option>";
				}
				echo "</select>";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $opts as $key => $value ) {
					echo "<label for='$id$i'><input class='radio' type='radio' name='{$this->slug}[$id]' id='$id$i' value='$key'" . checked( $options[$id], $key, false ) . ">$value</label>";
					$i++;
				}
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";	
				break;
			
			case 'textarea':
				echo "<textarea class='widefat' id='$id' name='{$this->slug}[$id]'>{$options[$id]}</textarea>";				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'image':
				$img = $options[$section][$id];
				if ( empty($img) ) $class = 'hideRemove'; else $class= 'showRemove';

				echo "<img alt='' class='optionImage' src='$img'>";
				echo "<a href='#' class='addImage button'>" . __( 'Add Image', $this->textdomain ) . "</a>";
				echo "<a class='$class removeImage button' href='#'>" . __( 'Remove', $this->textdomain ) . "</a>";
				echo "<input type='hidden' id='$id' name='{$this->slug}[$id]' value='$img' />";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
				
			case 'farbtastic':
				echo '<input type="text" class="color-input" id="' . $id . '" name="navmenu[' . $id . ']" style="background: #' . esc_attr( $options[$section][$id] ) . '; color: #';
					$colortype = esc_attr( $options[$section][$id] ); 
					$colortype = $colortype[0]; 
					if( is_numeric($colortype) ) echo 'fff'; else echo '000';
					echo '" value="' . $options[$section][$id] . '" />
				<a class="pickcolor" href="#" id="pickcolor' . $id . '">pickcolor</a>
				<div id="zcolorpicker' . $id . '" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none; margin-top: 10px;"></div>
			
				<script type="text/javascript">

				var farbtastic' . $id . ';
				function pickcolor' . $id . '(a){
					farbtastic' . $id . '.setColor(a);
					jQuery("#' . $id . '").val(a);
					jQuery("#' . $id . '").css("background-color",a)
				}

				jQuery("#pickcolor' . $id . '").click(function(){
					jQuery("#zcolorpicker' . $id . '").show();
					return false});
					jQuery("#' . $id . '").keyup(function(){var b=jQuery("#' . $id . '").val(),a=b;
					/* if(a.charAt(0)!="#"){a="#"+a}a=a.replace(/[^#a-fA-F0-9]+/,""); */ // uncomment this if you want the "#" still left at the textbox.
					if(a!=b){jQuery("#' . $id . '").val(a)}if(a.length==4||a.length==7){pickcolor' . $id . '(a)}});
					farbtastic' . $id . '=jQuery.farbtastic("#zcolorpicker' . $id . '",function(a){pickcolor' . $id . '(a)});pickcolor' . $id . '(jQuery("#' . $id . '").val());
					jQuery(document).mousedown(function(){
						jQuery("#zcolorpicker' . $id . '").each(function(){var a=jQuery(this).css("display");if(a=="block"){jQuery(this).fadeOut(2)}})
				})

				</script>';
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'text':
			default:
		 		echo "<input class='$field_class' type='text' id='$id' name='{$this->slug}[$id]' value='".esc_attr( $options[$id] )."' />";
		 		if ( $desc != '' ) echo "<span class='description'>$desc</span>";
		 		break;
		}		
	}
	

	/**
	 * Settings and defaults
	 * @since 0.0.1
	 */
	function create_options() {		
		die( 'function Total_Options::create_options() must be over-ridden in a sub-class.' );
	}

	
	/**
	 * Push the custom styles or scripts to the front end
	 * Check if the custom option is enable and not empty
	 * Use the wp_head action.
	 * @since 0.0.1
	 */	
	function print_custom() {}	

	
	/**
	 * Initialize settings to their default values
	 * @since 0.0.1
	 */
	function initialize_options() {
		$defaults = array();
		foreach ( $this->options as $id => $setting )
			$defaults[$id] = isset( $setting['std'] ) ? $setting['std'] : '';
		
		update_option( $this->slug, $defaults );
	}

	
	/**
	* Register settings
	* add_settings_section($id, $title, $callback, $page)
	* @since 0.0.1
	*/
	function add_settings_section() {
		
		register_setting( $this->slug, $this->slug, array ( &$this, 'validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
			add_settings_section( $this->slug . '-' . $slug, $title, array( &$this, 'display_section' ), $this->slug . '-' . $slug );
		}
		
		$this->create_options();
		
		foreach ( $this->options as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );			
		}
	}

	
	/**
	* Enqueue Script
	* @since 0.0.1
	*/
	function scripts() {
		$scripts = array( 
			'jquery', 			
			'jquery-ui-droppable', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-sortable',
			'farbtastic', 'media-upload',
			'admin-widgets', 
			'thickbox'
		);
		wp_enqueue_script( "{$this->slug}-options", "{$this->url}jquery.options.js", $scripts );
		add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ) );
	}
	
	
	/**
	* Create custom page settings body class
	* @since 0.0.1
	*/
	function admin_body_class() {
		return 'total-framework';
	}	

	
	/**
	* Styling for the theme options page
	* @since 0.0.1
	*/
	function styles() {
		$styles = array( 'farbtastic', 'thickbox' );			
		wp_enqueue_style( "{$this->slug}-options", "{$this->url}options.css", $styles );
	}
	
	
	/**
	 * Add settings page
	 * add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	 * @since 0.0.1
	 */
	function add_pages() {
		// $page = add_submenu_page( $this->title, $this->title, 'manage_options', 'download', array( &$this, 'display_page' ) );		
		$page = add_submenu_page( $this->slug, "{$this->title} Settings", "Settings", 'manage_options', "{$this->slug}-settings", array( &$this, 'display_page' ) );		
		add_action( "admin_print_styles-$page", array( &$this, 'styles' ) );
		add_action( "admin_print_scripts-$page", array( &$this, 'scripts' ) );
	}
	
	
	/**
	* Validate settings function
	* Returning input by excluding the selected input
	* @params $input
	* @since 0.0.1
	*/
	function validate_settings( $input ) {
		$options = get_option( $this->slug );
		
		foreach( $this->options as $key => $val ) {
			if( ! isset( $input[$key] ) && $val['section'] != $input['tab'] ) 
				$input[$key] = $options[$key];
		}
		return $input;
	}	
} // end class.
?>