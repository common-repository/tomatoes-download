<?php
if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class Tomatoes_List extends WP_List_Table {

    var $listdata = array();

	/**
	 * Additional variables for class
	 * @since 1.0
	 */
	var $textdomain;
	var $slug;	
	
    function __construct(){
		$this->textdomain = TOMATOES_LANG;
		$this->slug	= TOMATOES_SLUG;
		
        parent::__construct( array(
			'singular'  => __( $this->slug, $this->textdomain ),	// singular name of the listed records
			'plural'    => __( $this->slug, $this->textdomain ),   	// plural name of the listed records
			'ajax'      => false        						// does this table support ajax?
		) );
		
		$this->print_script();		
		$this->parse_data();		
	}

	function print_script() {
		echo '<script type="text/javascript">
			jQuery(document).ready(function($){
				$(".edit-link").click(function() {
					if ( confirm( "Delete selected ?" ) ){
						console.log( "on progress" );
					}
				});
			});
		</script>';
	}

	function no_items() {
		if( current_user_can( 'manage_options' ) )
			_e( 'There is no download. Please create at the post edit section.', $this->textdomain );
		else
			_e( 'You have not purchased any items.', $this->textdomain );
	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'title'  => array('title',false),
			'transaction'   => array('transaction',false)		
		);
		return $sortable_columns;
	}

	function get_columns(){
		$columns = array(
			'title' 		=> __( 'Title', $this->textdomain ),
			'description'	=> __( 'Description', $this->textdomain ),
			'transaction'	=> __( 'Transaction No.', $this->textdomain ),
			'download'    	=> __( 'Download', $this->textdomain )
		);
		if( current_user_can( 'manage_options' ) ) {
			unset( $columns['transaction'] );
			$columns['price'] = __( 'Price', $this->textdomain );
			$columns['downloaded'] = __( 'Downloaded', $this->textdomain );
		}
		return $columns;
	}

	function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'title';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}


	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		usort( $this->listdata, array( &$this, 'usort_reorder' ) );

		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->listdata );

		// only ncessary because we have sample data
		$this->found_data = array_slice( $this->listdata,( ( $current_page-1 )* $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,	// We have to calculate the total number of items
			'per_page'    => $per_page		// We have to determine how many items to show on a page
		) );
		$this->items = $this->found_data;
	}
	
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}
	
	
	function single_row_columns( $item ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( &$this, 'column_' . $column_name ), $item );
				echo "</td>";
			}
			else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				if ( 'title' == $column_name && current_user_can( 'manage_options' ) ) {
					$actions = array();					
					$actions['edit'] = '<a href="' . get_edit_post_link( $item['ID'] ) . '#' . $this->slug . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
					// $actions['delete'] = '<a class="edit-link" href="#" title="' . esc_attr( __( 'Delete this item' ) ) . '">' . __( 'Delete' ) . '</a>';
					$actions['view'] = '<a href="' . get_permalink( $item['ID'] ) . '" title="' . esc_attr( __( 'View this item' ) ) . '">' . __( 'View' ) . '</a>';
					echo $this->row_actions( $actions );
				}
				echo "</td>";
			}
		}
	}
	
	
	/**
	 * Generate and parse data based on users
	 * Display user purchased list
	 * Display all download list for administrator
	 * @since 1.0.0
	 */	
	function parse_data() {
		if( current_user_can( 'manage_options' ) ) {
			$wp_query = new WP_Query( array( 'meta_key' => "{$this->slug}_file" ) );
			
			if ( $wp_query->have_posts() ) {
				while ( $wp_query->have_posts() ) :			
					$wp_query->the_post();	
					$k = get_the_ID();
					$title = get_post_meta( $k, "{$this->slug}_title", true );
					$description = get_post_meta( $k, "{$this->slug}_description", true );
					$price = get_post_meta( $k, "{$this->slug}_price", true );
					$file = get_post_meta( $k, "{$this->slug}_file", true );
					$downloads = get_post_meta( $k, "{$this->slug}_downloads", true );

					$this->listdata[$k]['ID'] = $k;
					$this->listdata[$k]['title'] = "<a href='" . get_permalink( $k ) . "' class='row-title'>$title</a>";
					$this->listdata[$k]['description'] = wp_trim_words( $description, 15, '&hellip;' );
					$this->listdata[$k]['download'] = "<a href='$file'>". __('download', TOMATOES_LANG) . "</a>";
					$this->listdata[$k]['price'] = $price ? $price : '-';
					$this->listdata[$k]['downloaded'] = $downloads;				
				endwhile;

				wp_reset_postdata(); // reset the post globals as this query will shakes the party
			
			} else {
				// if no data
			}

		} else {
			$user_id = get_current_user_id();
			$purchases = get_user_meta( $user_id, $this->slug, true );	// Array ( [1018] => 1HR86511FM009834T [1016] => 1FM0865109834T1HR ) 
			
			if( $purchases ) {
				foreach ( $purchases as $k => $v ) {
					$title = get_post_meta( $k, "{$this->slug}_title", true );
					$description = get_post_meta( $k, "{$this->slug}_description", true );
					$price = get_post_meta( $k, "{$this->slug}_price", true );
					$file = get_post_meta( $k, "{$this->slug}_file", true );
					$file_url = add_query_arg( array( 'download' => $k ), get_home_url() );

					$this->listdata[$k]['ID'] = $k;
					$this->listdata[$k]['title'] = "<a href='" . get_permalink( $k ) . "' class='row-title'>$title</a>";
					$this->listdata[$k]['description'] = wp_trim_words( $description, 15, '&hellip;' );
					$this->listdata[$k]['download'] = "<a href='$file_url'>". __('download', TOMATOES_LANG) . "</a>";
					$this->listdata[$k]['transaction'] = $v;
				}
			}
		}
	}

} // end class
?>