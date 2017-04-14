<?php

function discpressVerify() {

	$options = get_option('discpress');

	$consumerKey = $options['consumerKey'];
	$consumerSecret = $options['consumerSecret'];

	$signatures = array('consumer_key' => $consumerKey, 'shared_secret' => $consumerSecret);

	$signatures['oauth_secret'] = get_option('discpress_request_token_secret');
	$signatures['oauth_token'] = $_GET['oauth_token'];

	$oauthObject = new DiscPressOAuthSimple();
	$scope = 'https://api.discogs.com';

	$result = $oauthObject->sign(array(
	    'path'      => 'https://api.discogs.com/oauth/access_token',
	    'parameters'=> array(
	    	'scope'         => $scope,
	        'oauth_verifier' => $_GET['oauth_verifier'],
	        'oauth_token'    => $_GET['oauth_token']
	    ),
	    'signatures'=> $signatures
	));

	$r = wp_remote_get($result['signed_url'], array(
		'timeout' => 20,
		'user-agent' => 'DiscPress/1.0'
	));
	if(is_wp_error($r)) {
		$output_message = "e";
	 	wp_redirect( $_SERVER['HTTP_REFERER']."&res=".$output_message);
		exit();	
	}

	parse_str($r["body"], $returned_items);
	$access_token = $returned_items['oauth_token'];
	$access_token_secret = $returned_items['oauth_token_secret'];

	if(!get_option('discpress_access_token')) {
		add_option('discpress_access_token', $access_token, '', 'yes');
	}
	else {
		update_option('discpress_access_token', $access_token);
	}
	if(!get_option('discpress_access_token_secret')) {
		add_option('discpress_access_token_secret', $access_token_secret, '', 'yes');
	}
	else {
		update_option('discpress_access_token_secret', $access_token_secret);
	}

	wp_redirect(admin_url() . 'options-general.php?page=discpress');
	exit();

}
