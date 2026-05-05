<?php
/*
Plugin Name: Link Hover 
Description: Shows the information about the link when hovering it.
Version: 1.0
Author: Devymuse
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

// Enqueue CSS & JS
function lpt_enqueue_assets() {
    wp_enqueue_style(
        'lpt-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'lpt-script',
        plugin_dir_url(__FILE__) . 'js/script.js',
        array(),
        '1.0',
        true
    );

    // Pass AJAX URL + NONCE
    wp_localize_script('lpt-script', 'lpt_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('lpt_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'lpt_enqueue_assets');


// AJAX handler
add_action('wp_ajax_lpt_fetch', 'lpt_fetch_data');
add_action('wp_ajax_nopriv_lpt_fetch', 'lpt_fetch_data');

function lpt_fetch_data() {

    if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'lpt_nonce')) {
        wp_send_json([]);
    }

    $url = '';
    if (isset($_GET['url'])) {
        $url = esc_url_raw(wp_unslash($_GET['url']));
    }

    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        wp_send_json([]);
    }

    $parsed_url = wp_parse_url($url);
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    if (!$host || in_array($host, ['localhost', '127.0.0.1'])) {
        wp_send_json([]);
    }
    

    // Fetch external page
    $response = wp_remote_get($url, ['timeout' => 5]);

    if (is_wp_error($response)) {
        wp_send_json([]);
    }

    $html = wp_remote_retrieve_body($response);

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $xpath = new DOMXPath($doc);

    $get_meta = function($property) use ($xpath) {
        $tags = $xpath->query("//meta[@property='$property']");
        return $tags->length ? sanitize_text_field($tags->item(0)->getAttribute('content')) : '';
    };

    $title = $get_meta('og:title');
    $desc  = $get_meta('og:description');
    $image = $get_meta('og:image');

    // Fallback title
    if (!$title) {
        $titles = $doc->getElementsByTagName('title');
        $title = $titles->length ? sanitize_text_field($titles->item(0)->nodeValue) : '';
    }

    wp_send_json([
        'title' => $title,
        'desc'  => $desc,
        'image' => esc_url_raw($image)
    ]);
}