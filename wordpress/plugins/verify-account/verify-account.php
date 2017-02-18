<?php
/*
 * Plugin Name: Verify Account ID
 * Description: verifies account id
 * Author: Soohee Lee
 */


/**
 * Register and enqueue javascript and css files.
 *
 * This function hooks on to the Wordpress wp_enqueue_scripts
 * action hook.
 */
function myplugin_scripts() {
    wp_enqueue_script( 'ce_account-profile-script',  plugin_dir_url( __FILE__ ) . 'js/ajax-authentication.js', array( 'jquery' ), '1.0.0' );

    $account_id_nonce = wp_create_nonce( 'account_id_nonce' );
    wp_localize_script( 'ce_account-profile-script', 'myajax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => $account_id_nonce,
            ));
}
add_action( 'wp_enqueue_scripts', 'myplugin_scripts' );

/**
 * Load templates for pages 4.8.1 and 4.8.2.
 *
 * This filter function hooks on to the Wordpress single_template hook.
 */
function get_custom_post_type_template($single_template) {
    global $post;
    if ($post->post_type == 'post' && ( is_single( '66' ) || is_single( '64' ) || is_single( '78' ))) {
        $single_template = dirname( __FILE__ ) . '/templates/verify-account-display.php';
    }
    return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );


 /**
 * Handles AJAX request.
 *
 * This function hooks on to the Wordpress wp_ajax action.
 * It is a custom handler for the AJAX request from
 * js/ajax-authentication.js and generates a different response page
 * depending on which page the AJAX request is sent from. If the request
 * comes from page 4.8.1, this function sends back the
 * account-profile-display.php template. If the request comes from page
 * 4.8.2, it sends back carbon-profile-display.php template.
 */
function my_ajax_function() {

    $errors = array();
    $data = array();

    // Check nonce
    if ( ! check_ajax_referer( 'account_id_nonce', 'security', false ) ) {
        $errors['unauthorized'] = 'Unauthorized request';
    }

    // Check if user is logged in
    if ( ! current_user_can('read_private_posts') ) {
        $errors['login'] = 'Login to see your profile';
    }

    // Validate account ID
    if ( empty($_POST['account_id']) ) {
        $errors['empty'] = 'Account id is required';

        /**
         * @TODO    Do further $_POST['account_id'] validation
         *          and reject invalid data.
         */

    // Verify that account ID is correct using cookie
    } else {
        $account_id = sanitize_text_field( $_POST['account_id'] );
        $cookie_name = "ce_info";
        $cookie_value = "";

        // Get account_id cookie value
        if( isset($_COOKIE[$cookie_name]) ) {
            $cookie_value = $_COOKIE[$cookie_name];
	    $account_id_cookie = explode('|', $cookie_value)[0];
            if ($account_id !== $account_id_cookie) {
                $errors['incorrect'] = "Incorrect account id";
            }

            /**
             * @TODO    Handle the case where account ID cookie is not set.
             */
        }
    }

    if ( ! empty($errors)) {
        $data['success'] = false;
        $data['errors'] = $errors;

    } else {
        $data['success'] = true;
        ob_start();
        if ($_POST['post_id'] === 'post-64') { // page 4.8.1
            include( WP_PLUGIN_DIR . '/account-profile/templates/account-profile-display.php' );
        } elseif ($_POST['post_id'] === 'post-66') { // page 4.8.2
            include( WP_PLUGIN_DIR . '/account-profile/templates/carbon-profile-display.php' );
        } elseif ($_POST['post_id'] === 'post-78') { // page 4.8.7
            include( WP_PLUGIN_DIR . '/account-profile/templates/final-winner-display.php' );
        }
        $data['html'] = ob_get_clean();
    }

    // return data to AJAX call
    echo json_encode($data);
    wp_die();
}
// Hook to Wordpress wp_ajax_ action hook
add_action('wp_ajax_my_ajax_function', 'my_ajax_function');
add_action('wp_ajax_nopriv_my_ajax_function', 'my_ajax_function');
