<?php defined('ABSPATH') || die;
/**
 * RTL SpeedUp Iranian Host Wordpress Dashboard
 *
 * @author            mojtabahbb
 * @copyright         mojtabahbb
 * @license           mojtabahbb
 *
 * @wordpress-plugin
 *
 * Plugin Name:         افزایش سرعت هاست ایرانی
 * Plugin URI:          https://www.mojtabahbb.ir/
 * Description:         افزونه ای جهت افزایش سرعت داشبورد وردپرس در هاست‌های ایرانی
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.2
 * Author:              mojtabahbb
 * Author URI:          https://www.mojtabahbb.ir/
 * License:             GNU
 * License URI:         https://www.mojtabahbb.ir/
 * Text Domain:         mojtabahbb
 * Domain Path:         /languages
 */



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