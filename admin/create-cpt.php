<?php

function register_record_cpt() {
	if( !post_type_exists( 'record' ) ) {
		$labels = array(
			"name" => __( 'Records', 'discpress' ),
			"singular_name" => __( 'Record', 'discpress' ),
			);
		$args = array(
			"label" => __( 'Records', 'discpress' ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => false,
			"rest_base" => "",
			"has_archive" => true,
			"show_in_menu" => true,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => array( "slug" => "record", "with_front" => true ),
			"query_var" => true,
			"supports" => array( "title", "editor", "thumbnail", "custom-fields" ),
			"menu_icon" => "dashicons-format-audio"
		);
		register_post_type( "record", $args );
	}
}
add_action( 'init', 'register_record_cpt' );
