<?php defined('ABSPATH') || die;

function rtlTextHasString($text, $string) {
	return strpos($text, $string) !== false;
}


function rtlBlockExternalHostRequests ($false, $parsed_args, $url) {
	$blockedHosts = [
		'elementor.com',
		'github.com',
		'yoast.com',
		'yoa.st',
		'unyson.io',
		'siteorigin.com',
		'woocommerce.ir',
		'woocommerce.com'
	];

	foreach ( $blockedHosts as $host ) {
		if ( !empty($host) && rtlTextHasString($url, $host) ) {
			return [
				'headers'  => '',
				'body'     => '',
				'response' => '',
				'cookies'  => '',
				'filename' => ''
			];
		}
	}

	return $false;
}
add_filter('pre_http_request', 'rtlBlockExternalHostRequests', 10, 3);