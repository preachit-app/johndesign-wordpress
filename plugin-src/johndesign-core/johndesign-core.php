<?php
/**
 * Plugin Name: John Design Core
 * Description: Modules Gutenberg John Design, import V2.12, portfolio, formulaire sécurisé et mises à jour GitHub.
 * Version: 2.16.5
 * Update URI: https://github.com/preachit-app/johndesign-wordpress
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: John Design
 */
if (!defined('ABSPATH')) exit;
define('JD_CORE_VERSION', '2.16.5');
define('JD_CORE_DIR', plugin_dir_path(__FILE__));
define('JD_CORE_URL', plugin_dir_url(__FILE__));
require_once JD_CORE_DIR.'includes/sections.php';
require_once JD_CORE_DIR.'includes/contact.php';
require_once JD_CORE_DIR.'includes/admin.php';
require_once JD_CORE_DIR.'includes/updater.php';
require_once JD_CORE_DIR.'includes/seo.php';
require_once JD_CORE_DIR.'includes/launch.php';
require_once JD_CORE_DIR.'includes/site-polish.php';
require_once JD_CORE_DIR.'includes/content-cleanup.php';
require_once JD_CORE_DIR.'includes/clarity.php';
require_once JD_CORE_DIR.'includes/web-layout.php';
require_once JD_CORE_DIR.'includes/service-galleries.php';

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
    wp_enqueue_script('jd-core-frontend', JD_CORE_URL.'assets/frontend.js', [], JD_CORE_VERSION, true);
    wp_localize_script('jd-core-frontend','JD_CORE_ASSETS',[
        'umdv'=>JD_CORE_URL.'assets/portfolio/umdv-site.webp',
        'truck'=>JD_CORE_URL.'assets/portfolio/eco-clim-truck.webp',
    ]);
}
add_action('wp_enqueue_scripts','jd_core_front_assets_2124',30);
add_action('enqueue_block_editor_assets','jd_core_front_assets_2124',30);

function jd_core_whatsapp_number_2151(){
    $raw=(string)get_option('jd_whatsapp_number','');
    $digits=preg_replace('/\D+/','',$raw);
    if(!$digits) return '';
    if(strpos($digits,'00')===0) $digits=substr($digits,2);
    if(strlen($digits)===10 && $digits[0]==='0') $digits='33'.substr($digits,1);
    return $digits;
}
function jd_core_whatsapp_button_2151(){
    if(is_admin()) return;
    $number=jd_core_whatsapp_number_2151();
    if(!$number) return;
    $message=rawurlencode('Bonjour John Design, je vous contacte depuis le site au sujet de mon projet.');
    $url='https://wa.me/'.$number.'?text='.$message;
    echo '<a class="jd-whatsapp-float" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" aria-label="Contacter John Design sur WhatsApp" title="WhatsApp">
      <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 4.5c-6.3 0-11.4 4.9-11.4 11 0 2.1.6 4.1 1.7 5.8L4.5 27.5l6.4-1.7c1.6.9 3.3 1.3 5.1 1.3 6.3 0 11.4-4.9 11.4-11S22.3 4.5 16 4.5zm0 20.6c-1.6 0-3.2-.4-4.6-1.2l-.7-.4-3.8 1 1-3.7-.4-.7a9.1 9.1 0 0 1-1.4-4.8c0-5 4.3-9.1 9.7-9.1 5.3 0 9.7 4.1 9.7 9.1S21.3 25.1 16 25.1zm5.5-6.8c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.2-.2.2-.3.2-.7.1-1.8-.9-3-1.6-4.2-3.6-.3-.5.3-.5.9-1.6.1-.2.1-.4 0-.6-.1-.2-.7-1.7-1-2.3-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.2 2.4 3.6 5.8 5 2.2.9 3.1 1 4.2.9.7-.1 1.8-.7 2.1-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg><span>WhatsApp</span>
    </a>';
}
add_action('wp_footer','jd_core_whatsapp_button_2151',30);

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
