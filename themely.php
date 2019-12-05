<?php
/**
 * Themely WHMCS Hook
 *
 * Created by Ishmael 'Hans' Desjarlais - @ismaelyws
 *
 * Copyright 2019-2020 inVenture Group DBA Themely cPanel Plugin
 *
 * Version 1.0.0 
 *
 */
if (!defined("WHMCS"))
die("This file cannot be accessed directly");

function hook_themely_cp_session($vars) {

	// Variables
	$hostname = $vars['params']['serverhostname'];
    $username = $vars['params']['username'];
    $password = $vars['params']['password'];

    // Query URL
	$url = 'https://' . $hostname . ':2087/json-api/create_user_session?api.version=1&user=' . $username . '&service=cpaneld';

	// Create Curl Object
	$curl = curl_init();

	// Allow self-signed certificates     
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	// and certificates that don't match the hostname
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

	// Do not include header in output
	curl_setopt($curl, CURLOPT_HEADER, false);

	// Return contents of transfer on curl_exec
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	// Set the username and password
	$header[0] = "Authorization: Basic " . base64_encode($username.":".$password) . "\n\r";
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	
	// Execute the query
	curl_setopt($curl, CURLOPT_URL, $url);
	
	// Result
	$result = curl_exec($curl);

	// log error if curl exec fails
	if ($result == false) {

	    error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $url");
	                                                    
	}

	// Decode response
	$decoded_response = json_decode( $result, true );

	// Get the session token
	$themely_cp_session = $decoded_response['data']['cp_security_token'];

	return $themely_cp_session;

}
add_hook('AfterModuleCreate', 10, "hook_themely_cp_session");

function hook_themely_wp_install($vars) {

	// Variables
	$themely_cp_session = hook_themely_cp_session($vars);
	$hostname = $vars['params']['serverhostname'];
    $username = $vars['params']['username'];
    $password = $vars['params']['password'];
    $wp_site_name = 'My Blog';
    $wp_site_tagline = 'New WordPress Site';
    $wp_admin_username = $vars['params']['customfields']['WordPress Admin Username'];
    $wp_admin_password = $vars['params']['customfields']['WordPress Admin Password'];
    $wp_admin_email = $vars['params']['clientsdetails']['email'];
    $wp_site_protocol = 'http://www.';
    $wp_site_domain = $vars['params']['domain'].'|'.'/home/'.$vars['params']['username'].'/public_html';
    $wp_db_name = 'wp';
    $wp_db_user = 'wp';
    $wp_table_prefix = chr(rand(97,122)) . chr(rand(97,122)) . chr(rand(97,122));
    $wp_submit_btn_value = 'Install WordPress';
    // Change the values of the $wp_theme_slug & $wp_theme_url variables to install a different theme
    // Leave blank '' to install the default TwentyTwenty theme
    // Enter 'latest' to install the latest theme in the directory
    // Enter 'random' to install a random theme from the directory
    $wp_theme_slug = 'latest'; // CHANGE VALUES HERE
    $wp_theme_url = 'latest'; // CHANGE VALUES HERE
    
    
    // Form fields
	$data = array(
		'wp_site_name' => $wp_site_name,
		'wp_site_description' => $wp_site_tagline,
		'wp_admin_username' => $wp_admin_username,
		'wp_admin_password' => $wp_admin_password,
		'wp_admin_email' => $wp_admin_email,
		'wp_site_protocol' => $wp_site_protocol,
		'wp_site_domain' => $wp_site_domain,
		'wp_db_name' => $wp_db_name,
		'wp_db_user' => $wp_db_user,
		'wp_table_prefix' => $wp_table_prefix,
		'wp_theme_slug' => $wp_theme_slug,
		'wp_theme_url' => $wp_theme_url,
		'submit' => $wp_submit_btn_value,
	);

	// Login URL
	$url = 'https://' . $hostname . ':2083' . $themely_cp_session . '/frontend/paper_lantern/themely/index.live.php';

	// open connection
	$curl = curl_init();

	// Allow self-signed certs
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

	// Allow certs that do not match the hostname       
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

	// Do not include header in output
	curl_setopt($curl, CURLOPT_HEADER, 0);

	// Prevent return contents of transfer on curl_exec ???
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	// set the username and password 
	$header[0] = "Authorization: Basic " . base64_encode($username.":".$password) . "\n\r";
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);   

	// set the url
	curl_setopt($curl, CURLOPT_URL, $url);

	// Post the fields data
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data) );

	// Prevent page from directing
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);

	// execute post
	$result = curl_exec($curl);

	// log error if curl exec fails
	if ($result == false) {
	    
	    error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $url");   

	}

	// close connection
	curl_close($curl);

}
add_hook('AfterModuleCreate', 10, "hook_themely_wp_install");