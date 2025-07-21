<?php
/*
Plugin Name: Keenado - Post Palooza
Description: A plugin to list posts. POST GRID VIEW Shortcode: [post_palooza_grid_view]
Version: 1.0
Author: eworthen
*/

use app\models\KeenadoPostGrid;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**********************************************
 * Plugin Constants
 **********************************************/
define( 'KEENADO_POST_PALOOZA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KEENADO_POST_PALOOZA_URL_DIR', plugin_dir_url( __FILE__ ) );


/**********************************************
 * AJAX Handlers
 **********************************************/
add_action( 'init', 'keenado_post_palooza_plugin_init' ); // initialize plugin
add_action( 'admin_menu', 'keenado_post_palooza_plugin_menu' ); // admin dashboard menu
add_action( 'wp_enqueue_scripts', 'keenado_posts_plugin_enqueue_frontend_scripts' ); // front end css
add_action( 'admin_enqueue_scripts', 'keenado_posts_plugin_enqueue_admin_scripts' ); // admin dashboard css
add_action( 'wp_ajax_keenado_pagination', 'keenado_handle_pagination' ); // admin pagination
add_action( 'wp_ajax_nopriv_keenado_pagination', 'keenado_handle_pagination' ); // user pagination

// Include the main plugin file
require_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/keenado-post-palooza-plugin.php';

// Initialize the plugin
function keenado_post_palooza_plugin_init() {
    // Any initialization or setup logic for your plugin
}

/**********************************************
 * Plugin Admin Dashboard
 **********************************************/
function keenado_post_palooza_plugin_menu() {
    // [0] page title, [1] menu title, [2] capability, [3] menu slug, [4] Admin function to display in dashboard, [5] link to icon file, [6] position in admin panel from top
    add_menu_page(
        'Keenado Post Palooza Plugin', 
        'Post Palooza', 
        'manage_options',
        'keenado_post_palooza_plugin_dashboard', 
        'keenado_post_palooza_plugin_admin_page', 
        'dashicons-megaphone',
        8,
    );
}

/**********************************************
 * Enqueue Styles and Scripts for Admin and Front End
 **********************************************/
// Enqueue TailwindCSS for the front end
function keenado_posts_plugin_enqueue_frontend_scripts() {
    // Enqueue the TailwindCSS output file for front-end
    wp_enqueue_style( 
        'keenado-post-palooza-styles', 
        KEENADO_POST_PALOOZA_URL_DIR. 'app/assets/css/output.css', 
        array(), 
        '1.0.0', 
        'all' 
    );

    // Enqueue AJAX for post pagination
    wp_enqueue_script( 
        'keenado-post-palooza-ajax', 
        KEENADO_POST_PALOOZA_URL_DIR. 'app/assets/js/pagination-ajax.js', 
        ['jquery'], 
        '1.0.0', 
        true 
    );

    // Pass AJAX URL to the script
    wp_localize_script(
        'keenado-post-palooza-ajax',
        'keenado_ajax',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );

}

// Enqueue TailwindCSS for the admin dashboard
function keenado_posts_plugin_enqueue_admin_scripts() {
    // Enqueue the TailwindCSS output file for admin pages
    wp_enqueue_style( 
        'keenado-post-palooza-admin-styles', 
        KEENADO_POST_PALOOZA_URL_DIR . 'app/assets/css/output.css', 
        array(), 
        '1.0.0', 
        'all' 
    );
}

function keenado_handle_pagination() {
    if (
        !isset($_POST['page']) ||
        !isset($_POST['grid_id']) ||
        !isset($_POST['layout'])
    ) {
        wp_send_json_error('Missing required pagination parameters.');
        wp_die();
    }

    $page    = intval($_POST['page']);
    $grid_id = sanitize_text_field($_POST['grid_id']);
    $layout  = sanitize_text_field($_POST['layout']);

    $atts = [
        'posts_per_page'          => 3,
        'paged'                   => $page,
        'grid_id'                 => $grid_id,
        'title_font_color'        => '#333333',
        'bg_color'                => '#ffffff',
        'title_font_family'       => 'font-sans',
        'description_font_color'  => '#666666',
        'description_font_family' => 'font-serif',
    ];

    try {
        if ($layout === 'horizontal') {
            require_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/models/KeenadoHorizontalPostGrid.php';
            $renderer = new \app\models\KeenadoHorizontalPostGrid($atts);
        } elseif ($layout === 'vertical') {
            require_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/models/KeenadoPostGrid.php';
            $renderer = new \app\models\KeenadoPostGrid($atts);
        } else {
            wp_send_json_error('Invalid layout type.');
            wp_die();
        }

        $content = $renderer->render();
        wp_send_json_success($content);
    } catch (Exception $e) {
        wp_send_json_error('Error rendering posts: ' . $e->getMessage());
    }

    wp_die();
}

