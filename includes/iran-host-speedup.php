<?php
/**
 * Iran Host Speedup Module
 *
 * Blocks external host requests for performance optimization.
 *
 * @package WP_Tools
 */

defined( 'ABSPATH' ) || die;

/**
 * Check if text contains a string
 *
 * @param string $text The text to search in.
 * @param string $string The string to search for.
 * @return bool True if string is found, false otherwise.
 */
function wp_tools_text_has_string( $text, $string ) {
	return strpos( $text, $string ) !== false;
}

/**
 * Block external host requests for performance
 *
 * @param bool|array $false Whether to preempt an HTTP request.
 * @param array      $parsed_args HTTP request arguments.
 * @param string     $url The URL being requested.
 * @return bool|array Blocked response or original false.
 */
function wp_tools_block_external_host_requests( $false, $parsed_args, $url ) {
	$blocked_hosts = array(
		'elementor.com',
		'github.com',
		'yoast.com',
		'yoa.st',
		'unyson.io',
		'siteorigin.com',
		'woocommerce.ir',
		'woocommerce.com',
	);

	foreach ( $blocked_hosts as $host ) {
		if ( ! empty( $host ) && wp_tools_text_has_string( $url, $host ) ) {
			return array(
				'headers'  => '',
				'body'     => '',
				'response' => '',
				'cookies'  => '',
				'filename' => '',
			);
		}
	}

	return $false;
}
add_filter( 'pre_http_request', 'wp_tools_block_external_host_requests', 10, 3 );
