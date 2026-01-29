<?php
/**
 * wordpress tools plugin
 *
 * @author            mojtabahbb
 * @copyright         mojtabahbb
 * @license           mojtabahbb
 *
 * @wordpress-plugin
 *
 * Plugin Name:         مجموعه ابزارهای وردپرس
 * Plugin URI:          https://www.mojtabahbb.ir/
 * Description:         مجموعه‌ای از ابزارهای کاربردی برای بهبود عملکرد و تجربه کاربری در وب‌سایت‌های وردپرسی.
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

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// 1. Load the WebP Converter
// We use require_once to ensure the file exists and is loaded only once
require_once plugin_dir_path( __FILE__ ) . 'image-tool.php';

// 2. Load the Host Speedup
require_once plugin_dir_path( __FILE__ ) . 'iran-host-speedup.php';