<?php

add_action('wp_ajax_discpressCheckImagesStatus', 'discpressCheckImagesStatus');
function discpressCheckImagesStatus() {

	// return all
	$records_to_sync = new WP_Query(array("post_type" => 'record', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array(
        array(
			'key' => '_thumbnail_id',
			'compare' => 'NOT EXISTS'
        )
	)));
	$total_records_to_sync = $records_to_sync->post_count;

	if($total_records_to_sync > 0) {
	    echo json_encode(array('status' => 'start', 'tot' => $total_records_to_sync));
	   	exit();
	} else {
		echo json_encode(array('status' => 'nothing'));
		exit();
	}

}

add_action('wp_ajax_discpressImportImages', 'discpressImportImages');
function discpressImportImages() {

	// return 1
	$record_to_sync = new WP_Query(array("post_type" => 'record', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array(
        array(
			'key' => '_thumbnail_id',
			'compare' => 'NOT EXISTS'
        )
	)));

	if($record_to_sync->post_count > 0) {

		foreach($record_to_sync->posts as $record) {

			$endpoint_url = get_post_meta($record, 'release_endpoint', true);

			$oauthObject = new DiscPressOAuthSimple();
			$scope = 'https://api.discogs.com';
			$options = get_option('discpress');
			$username = $options['username'];
			$consumerKey = $options['consumerKey'];
			$consumerSecret = $options['consumerSecret'];
		    
		    $signatures = array('consumer_key' => $consumerKey, 'shared_secret' => $consumerSecret);
		    
		    $oauth_props = array( 'oauth_token' => get_option("discpress_access_token" ), 'oauth_secret' => get_option("discpress_access_token_secret"));
		        
		    $params['path'] = $endpoint_url;
		    $params['signatures'] = array_merge($oauth_props, $signatures);
		    
		    $params['parameters'] = '';
		    
		    $result = $oauthObject->sign($params);

		    $url = $result['signed_url'];

			$r = wp_remote_get($url, array(
				'timeout' => 20,
				'user-agent' => 'DiscPress/1.0'
			));
			
			if(is_wp_error($r)) {
				echo json_encode(array('status' => 'error'));
			   	exit();
			}

		    $output = $r["body"];

		    $json = json_decode($output, true);
		    $image_url = isset($json["images"][0]["uri"]) ? $json["images"][0]["uri"] : null;
			if(!$image_url) {
		    	if(isset($json["formats"][0]["name"]) && $json["formats"][0]["name"] == 'CD') {
		    		$image_url = 'https://s.discogs.com/images/default-release-cd.png';
		    	}
		    	else if(isset($json["formats"][0]["name"]) && $json["formats"][0]["name"] == 'Cassette') {
		    		$image_url = 'https://s.discogs.com/images/default-release-cassette.png';
		    	}
		    	else {
		    		$image_url = 'https://s.discogs.com/images/default-release.png';
		    	}
			}

			$image_title = pathinfo($image_url, PATHINFO_FILENAME);
			$existing_image = get_page_by_title($image_title, OBJECT, 'attachment');
			if(!$existing_image) {
				add_action('add_attachment','discpress_new_attachment');
				media_sideload_image($image_url, $record);
				remove_action('add_attachment','discpress_new_attachment');
			}
			else {
				discpress_same_attachment($existing_image->ID, $record);
			}

		}

		$response = array('status' => 'syncing');
		echo json_encode($response);
	   	exit();

	} else {
		$response = array('status' => 'done');
		echo json_encode($response);
		exit();
	}

}

function discpress_new_attachment($att_id) {
    update_post_meta(wp_get_post_parent_id( $att_id ), '_thumbnail_id', $att_id);
}

function discpress_same_attachment($att_id, $record) {
    update_post_meta($record, '_thumbnail_id', $att_id);
}
