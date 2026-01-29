<?php
/**
 * Image Tool - WebP Converter Module
 *
 * @package WP_Tools
 */

defined( 'ABSPATH' ) || die;

/**
 * Enqueue admin styles for image tool
 */
function wp_tools_image_enqueue_styles() {
	wp_enqueue_style(
		'wp-tools-image-styles',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/image-tool.css',
		array(),
		'1.0.0',
		'all'
	);
}
add_action( 'admin_enqueue_scripts', 'wp_tools_image_enqueue_styles' );

/**
 * Initialize image tool on admin init
 */
add_action( 'init', 'wp_tools_image_tool_init' );

function wp_tools_image_tool_init() {
	add_action( 'admin_notices', 'wp_tools_image_admin_notices' );
	add_action( 'admin_init', 'wp_tools_image_register_settings' );
	add_filter( 'wp_handle_upload', 'wp_tools_handle_upload_convert_to_webp' );
}

/**
 * Display admin notices for Imagick extension and WebP conversion settings.
 */
function wp_tools_image_admin_notices() {
	$screen = get_current_screen();
	if ( ! in_array( $screen->id, array( 'upload', 'media' ), true ) ) {
		return;
	}

	if ( ! extension_loaded( 'imagick' ) ) {
		echo "<div class='notice notice-warning is-dismissible'><p><strong>Warning:</strong> The Imagick PHP extension required for WebP conversion is not installed or enabled. Please install or enable Imagick.</p></div>";
	} else {
		$settings_url            = admin_url( 'options-media.php' );
		$webp_conversion_enabled = get_option( 'wp_tools_convert_to_webp', false );

		if ( $webp_conversion_enabled ) {
			echo "<div class='notice notice-info is-dismissible'><p><strong>Enabled:</strong> WebP image conversion is active. You can disable this feature on the <a href='" . esc_url( $settings_url ) . "'>Settings > Media</a> page.</p></div>";
		} else {
			echo "<div class='notice notice-info is-dismissible'><p><strong>Disabled:</strong> WebP image conversion is not active. You can enable this feature on the <a href='" . esc_url( $settings_url ) . "'>Settings > Media</a> page.</p></div>";
		}
	}
}

/**
 * Register settings for WebP conversion and image processing.
 */
function wp_tools_image_register_settings() {
	register_setting(
		'media',
		'wp_tools_convert_to_webp',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
		)
	);

	register_setting(
		'media',
		'wp_tools_max_width',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 1920,
		)
	);

	register_setting(
		'media',
		'wp_tools_max_height',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 1080,
		)
	);

	register_setting(
		'media',
		'wp_tools_compression_quality',
		array(
			'type'              => 'integer',
			'sanitize_callback' => function ( $value ) {
				return max( 1, min( 100, absint( $value ) ) );
			},
			'default'           => 80,
		)
	);

	add_settings_field(
		'wp_tools_convert_to_webp',
		__( 'Convert Uploaded Images to WebP', 'wp-tools' ),
		'wp_tools_field_callback',
		'media',
		'default',
		array( 'label_for' => 'wp_tools_convert_to_webp' )
	);

	add_settings_field(
		'wp_tools_image_settings',
		__( 'Image Processing Settings', 'wp-tools' ),
		'wp_tools_image_settings_callback',
		'media',
		'default'
	);
}

/**
 * Display the image processing settings fields.
 */
function wp_tools_image_settings_callback() {
	$max_width            = get_option( 'wp_tools_max_width', 1920 );
	$max_height           = get_option( 'wp_tools_max_height', 1080 );
	$compression_quality  = get_option( 'wp_tools_compression_quality', 80 );

	echo '<fieldset>';
	echo '<p><label for="wp_tools_max_width">Max Width (pixels):</label><br>';
	echo '<input type="number" id="wp_tools_max_width" name="wp_tools_max_width" value="' . esc_attr( $max_width ) . '" min="1" max="9999" /></p>';

	echo '<p><label for="wp_tools_max_height">Max Height (pixels):</label><br>';
	echo '<input type="number" id="wp_tools_max_height" name="wp_tools_max_height" value="' . esc_attr( $max_height ) . '" min="1" max="9999" /></p>';

	echo '<p><label for="wp_tools_compression_quality">Compression Quality (1-100):</label><br>';
	echo '<input type="number" id="wp_tools_compression_quality" name="wp_tools_compression_quality" value="' . esc_attr( $compression_quality ) . '" min="1" max="100" /></p>';
	echo '</fieldset>';
}

/**
 * Display the WebP conversion setting field.
 */
function wp_tools_field_callback() {
	$value = get_option( 'wp_tools_convert_to_webp', false );
	echo '<input type="checkbox" id="wp_tools_convert_to_webp" name="wp_tools_convert_to_webp" ' . checked( 1, $value, false ) . ' value="1">';
	echo '<label for="wp_tools_convert_to_webp">' . esc_html__( 'Enable automatic conversion of uploaded images to WebP format.', 'wp-tools' ) . '</label>';
}

/**
 * Handle the image upload and convert to WebP format.
 *
 * @param array $upload Array containing the upload data.
 * @return array Modified upload data.
 */
function wp_tools_handle_upload_convert_to_webp( $upload ) {
	if ( ! get_option( 'wp_tools_convert_to_webp' ) ) {
		return $upload; // Skip the conversion if not enabled.
	}

	$max_width           = get_option( 'wp_tools_max_width', 1920 );
	$max_height          = get_option( 'wp_tools_max_height', 1080 );
	$compression_quality = get_option( 'wp_tools_compression_quality', 80 );

	$valid_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/heic' );

	if ( in_array( $upload['type'], $valid_types, true ) ) {
		$file_path = $upload['file'];

		if ( extension_loaded( 'imagick' ) ) {
			try {
				$imagick = new Imagick( $file_path );

				// Adjust orientation based on EXIF data.
				switch ( $imagick->getImageOrientation() ) {
					case Imagick::ORIENTATION_BOTTOMRIGHT:
						$imagick->rotateImage( '#000', 180 );
						break;
					case Imagick::ORIENTATION_RIGHTTOP:
						$imagick->rotateImage( '#000', 90 );
						break;
					case Imagick::ORIENTATION_LEFTBOTTOM:
						$imagick->rotateImage( '#000', -90 );
						break;
				}

				$imagick->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );

				$original_width   = $imagick->getImageWidth();
				$original_height  = $imagick->getImageHeight();
				$aspect_ratio     = $original_width / $original_height;
				$new_width        = $original_width;
				$new_height       = $original_height;

				if ( $original_width > $max_width || $original_height > $max_height ) {
					if ( $new_width > $max_width ) {
						$new_width  = $max_width;
						$new_height = $new_width / $aspect_ratio;
					}
					if ( $new_height > $max_height ) {
						$new_height = $max_height;
						$new_width  = $new_height * $aspect_ratio;
					}
				}

				$imagick->resizeImage( $new_width, $new_height, Imagick::FILTER_LANCZOS, 1 );
				$imagick->setImageFormat( 'webp' );
				$imagick->setImageCompression( Imagick::COMPRESSION_JPEG );
				$imagick->setImageCompressionQuality( $compression_quality );

				$file_info      = pathinfo( $file_path );
				$dirname        = $file_info['dirname'];
				$filename       = $file_info['filename'];
				$new_file_path  = $dirname . '/' . $filename . '.webp';
				$imagick->writeImage( $new_file_path );

				// Compare file sizes.
				$original_size = filesize( $file_path );
				$new_size      = filesize( $new_file_path );

				if ( $new_size < $original_size ) {
					if ( file_exists( $new_file_path ) ) {
						$upload['file'] = $new_file_path;
						$upload['url']  = str_replace( basename( $upload['url'] ), basename( $new_file_path ), $upload['url'] );
						$upload['type'] = 'image/webp';
						@unlink( $file_path );
					}
				} else {
					@unlink( $new_file_path );
				}

				$imagick->clear();
				$imagick->destroy();

			} catch ( Exception $e ) {
				// Log error or handle gracefully.
				error_log( 'WebP conversion failed: ' . $e->getMessage() );
			}
		} else {
			// Only show this message to admins in debug mode.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && current_user_can( 'manage_options' ) ) {
				add_action(
					'admin_notices',
					function() {
						echo '<div class="notice notice-error"><p>Imagick is not installed. WebP conversion cannot be performed.</p></div>';
					}
				);
			}
		}
	}

	return $upload;
}
