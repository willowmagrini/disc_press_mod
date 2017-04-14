<?php

add_action( 'admin_action_discpressAuthenticate', 'discpressAuthenticate' );
function discpressAuthenticate() {

	$options = get_option('discpress');

	$consumerKey = $options['consumerKey'];
	$consumerSecret = $options['consumerSecret'];

	$oauthObject = new DiscPressOAuthSimple();
	$scope = 'https://api.discogs.com';

	$signatures = array('consumer_key' => $consumerKey, 'shared_secret' => $consumerSecret);

    $result = $oauthObject->sign(array(
        'path'      => 'https://api.discogs.com/oauth/request_token',
        'parameters'=> array(
            'scope'         => $scope,
            'oauth_callback'=> admin_url() . 'options-general.php?page=discpress'
        ),
        'signatures'=> $signatures
    ));
    
	$r = wp_remote_get($result['signed_url'], array(
		'timeout' => 20,
		'user-agent' => 'DiscPress/1.0'
	));
	if(is_wp_error($r)) {
		$output_message = "e";
	 	wp_redirect($_SERVER['HTTP_REFERER']."&res=" . $output_message);
    	exit();	
	}

    parse_str($r['body'], $returned_items);
    $request_token = $returned_items['oauth_token'];
    $request_token_secret = $returned_items['oauth_token_secret'];
  	
    if(!get_option('discpress_request_token')) {
		add_option('discpress_request_token', $request_token, '', 'yes');
    }
	else {
		update_option('discpress_request_token', $request_token);
	}
	if(!get_option('discpress_request_token_secret')) {
		add_option('discpress_request_token_secret', $request_token_secret, '', 'yes');
	}
	else {
		update_option('discpress_request_token_secret', $request_token_secret);
	}

    $result = $oauthObject->sign(array(
        'path'      => 'https://www.discogs.com/oauth/authorize',
        'parameters' 	=> array(
        	'scope'         => $scope,
            'oauth_token' => $request_token
        ),
    	'signatures' => $signatures)
    );

    wp_redirect($result['signed_url']);
	exit();

}
