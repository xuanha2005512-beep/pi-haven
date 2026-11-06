
<?php
/**
 * Pi Haven Theme - Setup
 */

if (!defined('ABSPATH')) {
    exit;
}

function pihaven_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'pihaven_theme_setup');

function pihaven_enqueue_styles() {
    wp_enqueue_style(
        'pihaven-style',
        get_stylesheet_uri(),
        array(),
        '0.1.0'
    );
}

add_action('wp_enqueue_scripts', 'pihaven_enqueue_styles');
