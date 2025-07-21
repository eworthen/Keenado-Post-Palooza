<?php

use app\models\KeenadoPostGrid;
use app\models\KeenadoHorizontalPostGrid;

// Ensure the script is being run within WordPress
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**********************************************
 * Admin Dashboard
 **********************************************/
function keenado_post_palooza_plugin_admin_page() {
    try {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="h-[calc(100%-20px)] w-[calc(100%-20px)] m-2.5 bg-transparent">';
        echo '<h3 class="text-3xl font-bold text-center text-gray-800 mb-8">Post Palooza Admin</h3>';
        include_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/includes/admin/cards-config.php';
        echo '</div>';

    } catch (Exception $e) {
        echo '<p>An error occurred: ' . esc_html($e->getMessage()) . '</p>';
    }
}

/**********************************************
 * Shortcode: [post_palooza_grid_view]
 **********************************************/
function keenado_post_grid_shortcode($atts) {
    require_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/models/KeenadoPostGrid.php';

    $grid_id = uniqid('post_grid_');
    $atts['grid_id'] = $grid_id; // Pass grid ID to the class

    $keenado_post_grid = new KeenadoPostGrid($atts);
    return $keenado_post_grid->render();
}
add_shortcode('post_palooza_grid_view', 'keenado_post_grid_shortcode');

/**********************************************
 * Shortcode: [post_palooza_horizontal_grid_view]
 **********************************************/
function keenado_horizontal_post_grid_shortcode($atts) {
    require_once KEENADO_POST_PALOOZA_PLUGIN_DIR . 'app/models/KeenadoHorizontalPostGrid.php';

    $grid_id = uniqid('post_grid_');
    $atts['grid_id'] = $grid_id;

    $keenado_horizontal_post_grid = new KeenadoHorizontalPostGrid($atts);
    return $keenado_horizontal_post_grid->render();
}
add_shortcode('post_palooza_horizontal_grid_view', 'keenado_horizontal_post_grid_shortcode');
