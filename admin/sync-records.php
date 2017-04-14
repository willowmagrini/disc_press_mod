<?php

add_action( 'wp_ajax_discpressGetJson', 'discpressGetJson' );
function discpressGetJson() {

	$scope = 'https://api.discogs.com';
	$options = get_option('discpress');
	$username = $options['username'];

	if(get_option("discpress_access_token")) {

		$oauthObject = new DiscPressOAuthSimple();
		$consumerKey = $options['consumerKey'];
		$consumerSecret = $options['consumerSecret'];
	    $signatures = array('consumer_key' => $consumerKey, 'shared_secret' => $consumerSecret);
	    $oauth_props = array( 'oauth_token' => get_option("discpress_access_token" ), 'oauth_secret' => get_option("discpress_access_token_secret"));
	    $params['path'] = "$scope/users/".$username."/collection/folders/0/releases";
	    $params['signatures'] = array_merge($oauth_props, $signatures);
	    $page = isset($_POST['page']) ? $_POST['page'] : 1;
	    $params['parameters'] = 'per_page=100&page=' . $page;
	    $result = $oauthObject->sign($params);
	    $url = $result['signed_url'];

	}

	else {

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$url = "$scope/users/".$username."/collection/folders/0/releases?per_page=100&page=" . $page;

	}

	$r = wp_remote_get($url, array(
		'timeout' => 20,
		'user-agent' => 'DiscPress/1.0'
	));
	if(is_wp_error($r)) {
		echo json_encode(array('status' => 'error'));
	   	exit();
	}

    $output = $r["body"];

    echo $output;
    exit();

}

add_action( 'wp_ajax_discpressParseRecords', 'discpressParseRecords' );
function discpressParseRecords() {

    $json = json_decode(stripslashes($_POST['json']), true);
    $itemsPerPage = count($json["releases"]);
    $recordCount = array('added' => 0, 'updated' => 0);

	for ($i = 0; $i <= $itemsPerPage - 1; $i++) {
    	$release = array();

    	$release['rel_id'] = isset($json["releases"][$i]["instance_id"]) ? $json["releases"][$i]["instance_id"] : null;
    	$release['id'] = isset($json["releases"][$i]["id"]) ? $json["releases"][$i]["id"] : null;
    	$release['year'] = isset($json["releases"][$i]["basic_information"]["year"]) ? $json["releases"][$i]["basic_information"]["year"] : null;
		$release['title'] = isset($json["releases"][$i]["basic_information"]["title"]) ? $json["releases"][$i]["basic_information"]["title"] : null;
		$release['added'] = isset($json["releases"][$i]["date_added"]) ? $json["releases"][$i]["date_added"] : null;
		$release['label'] = isset($json["releases"][$i]["basic_information"]["labels"][0]["name"]) ? concatenateValues($json["releases"][$i]["basic_information"]["labels"], "name") : null;
		$release['label_cat'] = isset($json["releases"][$i]["basic_information"]["labels"][0]["catno"]) ? concatenateValues($json["releases"][$i]["basic_information"]["labels"], "catno") : null;
		$release['format_name'] = isset($json["releases"][$i]["basic_information"]["formats"][0]["name"]) ? $json["releases"][$i]["basic_information"]["formats"][0]["name"] : null;
		$release['format'] = isset($json["releases"][$i]["basic_information"]["formats"][0]["descriptions"][0]) ? concatenateValues($json["releases"][$i]["basic_information"]["formats"][0]["descriptions"], "") : null;
		$release['artist'] = isset($json["releases"][$i]["basic_information"]["artists"][0]["name"]) ? concatenateValues($json["releases"][$i]["basic_information"]["artists"], "name") : null;
		$release['release_endpoint'] = isset($json["releases"][$i]["basic_information"]["resource_url"]) ? $json["releases"][$i]["basic_information"]["resource_url"] : null;
		$url = autentica_url($release['release_endpoint']);
		$generos=pega_genero($getPostCustom['release_endpoint'][0]);
		$termos = array_para_term_genero($generos);
		$release['genero']=$termos;
	    $recordCount = discpressCreateRecord($release, $recordCount);
    }

	echo json_encode(array('status' => 'done', 'records' => $recordCount));
   	exit();

}

function concatenateValues($array, $wanted) {
	foreach($array as $value) {
		$return .= $wanted ? $value[$wanted] . ', ' : $value . ', ';
	}
	return rtrim($return, ', ');
}

function discpressCreateRecord($release, $recordCount) {

    // cerca se il record esiste già
	$records = new WP_Query(array('post_type' => 'record', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array(
		array(
			'key' => 'rel_id',
			'value' => $release['rel_id']
		)
	)));

	// se non esiste lo aggiungo
	if($records->post_count == 0) {

		$my_post = array(
			'post_title' => $release['artist'] . ' - ' . $release['title'],
			'post_status' => 'publish',
			'post_type' => 'record'
		);

		$the_post_id = wp_insert_post( $my_post );

		$recordCount['added']++;

	}

	// se esiste lo aggiorno e basta
	else {

		$the_post_id = $records->posts[0];

		$recordCount['updated']++;

	}

	foreach($release as $meta_key => $meta_value) {
		if ($meta_key!= 'genero') {
			update_post_meta( $the_post_id, $meta_key, $meta_value );
		}
		else{
			$termos=$meta_value;
			wp_set_post_terms( $the_post_id, $termos, 'genero', 'true' );
		}
	}
	return $recordCount;
}

add_action( 'wp_ajax_discpressRemoveOldRecords', 'discpressRemoveOldRecords' );
function discpressRemoveOldRecords() {

    $json = json_decode(stripslashes($_POST['json']), true);
    $rel_ids = array();
    $recordCount = array('removed' => 0);

	foreach($json["pages"] as $page) {
		foreach($page["releases"] as $record) {
			$rel_ids[] = $record["instance_id"];
		}
	}

	$records = new WP_Query(array('post_type' => 'record', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array(
		array(
			'key' => 'rel_id',
			'value' => $rel_ids,
			'compare' => 'NOT IN'
		)
	)));

	foreach($records->posts as $record) {
		wp_delete_post( $record, true );
		$recordCount['removed']++;
	}

	echo json_encode(array('status' => 'done', 'records' => $recordCount));
   	exit();

}
