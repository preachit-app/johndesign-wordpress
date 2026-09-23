<?php
/** John Design Core updater — robust GitHub channel. */
if (!defined('ABSPATH')) exit;

if (!defined('JD_REMOTE_REPO')) define('JD_REMOTE_REPO', 'https://github.com/preachit-app/johndesign-wordpress');
if (!defined('JD_REMOTE_API_MANIFEST')) define('JD_REMOTE_API_MANIFEST', 'https://api.github.com/repos/preachit-app/johndesign-wordpress/contents/updates/johndesign-core.json?ref=main');
if (!defined('JD_REMOTE_RAW_MANIFEST')) define('JD_REMOTE_RAW_MANIFEST', 'https://raw.githubusercontent.com/preachit-app/johndesign-wordpress/main/updates/johndesign-core.json');
if (!defined('JD_REMOTE_GITHUB_RAW_MANIFEST')) define('JD_REMOTE_GITHUB_RAW_MANIFEST', 'https://github.com/preachit-app/johndesign-wordpress/raw/refs/heads/main/updates/johndesign-core.json');

function jd_core_plugin_basename(){
    return plugin_basename(JD_CORE_DIR . 'johndesign-core.php');
}

function jd_core_decode_manifest_response($body){
    $body = trim((string) $body);
    if ($body === '') return null;
    $data = json_decode($body, true);
    if (is_array($data) && !empty($data['version']) && !empty($data['download_url'])) return $data;
    if (is_array($data) && ($data['encoding'] ?? '') === 'base64' && !empty($data['content'])) {
        $decoded = base64_decode(str_replace(["\r", "\n"], '', $data['content']), true);
        if ($decoded !== false) {
            $inner = json_decode($decoded, true);
            if (is_array($inner) && !empty($inner['version']) && !empty($inner['download_url'])) return $inner;
        }
    }
    return null;
}

function jd_core_store_update_diag($ok, $endpoint='', $status=0, $error=''){
    update_option('jd_core_update_diag', [
        'ok' => (bool) $ok,
        'endpoint' => (string) $endpoint,
        'status' => (int) $status,
        'error' => (string) $error,
        'checked_at' => current_time('mysql'),
    ], false);
}

function jd_core_remote_manifest($force=false){
    $key = 'jd_core_remote_manifest_v2';
    if (!$force) {
        $cached = get_site_transient($key);
        if (is_array($cached) && !empty($cached['version'])) return $cached;
    }

    $endpoints = [
        [JD_REMOTE_API_MANIFEST, [
            'Accept' => 'application/vnd.github.raw+json',
            'User-Agent' => 'JohnDesignCore/' . (defined('JD_CORE_VERSION') ? JD_CORE_VERSION : 'unknown'),
            'Cache-Control' => 'no-cache',
        ]],
        [add_query_arg('v', time(), JD_REMOTE_RAW_MANIFEST), [
            'Accept' => 'application/json',
            'User-Agent' => 'JohnDesignCore/' . (defined('JD_CORE_VERSION') ? JD_CORE_VERSION : 'unknown'),
            'Cache-Control' => 'no-cache',
        ]],
        [add_query_arg('v', time(), JD_REMOTE_GITHUB_RAW_MANIFEST), [
            'Accept' => 'application/json',
            'User-Agent' => 'JohnDesignCore/' . (defined('JD_CORE_VERSION') ? JD_CORE_VERSION : 'unknown'),
            'Cache-Control' => 'no-cache',
        ]],
    ];

    foreach ($endpoints as [$url, $headers]) {
        $r = wp_remote_get($url, [
            'timeout' => 15,
            'redirection' => 5,
            'sslverify' => true,
            'headers' => $headers,
        ]);
        if (is_wp_error($r)) {
            jd_core_store_update_diag(false, $url, 0, $r->get_error_message());
            continue;
        }
        $status = (int) wp_remote_retrieve_response_code($r);
        $manifest = $status === 200 ? jd_core_decode_manifest_response(wp_remote_retrieve_body($r)) : null;
        if ($manifest) {
            set_site_transient($key, $manifest, 5 * MINUTE_IN_SECONDS);
            jd_core_store_update_diag(true, $url, $status, '');
            return $manifest;
        }
        jd_core_store_update_diag(false, $url, $status, 'HTTP ' . $status . ' ou manifeste invalide');
    }
    return null;
}

function jd_core_update_uri_response($update, $plugin_data, $plugin_file, $locales){
    if ($plugin_file !== jd_core_plugin_basename()) return $update;
    $m = jd_core_remote_manifest();
    if (!$m) return false;
    $current = defined('JD_CORE_VERSION') ? JD_CORE_VERSION : ($plugin_data['Version'] ?? '0.0.0');
    if (!version_compare((string)$m['version'], (string)$current, '>')) return false;
    return [
        'slug' => dirname($plugin_file),
        'version' => sanitize_text_field($m['version']),
        'url' => esc_url_raw($m['homepage'] ?? JD_REMOTE_REPO),
        'package' => esc_url_raw($m['download_url']),
        'tested' => sanitize_text_field($m['tested'] ?? ''),
        'requires_php' => sanitize_text_field($m['requires_php'] ?? '8.0'),
        'autoupdate' => true,
    ];
}
add_filter('update_plugins_github.com', 'jd_core_update_uri_response', 10, 4);

function jd_core_inject_update_transient($transient){
    if (!is_object($transient)) $transient = new stdClass();
    if (!isset($transient->response) || !is_array($transient->response)) $transient->response = [];
    $m = jd_core_remote_manifest();
    if (!$m) return $transient;
    $plugin = jd_core_plugin_basename();
    $current = defined('JD_CORE_VERSION') ? JD_CORE_VERSION : '0.0.0';
    if (version_compare((string)$m['version'], (string)$current, '>')) {
        $transient->response[$plugin] = (object) [
            'id' => JD_REMOTE_REPO,
            'slug' => dirname($plugin),
            'plugin' => $plugin,
            'new_version' => sanitize_text_field($m['version']),
            'url' => esc_url_raw($m['homepage'] ?? JD_REMOTE_REPO),
            'package' => esc_url_raw($m['download_url']),
            'tested' => sanitize_text_field($m['tested'] ?? ''),
            'requires_php' => sanitize_text_field($m['requires_php'] ?? '8.0'),
        ];
    }
    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'jd_core_inject_update_transient', 99);
add_filter('site_transient_update_plugins', 'jd_core_inject_update_transient', 99);

function jd_core_auto_update($update, $item){
    if (is_object($item) && !empty($item->plugin) && $item->plugin === jd_core_plugin_basename()) return true;
    return $update;
}
add_filter('auto_update_plugin', 'jd_core_auto_update', 20, 2);

/** Keep the plugin directory stable for GitHub/codeload archives. */
function jd_core_fix_update_source($source, $remote_source, $upgrader, $hook_extra){
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== jd_core_plugin_basename()) return $source;
    global $wp_filesystem;
    if (!$wp_filesystem) return $source;
    $target = trailingslashit($remote_source) . 'johndesign-core';
    if (untrailingslashit($source) === untrailingslashit($target)) return $source;
    if ($wp_filesystem->exists($target)) $wp_filesystem->delete($target, true);
    if ($wp_filesystem->move($source, $target, true)) return trailingslashit($target);
    return $source;
}
add_filter('upgrader_source_selection', 'jd_core_fix_update_source', 10, 4);

function jd_core_clear_update_cache(){
    delete_site_transient('jd_core_remote_manifest_v2');
    delete_site_transient('jd_core_remote_manifest');
    delete_site_transient('jd_v2124_remote_manifest');
}
add_action('upgrader_process_complete', 'jd_core_clear_update_cache', 10, 0);

function jd_core_force_update_check(){
    if (!current_user_can('update_plugins')) wp_die('Accès refusé');
    check_admin_referer('jd_force_update_check');
    jd_core_clear_update_cache();
    delete_site_transient('update_plugins');
    jd_core_remote_manifest(true);
    if (function_exists('wp_update_plugins')) wp_update_plugins();
    wp_safe_redirect(admin_url('admin.php?page=jd-core&update_checked=1'));
    exit;
}
add_action('admin_post_jd_force_update_check', 'jd_core_force_update_check');
