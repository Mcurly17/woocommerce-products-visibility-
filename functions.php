<?php

function visibility_products() {
    
    $current_time = time();
    
	// Each post with availabilities dates
    $posts_avaibility = array(
        array(
            'id'                 => '1', 		// Your product id
            'start_availability' => date('01-10-Y'), 	// Your product availability start
            'end_availability'   => date('30-04-Y'), 	// Your product availability end
        ),
        array(
            'id'                 => '2', 		// Your product id
            'start_availability' => date('01-10-Y'),  	// Your product availability start
            'end_availability'   => date('30-04-Y'),  	// Your product availability end
        ),
        array(
            'id'                 => '3',  		// Your product id
            'start_availability' => date('15-07-Y'),  	// Your product availability start
            'end_availability'   => date('01-11-Y'),  	// Your product availability end
        ),
        array(
            'id'                 => '4', 		// Your product id
            'start_availability' => date('01-05-Y'),  	// Your product availability start
            'end_availability'   => date('31-08-Y'),  	// Your product availability end
        )
    );
    
    
    foreach( $posts_avaibility as $post_avaibility ) {
        $start_availability = strtotime( $post_avaibility['start_availability'] );
        $end_availability   = strtotime( $post_avaibility['end_availability']  );
        
        
        // Not available
        if( 
            ( ( $current_time < $start_availability ) && ( $current_time > $end_availability ) ) ||
            ( ( $current_time > $start_availability ) && ( $current_time > $end_availability ) && ( $start_availability < $end_availability ) ) ||
            ( ( $current_time < $start_availability ) && ( $current_time < $end_availability ) && ( $start_availability < $end_availability ) ) 
        ) {
            $terms = array( 'exclude-from-search', 'exclude-from-catalog' ); 
            $stock = 'outofstock';
        } 
        // Available
        else {
            $terms = array(); 
            $stock = 'yes';
        }
        
        
        // Set product visibility & stock
        wp_set_post_terms( $post_avaibility['id'], $terms, 'product_visibility', false ); 
        update_post_meta(  $post_avaibility['id'], '_stock_status', $stock );
    
        // Set stock for each variation
        $post_product            = new WC_Product_Variable( $post_avaibility['id'] );
        $post_product_variations = $post_product->get_available_variations();

        foreach( $post_product_variations as $post_product_variation ) {
            update_post_meta( $post_product_variation["variation_id"], '_stock_status', $stock );
        }
        
        // Clear cache
        wc_delete_product_transients( $post_avaibility['id'] );
    }
}

// Action for the cron
add_action( 'change_product_visibility', 'visibility_products' );

// If you want to init on each page load
//add_action( 'init', 'visibility_products' ); 


// Add cron for visibility products : Check each day
register_activation_hook( __FILE__, 'cron_product_visibility' );
 
function cron_product_visibility() {
    if (! wp_next_scheduled ( 'change_product_visibility' ) ) {
        wp_schedule_event( time(), 'daily', 'change_product_visibility' );
    }
}


?>
