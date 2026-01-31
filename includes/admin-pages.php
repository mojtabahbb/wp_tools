<?php
/**
 * Admin pages for WP Tools
 *
 * @package WP_Tools
 */

defined( 'ABSPATH' ) || die;

add_action( 'admin_menu', 'wp_tools_add_admin_menu' );

function wp_tools_add_admin_menu() {
    $capability = 'manage_options';

    // Top-level menu: 'wp-tool' text and tool icon
    add_menu_page(
        __( 'WP Tools', 'wp-tools' ),
        'wp-tool',
        $capability,
        'wp-tools',
        'wp_tools_admin_dashboard',
        'dashicons-admin-tools',
        25
    );

    // Submenu: Settings
    add_submenu_page(
        'wp-tools',
        __( 'WP Tools Settings', 'wp-tools' ),
        __( 'Settings', 'wp-tools' ),
        $capability,
        'wp-tools-settings',
        'wp_tools_admin_settings_page'
    );

    // Get enabled modules
    $modules = wp_tools_get_modules_list();
    $saved   = get_option( 'wp_tools_modules', array() );

    // Submenu: Image Tool (link directly to Media settings)
    if ( isset( $modules['image-tool'] ) && ( empty( $saved ) || ( isset( $saved['image-tool'] ) && $saved['image-tool'] ) ) ) {
        add_submenu_page(
            'wp-tools',
            __( 'Image Tool', 'wp-tools' ),
            __( 'Image Tool', 'wp-tools' ),
            $capability,
            'options-media.php'
        );
    }

    // Submenu: Iran Host Speedup
    if ( isset( $modules['iran-host-speedup'] ) && ( empty( $saved ) || ( isset( $saved['iran-host-speedup'] ) && $saved['iran-host-speedup'] ) ) ) {
        add_submenu_page(
            'wp-tools',
            __( 'Iran Host Speedup', 'wp-tools' ),
            __( 'Iran Host Speedup', 'wp-tools' ),
            $capability,
            'wp-tools-iran-host-speedup',
            'wp_tools_admin_iran_host_page'
        );
    }
}

function wp_tools_admin_dashboard() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    echo '<div class="wrap"><h1>WP Tools</h1><p>Welcome to the WP Tools plugin. Use the menu on the left to configure modules.</p></div>';
}

function wp_tools_get_modules_list() {
    return array(
        'image-tool' => __( 'Image Tool', 'wp-tools' ),
        'iran-host-speedup' => __( 'Iran Host Speedup', 'wp-tools' ),
    );
}

function wp_tools_admin_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $modules = wp_tools_get_modules_list();
    $saved  = get_option( 'wp_tools_modules', array() );

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wp_tools_modules_nonce'] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_tools_modules_nonce'] ) ), 'wp_tools_save_modules' ) ) {
            wp_die( 'Nonce verification failed' );
        }
        $new = array();
        foreach ( $modules as $key => $label ) {
            $new[ $key ] = isset( $_POST['module'][ $key ] ) ? 1 : 0;
        }
        update_option( 'wp_tools_modules', $new );
        // Redirect to avoid resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=wp-tools-settings&updated=1' ) );
        exit;
    }

    $updated = isset( $_GET['updated'] ) ? true : false;

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'WP Tools Settings', 'wp-tools' ) . '</h1>';
    if ( $updated ) {
        echo '<div class="updated notice is-dismissible"><p>' . esc_html__( 'Settings saved.', 'wp-tools' ) . '</p></div>';
    }

    echo '<form method="post">';
    wp_nonce_field( 'wp_tools_save_modules', 'wp_tools_modules_nonce' );
    echo '<table class="form-table"><tbody>';

    foreach ( $modules as $key => $label ) {
        $enabled = isset( $saved[ $key ] ) ? (bool) $saved[ $key ] : true;
        echo '<tr><th scope="row"><label for="module_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
        echo '<label><input type="checkbox" id="module_' . esc_attr( $key ) . '" name="module[' . esc_attr( $key ) . ']" value="1" ' . checked( true, $enabled, false ) . '> ' . esc_html__( 'Enabled', 'wp-tools' ) . '</label>';
        echo '</td></tr>';
    }

    echo '</tbody></table>';
    submit_button();
    echo '</form>';
    echo '</div>';
}



function wp_tools_admin_iran_host_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $default_hosts = array(
        'elementor.com',
        'github.com',
        'yoast.com',
        'yoa.st',
        'unyson.io',
        'siteorigin.com',
        'woocommerce.ir',
        'woocommerce.com',
    );

    $saved_hosts = get_option( 'wp_tools_blocked_hosts', $default_hosts );

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wp_tools_blocked_hosts_nonce'] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_tools_blocked_hosts_nonce'] ) ), 'wp_tools_save_blocked_hosts' ) ) {
            wp_die( 'Nonce verification failed' );
        }

        $submitted = array();
        if ( isset( $_POST['hosts'] ) && is_array( $_POST['hosts'] ) ) {
            foreach ( $_POST['hosts'] as $h ) {
                $h = sanitize_text_field( wp_unslash( $h ) );
                // Normalize host (remove protocol/path)
                $parsed = wp_parse_url( $h );
                if ( ! empty( $parsed['host'] ) ) {
                    $host = $parsed['host'];
                } else {
                    $host = $h;
                }
                // Keep only allowed characters
                $host = preg_replace( '/[^A-Za-z0-9\.-]/', '', $host );
                if ( $host ) {
                    $submitted[] = strtolower( $host );
                }
            }
        }

        if ( isset( $_POST['add_host'] ) && ! empty( $_POST['add_host'] ) ) {
            $new = sanitize_text_field( wp_unslash( $_POST['add_host'] ) );
            $parsed = wp_parse_url( $new );
            if ( ! empty( $parsed['host'] ) ) {
                $new = $parsed['host'];
            }
            $new = preg_replace( '/[^A-Za-z0-9\.-]/', '', $new );
            if ( $new ) {
                $submitted[] = strtolower( $new );
            }
        }

        // Ensure unique and reindex
        $submitted = array_values( array_unique( $submitted ) );
        update_option( 'wp_tools_blocked_hosts', $submitted );

        wp_safe_redirect( admin_url( 'admin.php?page=wp-tools-iran-host-speedup&updated=1' ) );
        exit;
    }

    $updated = isset( $_GET['updated'] ) ? true : false;

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Iran Host Speedup', 'wp-tools' ) . '</h1>';
    if ( $updated ) {
        echo '<div class="updated notice is-dismissible"><p>' . esc_html__( 'Blocked hosts updated.', 'wp-tools' ) . '</p></div>';
    }

    echo '<form method="post">';
    wp_nonce_field( 'wp_tools_save_blocked_hosts', 'wp_tools_blocked_hosts_nonce' );

    echo '<p>' . esc_html__( 'Toggle hosts you want to block. Unchecked hosts will be removed from the blocked list.', 'wp-tools' ) . '</p>';

    echo '<table class="widefat fixed striped"><thead><tr><th>' . esc_html__( 'Blocked Host', 'wp-tools' ) . '</th><th>' . esc_html__( 'Blocked', 'wp-tools' ) . '</th></tr></thead><tbody>';

    $all_hosts = ! empty( $saved_hosts ) ? $saved_hosts : $default_hosts;
    foreach ( $all_hosts as $host ) {
        $host_esc = esc_html( $host );
        echo '<tr><td>' . $host_esc . '</td><td><label><input type="checkbox" name="hosts[]" value="' . esc_attr( $host ) . '" checked> ' . esc_html__( 'Blocked', 'wp-tools' ) . '</label></td></tr>';
    }

    echo '</tbody></table>';

    echo '<p><label for="add_host">' . esc_html__( 'Add host (e.g. example.com):', 'wp-tools' ) . '</label><br>';
    echo '<input type="text" id="add_host" name="add_host" value="" class="regular-text" /></p>';

    submit_button( __( 'Save Blocked Hosts', 'wp-tools' ) );
    echo '</form>';
    echo '</div>';
}
