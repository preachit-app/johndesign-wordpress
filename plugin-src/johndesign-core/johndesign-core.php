<?php
/**
 * Plugin Name: John Design Core
 * Description: Modules Gutenberg John Design, import V2.12, portfolio, formulaire sécurisé et mises à jour GitHub.
 * Version: 2.13.5
 * Update URI: https://github.com/preachit-app/johndesign-wordpress
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: John Design
 */
if (!defined('ABSPATH')) exit;
define('JD_CORE_VERSION', '2.13.5');
define('JD_CORE_DIR', plugin_dir_path(__FILE__));
define('JD_CORE_URL', plugin_dir_url(__FILE__));
require_once JD_CORE_DIR.'includes/sections.php';
require_once JD_CORE_DIR.'includes/contact.php';
require_once JD_CORE_DIR.'includes/admin.php';
require_once JD_CORE_DIR.'includes/updater.php';
require_once JD_CORE_DIR.'includes/seo.php';
require_once JD_CORE_DIR.'includes/launch.php';

/** Persist the large V2.12 section library in WordPress so future updates can be lightweight. */
function jd_core_seed_content_library_2124(){
    $sections_file = JD_CORE_DIR . 'data/sections.json';
    $pages_file    = JD_CORE_DIR . 'data/pages.json';
    if (file_exists($sections_file)) {
        $sections = json_decode(file_get_contents($sections_file), true);
        if (is_array($sections) && $sections) update_option('jd_core_sections_library', $sections, false);
    }
    if (file_exists($pages_file)) {
        $pages = json_decode(file_get_contents($pages_file), true);
        if (is_array($pages) && $pages) update_option('jd_core_pages_library', $pages, false);
    }
    update_option('jd_core_library_version', JD_CORE_VERSION, false);
}
register_activation_hook(__FILE__, 'jd_core_seed_content_library_2124');
add_action('admin_init', function(){
    if (!get_option('jd_core_sections_library')) jd_core_seed_content_library_2124();
}, 5);

function jd_core_front_assets_2124(){
    wp_enqueue_style('jd-core-frontend', JD_CORE_URL.'assets/frontend.css', [], JD_CORE_VERSION);
}
add_action('wp_enqueue_scripts','jd_core_front_assets_2124',30);
add_action('enqueue_block_editor_assets','jd_core_front_assets_2124',30);

/** Warm WordPress.com mShots cache once for the public portfolio URLs. */
function jd_core_warm_mshots_2124(){
    if (!is_admin() || get_option('jd_mshots_warmed_2124')) return;
    $targets = [
        'https://actionformationtheresa.fr/',
        'https://www.capmulticloture.com/',
        'https://eglise-marseille-kleber.fr/',
        'https://www.unmaxdevie.com/',
        'https://www.sonicscape-studio.fr/',
        'https://www.acm-construction-piscine.fr/',
        'http://bourgaud-services.fr/',
    ];
    foreach ($targets as $target) {
        $shot = 'https://s0.wp.com/mshots/v1/' . rawurlencode($target) . '?w=1200&h=675';
        wp_remote_get($shot, ['timeout'=>0.01, 'blocking'=>false, 'redirection'=>2]);
    }
    update_option('jd_mshots_warmed_2124', 1, false);
}
add_action('admin_init','jd_core_warm_mshots_2124',20);
