<?php
/**
 * wpTypeScript Theme Functions
 * 
 * @package wpTypeScript
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue scripts and styles
 */
function wptypescript_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    
    // Enqueue compiled CSS
    wp_enqueue_style(
        'wptypescript-styles',
        get_template_directory_uri() . '/assets/css/styles.css',
        array(),
        $theme_version
    );

    // Enqueue root stylesheet with theme option CSS selectors
    wp_enqueue_style(
        'wptypescript-root-style',
        get_stylesheet_uri(),
        array('wptypescript-styles'),
        $theme_version
    );

    // Enqueue compiled JavaScript
    wp_enqueue_script(
        'wptypescript-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        $theme_version,
        true
    );
    
    // Pass data to JavaScript
    wp_localize_script('wptypescript-main', 'wpTypeScriptData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wptypescript_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'wptypescript_enqueue_assets');

/**
 * Enqueue admin scripts and styles
 */
function wptypescript_admin_enqueue_assets($hook) {
    $theme_version = wp_get_theme()->get('Version');
    
    // Enqueue admin CSS on all admin pages
    wp_enqueue_style(
        'wptypescript-admin',
        get_template_directory_uri() . '/assets/css/admin.css',
        array(),
        $theme_version
    );

    if ('toplevel_page_wptypescript-options' !== $hook) {
        return;
    }
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker', false, array('jquery'));
    wp_enqueue_media();
    wp_enqueue_script(
        'wptypescript-admin-script',
        get_template_directory_uri() . '/assets/js/admin.js',
        array('wp-color-picker', 'jquery'),
        $theme_version,
        true
    );

    wp_localize_script('wptypescript-admin-script', 'wptypescriptAdminData', array(
        'restUrl' => rest_url('wptypescript/v1'),
        'restNonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('admin_enqueue_scripts', 'wptypescript_admin_enqueue_assets');

/**
 * Add type="module" to Vite-built ES module scripts
 */
function wptypescript_script_module_type(string $tag, string $handle, string $src): string {
    if (in_array($handle, ['wptypescript-main', 'wptypescript-admin-script'], true)) {
        $tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '-js"></script>' . "\n";
    }
    return $tag;
}
add_filter('script_loader_tag', 'wptypescript_script_module_type', 10, 3);

/**
 * Theme setup
 */
function wptypescript_theme_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'wptypescript'),
        'footer' => __('Footer Menu', 'wptypescript'),
    ));
    
    // Register footer widget areas
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar(array(
            'name' => sprintf(__('Footer Column %d', 'wptypescript'), $i),
            'id' => 'footer-' . $i,
            'description' => sprintf(__('Widgets in this area will be shown in footer column %d', 'wptypescript'), $i),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));
    }
    
    // Register main sidebar
    register_sidebar(array(
        'name' => __('Main Sidebar', 'wptypescript'),
        'id' => 'sidebar-1',
        'description' => __('Main sidebar widget area', 'wptypescript'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('after_setup_theme', 'wptypescript_theme_setup');

/**
 * Register custom block category
 */
add_filter('block_categories_all', function ($categories) {
    $categories[] = array(
        'slug'  => 'wptypescript',
        'title' => __('Custom Blocks', 'wptypescript'),
    );
    return $categories;
});

/**
 * Register Gutenberg blocks
 */
function wptypescript_register_blocks() {
    $theme_dir = get_template_directory();
    $blocks_dir = $theme_dir . '/blocks';

    foreach (glob($blocks_dir . '/*', GLOB_ONLYDIR) as $block_dir) {
        $block_json_path = $block_dir . '/block.json';
        if (!file_exists($block_json_path)) continue;

        $metadata = json_decode(file_get_contents($block_json_path), true);
        if (!$metadata || empty($metadata['name'])) continue;

        $block_name   = $metadata['name'];
        $block_slug   = basename($block_dir);
        $block_uri    = get_template_directory_uri() . '/blocks/' . $block_slug;
        $handle_base  = str_replace('/', '-', $block_name);

        $editor_script_handles = array();
        $editor_style_handles  = array();
        $style_handles         = array();

        // Editor script
        if (!empty($metadata['editorScript'])) {
            $rel  = str_replace('file:', '', $metadata['editorScript']);
            $path = $block_dir . '/' . $rel;
            if (file_exists($path)) {
                $handle = $handle_base . '-editor';
                wp_register_script($handle, $block_uri . '/' . $rel,
                    ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n', 'wp-components'],
                    filemtime($path), false);
                $editor_script_handles[] = $handle;
            }
        }

        // Editor style
        if (!empty($metadata['editorStyle'])) {
            $rel  = str_replace('file:', '', $metadata['editorStyle']);
            $path = $block_dir . '/' . $rel;
            if (file_exists($path)) {
                $handle = $handle_base . '-editor-style';
                wp_register_style($handle, $block_uri . '/' . $rel, [], filemtime($path));
                $editor_style_handles[] = $handle;
            }
        }

        // Front-end style
        if (!empty($metadata['style'])) {
            $rel  = str_replace('file:', '', $metadata['style']);
            $path = $block_dir . '/' . $rel;
            if (file_exists($path)) {
                $handle = $handle_base . '-front-style';
                wp_register_style($handle, $block_uri . '/' . $rel, [], filemtime($path));
                $style_handles[] = $handle;
            }
        }

        $args = array(
            'title'                   => $metadata['title'] ?? '',
            'description'             => $metadata['description'] ?? '',
            'category'                => $metadata['category'] ?? 'wptypescript',
            'icon'                    => $metadata['icon'] ?? '',
            'keywords'                => $metadata['keywords'] ?? array(),
            'textdomain'              => $metadata['textdomain'] ?? 'wptypescript',
            'attributes'              => $metadata['attributes'] ?? array(),
            'supports'                => $metadata['supports'] ?? array(),
            'editor_script_handles'   => $editor_script_handles,
            'editor_style_handles'    => $editor_style_handles,
            'style_handles'           => $style_handles,
        );

        register_block_type($block_name, $args);
    }
}
add_action('init', 'wptypescript_register_blocks');

/**
 * Capture AND clear ALL inline styles right before they are printed.
 * Hooks at both wp_print_styles (head) and wp_print_footer_scripts (footer)
 * at priority 0 to catch styles added at any point during the request.
 * Captured CSS is cached in an option and served from the dynamic endpoint,
 * so no <style id="...-inline-css"> tags appear in the HTML.
 * Theme custom CSS vars and analytics (output directly via wp_head) are preserved.
 */
function wptypescript_capture_all_inline_css() {
    $wp_styles = wp_styles();
    $all = array();

    foreach ($wp_styles->registered as $handle => $data) {
        if (!empty($data->extra['after'])) {
            $css = trim(implode("\n", $data->extra['after']));
            if ($css !== '') {
                $all[] = "/* {$handle}-inline-css */\n" . $css;
            }
            $wp_styles->registered[$handle]->extra['after'] = array();
        }
    }

    if (empty($all)) {
        return;
    }

    $joined = implode("\n\n", $all);
    $prev = get_option('wptypescript_captured_inline_css', '');
    if ($joined !== $prev) {
        update_option('wptypescript_captured_inline_css', $joined);
    }
}
add_action('wp_print_styles', 'wptypescript_capture_all_inline_css', 0);
add_action('wp_print_footer_scripts', 'wptypescript_capture_all_inline_css', 0);

/**
 * Beautify HTML output with proper indentation.
 * Skips the dynamic CSS endpoint and admin pages.
 */
function wptypescript_beautify_html() {
    if (isset($_GET['wptypescript_dynamic_css']) || is_admin()) {
        return;
    }
    ob_start('wptypescript_format_html');
}
add_action('template_redirect', 'wptypescript_beautify_html', 0);

function wptypescript_format_html($buffer) {
    $lines = explode("\n", $buffer);
    $output = '';
    $depth = 0;
    $block_tags = 'html|head|body|div|section|article|nav|header|footer|main|aside|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|form|fieldset|select|option|optgroup|figure|figcaption|details|summary|dialog|blockquote|pre';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            $output .= "\n";
            continue;
        }

        if (preg_match('/^<\//', $trimmed)) {
            $depth = max(0, $depth - 1);
        } elseif (preg_match('/^<!--/', $trimmed)) {
        } elseif (preg_match('/^<(?:' . $block_tags . ')/i', $trimmed) && !preg_match('/\/>$/', $trimmed) && !preg_match('/^<[^>]*\/>/', $trimmed)) {
            $output .= str_repeat('  ', $depth) . $trimmed . "\n";
            $depth++;
            continue;
        }

        $output .= str_repeat('  ', $depth) . $trimmed . "\n";
    }

    return $output;
}

/**
 * Enqueue development assets (only in development)
 */
function wptypescript_dev_assets() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Add Vite HMR client in development
        echo '<script type="module">
            import { createHotReloadClient } from "@vite/client";
            createHotReloadClient();
        </script>';
    }
}
add_action('wp_head', 'wptypescript_dev_assets');

/**
 * Add theme options page to admin menu
 */
function wptypescript_add_admin_menu() {
    add_menu_page(
        __('Theme Options', 'wptypescript'),
        __('Theme Options', 'wptypescript'),
        'manage_options',
        'wptypescript-options',
        'wptypescript_options_page',
        'dashicons-admin-generic',
        81
    );
}
add_action('admin_menu', 'wptypescript_add_admin_menu');

/**
 * Register theme options settings
 */
function wptypescript_register_settings() {
    register_setting('wptypescript_options', 'wptypescript_enable_analytics', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));
    
    register_setting('wptypescript_options', 'wptypescript_analytics_code', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '',
    ));
    
    $scripts_settings = array('header_css', 'header_js', 'body_css', 'body_js', 'footer_css', 'footer_js');
    foreach ($scripts_settings as $key) {
        register_setting('wptypescript_options', "wptypescript_{$key}", array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '',
        ));
    }

    // Top Header Settings
    register_setting('wptypescript_options', 'wptypescript_enable_top_header', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));

    register_setting('wptypescript_options', 'wptypescript_top_header_hide_scroll', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));

    register_setting('wptypescript_options', 'wptypescript_top_header_content', array(
        'type' => 'string',
        'sanitize_callback' => 'wp_kses_post',
        'default' => '',
    ));

    // Global Layout Settings
    register_setting('wptypescript_options', 'wptypescript_container_width', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '1200',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_layout_type', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'full-width',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_header_style', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'standard',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_footer_columns', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '1',
    ));
    
    // Colors & Fonts Settings
    register_setting('wptypescript_options', 'wptypescript_primary_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#0073aa',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_secondary_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#23282d',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_text_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#333333',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_background_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#ffffff',
    ));

    register_setting('wptypescript_options', 'wptypescript_background_image', array(
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ));

    register_setting('wptypescript_options', 'wptypescript_background_image_id', array(
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0,
    ));

    register_setting('wptypescript_options', 'wptypescript_background_image_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'cover',
    ));

    register_setting('wptypescript_options', 'wptypescript_background_overlay_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));

    register_setting('wptypescript_options', 'wptypescript_gradient_top_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));

    register_setting('wptypescript_options', 'wptypescript_gradient_center_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));

    register_setting('wptypescript_options', 'wptypescript_gradient_bottom_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));

    register_setting('wptypescript_options', 'wptypescript_gradient_opacity', array(
        'type' => 'string',
        'sanitize_callback' => 'wptypescript_sanitize_opacity',
        'default' => '0',
    ));

    register_setting('wptypescript_options', 'wptypescript_link_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#0073aa',
    ));

    register_setting('wptypescript_options', 'wptypescript_link_hover_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#005b8f',
    ));

    register_setting('wptypescript_options', 'wptypescript_link_style', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'underline',
    ));
    
    // Header Builder Settings
    register_setting('wptypescript_options', 'wptypescript_header_layout', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'standard',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_header_show_search', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));
    
    register_setting('wptypescript_options', 'wptypescript_header_social_icons', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));
    
    register_setting('wptypescript_options', 'wptypescript_header_height', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '80',
    ));
    
    // Footer Builder Settings
    register_setting('wptypescript_options', 'wptypescript_footer_layout', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'standard',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_footer_show_social', array(
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));
    
    // Typography Settings
    register_setting('wptypescript_options', 'wptypescript_h1_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '48',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_h2_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '36',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_h3_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '28',
    ));

    register_setting('wptypescript_options', 'wptypescript_h4_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '22',
    ));

    register_setting('wptypescript_options', 'wptypescript_h5_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '18',
    ));

    register_setting('wptypescript_options', 'wptypescript_h6_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '16',
    ));

    register_setting('wptypescript_options', 'wptypescript_p_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '16',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_body_size', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '16',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_line_height', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '1.6',
    ));

    // Allow admins to add custom Google Fonts (comma-separated names)
    register_setting('wptypescript_options', 'wptypescript_custom_google_fonts', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ));

    // Per-heading font family / weight / style / color / line-height (H1-H6 and P)
    $headings = array('h1','h2','h3','h4','h5','h6','p');
    foreach ($headings as $h) {
        register_setting('wptypescript_options', 'wptypescript_' . $h . '_font_family', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        register_setting('wptypescript_options', 'wptypescript_' . $h . '_font_weight', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'inherit',
        ));

        register_setting('wptypescript_options', 'wptypescript_' . $h . '_font_style', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'normal',
        ));

        register_setting('wptypescript_options', 'wptypescript_' . $h . '_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '',
        ));

        register_setting('wptypescript_options', 'wptypescript_' . $h . '_letter_spacing', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        register_setting('wptypescript_options', 'wptypescript_' . $h . '_line_height', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1.3',
        ));
    }
    
    // Primary Button Typography Settings
    register_setting('wptypescript_options', 'wptypescript_primary_button_font_family', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_font_weight', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'inherit',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_font_style', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'normal',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_letter_spacing', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_link_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_border_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_border_radius', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_font_icon', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'none',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_type', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'filled',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_background_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_hover_link_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_hover_type', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'none',
    ));
    register_setting('wptypescript_options', 'wptypescript_primary_button_hover_background_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '',
    ));
    
    // Sidebar Layout
    register_setting('wptypescript_options', 'wptypescript_default_layout', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'full-width',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_sidebar_width', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '300',
    ));
}
add_action('admin_init', 'wptypescript_register_settings');

/**
 * Register REST API endpoints for theme options
 */
function wptypescript_register_rest_routes() {
    register_rest_route('wptypescript/v1', '/save-option', array(
        'methods' => 'POST',
        'callback' => 'wptypescript_rest_save_option',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'option_name' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_key',
            ),
            'option_value' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));

    register_rest_route('wptypescript/v1', '/save-options', array(
        'methods' => 'POST',
        'callback' => 'wptypescript_rest_save_options',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => array(
            'options' => array(
                'required' => true,
                'type' => 'object',
            ),
        ),
    ));
}

function wptypescript_rest_save_option(WP_REST_Request $request) {
    $option_name = $request->get_param('option_name');
    $option_value = $request->get_param('option_value');

    if (!wptypescript_is_valid_option($option_name)) {
        return new WP_Error('invalid_option', __('Invalid option name.', 'wptypescript'), array('status' => 400));
    }

    update_option($option_name, wp_unslash($option_value));

    return new WP_REST_Response(array(
        'success' => true,
        'message' => sprintf(__('Option "%s" saved.', 'wptypescript'), $option_name),
    ), 200);
}

function wptypescript_rest_save_options(WP_REST_Request $request) {
    $options = $request->get_param('options');

    foreach ($options as $name => $value) {
        if (!wptypescript_is_valid_option($name)) continue;
        update_option($name, wp_unslash(sanitize_text_field($value)));
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Options saved.', 'wptypescript'),
    ), 200);
}

function wptypescript_is_valid_option(string $name): bool {
    if (strpos($name, 'wptypescript_') !== 0) return false;
    $registered = array_keys(wp_load_alloptions());
    return in_array($name, $registered, true) || in_array($name, array(
        'wptypescript_enable_analytics',
        'wptypescript_analytics_code',
    ), true);
}
add_action('rest_api_init', 'wptypescript_register_rest_routes');

/**
 * Theme options page callback
 */
function wptypescript_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <!-- Tab Navigation -->
        <div class="wptypescript-tabs">
            <button class="tab-button active" data-tab="analytics"><?php _e('Analytics', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="scripts"><?php _e('Scripts', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="global-layout"><?php _e('Global Layout', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="colors-fonts"><?php _e('Colors', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="header-builder"><?php _e('Header Builder', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="typography"><?php _e('Typography', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="sidebar-layout"><?php _e('Sidebar Layout', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="top-header"><?php _e('Top Header', 'wptypescript'); ?></button>
        </div>
        
        <form id="wptypescript-options-form" action="options.php" method="post">
            <?php
            settings_fields('wptypescript_options');
            do_settings_sections('wptypescript_options');
            ?>
            
            <div class="wptypescript-options-container">
                <!-- Analytics Settings -->
                <div class="tab-content active" id="analytics">
                <div class="card">
                    <h2><?php _e('Analytics', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_enable_analytics"><?php _e('Enable Analytics', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="wptypescript_enable_analytics" 
                                       name="wptypescript_enable_analytics" 
                                       value="1" 
                                       <?php checked(get_option('wptypescript_enable_analytics', false), true); ?>>
                                <label for="wptypescript_enable_analytics"><?php _e('Enable analytics tracking', 'wptypescript'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_analytics_code"><?php _e('Analytics Code', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_analytics_code" 
                                          name="wptypescript_analytics_code" 
                                          rows="10" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_analytics_code', '')); ?></textarea>
                                <p class="description"><?php _e('Paste your analytics tracking code here (e.g., Google Analytics)', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>

                <!-- Scripts Settings (Header / Body / Footer) -->
                <div class="tab-content" id="scripts">
                <div class="card">
                    <h2><?php _e('Scripts', 'wptypescript'); ?></h2>
                    <p class="description"><?php _e('Add custom CSS and JavaScript to different sections of your site. CSS is wrapped in &lt;style&gt; tags, JavaScript in &lt;script&gt; tags.', 'wptypescript'); ?></p>
                    
                    <h3 style="margin-top:24px"><?php _e('Header (&lt;head&gt;)', 'wptypescript'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_css"><?php _e('CSS', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_header_css" 
                                          name="wptypescript_header_css" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_header_css', '')); ?></textarea>
                                <p class="description"><?php _e('Custom CSS added to &lt;head&gt;. Do not include &lt;style&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_js"><?php _e('JavaScript', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_header_js" 
                                          name="wptypescript_header_js" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_header_js', '')); ?></textarea>
                                <p class="description"><?php _e('Custom JavaScript added to &lt;head&gt;. Do not include &lt;script&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <h3 style="margin-top:24px"><?php _e('Body (after &lt;body&gt;)', 'wptypescript'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_body_css"><?php _e('CSS', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_body_css" 
                                          name="wptypescript_body_css" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_body_css', '')); ?></textarea>
                                <p class="description"><?php _e('Custom CSS added after &lt;body&gt;. Do not include &lt;style&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_body_js"><?php _e('JavaScript', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_body_js" 
                                          name="wptypescript_body_js" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_body_js', '')); ?></textarea>
                                <p class="description"><?php _e('Custom JavaScript added after &lt;body&gt;. Do not include &lt;script&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <h3 style="margin-top:24px"><?php _e('Footer (before &lt;/body&gt;)', 'wptypescript'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_footer_css"><?php _e('CSS', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_footer_css" 
                                          name="wptypescript_footer_css" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_footer_css', '')); ?></textarea>
                                <p class="description"><?php _e('Custom CSS added before &lt;/body&gt;. Do not include &lt;style&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_footer_js"><?php _e('JavaScript', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_footer_js" 
                                          name="wptypescript_footer_js" 
                                          rows="5" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_footer_js', '')); ?></textarea>
                                <p class="description"><?php _e('Custom JavaScript added before &lt;/body&gt;. Do not include &lt;script&gt; tags.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>

                <!-- Global Layout Settings -->
                <div class="tab-content" id="global-layout">
                <div class="card">
                    <h2><?php _e('Global Layout', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_container_width"><?php _e('Container Width (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_container_width" 
                                       name="wptypescript_container_width" 
                                       value="<?php echo esc_attr(get_option('wptypescript_container_width', '1200')); ?>" 
                                       class="small-text"
                                       min="800"
                                       max="1600"
                                       step="10">
                                <p class="description"><?php _e('Maximum width of the content container (800-1600px)', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_layout_type"><?php _e('Layout Type', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_layout_type" name="wptypescript_layout_type">
                                    <option value="full-width" <?php selected(get_option('wptypescript_layout_type', 'full-width'), 'full-width'); ?>>
                                        <?php _e('Full Width', 'wptypescript'); ?>
                                    </option>
                                    <option value="boxed" <?php selected(get_option('wptypescript_layout_type', 'full-width'), 'boxed'); ?>>
                                        <?php _e('Boxed', 'wptypescript'); ?>
                                    </option>
                                    <option value="wide" <?php selected(get_option('wptypescript_layout_type', 'full-width'), 'wide'); ?>>
                                        <?php _e('Wide', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Choose the overall layout style', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_style"><?php _e('Header Style', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_header_style" name="wptypescript_header_style">
                                    <option value="standard" <?php selected(get_option('wptypescript_header_style', 'standard'), 'standard'); ?>>
                                        <?php _e('Standard', 'wptypescript'); ?>
                                    </option>
                                    <option value="sticky" <?php selected(get_option('wptypescript_header_style', 'standard'), 'sticky'); ?>>
                                        <?php _e('Sticky', 'wptypescript'); ?>
                                    </option>
                                    <option value="transparent" <?php selected(get_option('wptypescript_header_style', 'standard'), 'transparent'); ?>>
                                        <?php _e('Transparent', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Choose the header behavior style', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_footer_columns"><?php _e('Footer Columns', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_footer_columns" name="wptypescript_footer_columns">
                                    <option value="1" <?php selected(get_option('wptypescript_footer_columns', '1'), '1'); ?>>
                                        <?php _e('1 Column', 'wptypescript'); ?>
                                    </option>
                                    <option value="2" <?php selected(get_option('wptypescript_footer_columns', '1'), '2'); ?>>
                                        <?php _e('2 Columns', 'wptypescript'); ?>
                                    </option>
                                    <option value="3" <?php selected(get_option('wptypescript_footer_columns', '1'), '3'); ?>>
                                        <?php _e('3 Columns', 'wptypescript'); ?>
                                    </option>
                                    <option value="4" <?php selected(get_option('wptypescript_footer_columns', '1'), '4'); ?>>
                                        <?php _e('4 Columns', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Number of columns in the footer', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
                
                <!-- Colors Settings -->
                <div class="tab-content" id="colors-fonts">
                <div class="card">
                    <h2><?php _e('Colors', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_primary_color"><?php _e('Primary Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color" 
                                       id="wptypescript_primary_color" 
                                       name="wptypescript_primary_color" 
                                       value="<?php echo esc_attr(get_option('wptypescript_primary_color', '#0073aa')); ?>" 
                                       class="color-picker">
                                <p class="description"><?php _e('Main accent color for buttons, links, and highlights', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_secondary_color"><?php _e('Secondary Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color" 
                                       id="wptypescript_secondary_color" 
                                       name="wptypescript_secondary_color" 
                                       value="<?php echo esc_attr(get_option('wptypescript_secondary_color', '#23282d')); ?>" 
                                       class="color-picker">
                                <p class="description"><?php _e('Secondary color for footer and dark elements', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_text_color"><?php _e('Text Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color" 
                                       id="wptypescript_text_color" 
                                       name="wptypescript_text_color" 
                                       value="<?php echo esc_attr(get_option('wptypescript_text_color', '#333333')); ?>" 
                                       class="color-picker">
                                <p class="description"><?php _e('Main text color for body content', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_background_color"><?php _e('Background Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color" 
                                       id="wptypescript_background_color" 
                                       name="wptypescript_background_color" 
                                       value="<?php echo esc_attr(get_option('wptypescript_background_color', '#ffffff')); ?>" 
                                       class="color-picker">
                                <p class="description"><?php _e('Main background color for the site', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_background_image"><?php _e('Background Image', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <input type="text"
                                               id="wptypescript_background_image"
                                               name="wptypescript_background_image"
                                               value="<?php echo esc_attr(get_option('wptypescript_background_image', '')); ?>"
                                               class="regular-text"
                                               placeholder="https://example.com/image.jpg">
                                        <input type="hidden"
                                               id="wptypescript_background_image_id"
                                               name="wptypescript_background_image_id"
                                               value="<?php echo esc_attr(get_option('wptypescript_background_image_id', '')); ?>">
                                    </div>
                                    <button type="button" id="wptypescript_background_image_button" class="button button-primary" style="margin-top: 0;"><?php _e('Upload Image', 'wptypescript'); ?></button>
                                </div>
                                <p class="description"><?php _e('Enter the URL or upload an image. You can enter the URL manually or use the Upload Image button to select from the media library.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_background_image_size"><?php _e('Background Image Style', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_background_image_size" name="wptypescript_background_image_size">
                                    <option value="cover" <?php selected(get_option('wptypescript_background_image_size', 'cover'), 'cover'); ?>><?php _e('Cover', 'wptypescript'); ?></option>
                                    <option value="contain" <?php selected(get_option('wptypescript_background_image_size', 'cover'), 'contain'); ?>><?php _e('Contain', 'wptypescript'); ?></option>
                                    <option value="auto" <?php selected(get_option('wptypescript_background_image_size', 'cover'), 'auto'); ?>><?php _e('Auto', 'wptypescript'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose how the background image is sized.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_background_overlay_color"><?php _e('Background Overlay Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color"
                                       id="wptypescript_background_overlay_color"
                                       name="wptypescript_background_overlay_color"
                                       value="<?php echo esc_attr(get_option('wptypescript_background_overlay_color', '')); ?>"
                                       class="color-picker">
                                <p class="description"><?php _e('Optional overlay color for the background image.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Gradient Colors', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <p class="description" style="margin-bottom: 12px;"><?php _e('Create an optional gradient overlay on top of the background.', 'wptypescript'); ?></p>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                                    <div>
                                        <label for="wptypescript_gradient_top_color" style="display: block; margin-bottom: 4px; font-weight: 500;"><?php _e('Top Color', 'wptypescript'); ?></label>
                                        <input type="color"
                                               id="wptypescript_gradient_top_color"
                                               name="wptypescript_gradient_top_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_gradient_top_color', '')); ?>"
                                               class="color-picker"
                                               style="width: 100%; height: 40px; cursor: pointer;">
                                    </div>
                                    <div>
                                        <label for="wptypescript_gradient_center_color" style="display: block; margin-bottom: 4px; font-weight: 500;"><?php _e('Center Color', 'wptypescript'); ?></label>
                                        <input type="color"
                                               id="wptypescript_gradient_center_color"
                                               name="wptypescript_gradient_center_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_gradient_center_color', '')); ?>"
                                               class="color-picker"
                                               style="width: 100%; height: 40px; cursor: pointer;">
                                    </div>
                                    <div>
                                        <label for="wptypescript_gradient_bottom_color" style="display: block; margin-bottom: 4px; font-weight: 500;"><?php _e('Bottom Color', 'wptypescript'); ?></label>
                                        <input type="color"
                                               id="wptypescript_gradient_bottom_color"
                                               name="wptypescript_gradient_bottom_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_gradient_bottom_color', '')); ?>"
                                               class="color-picker"
                                               style="width: 100%; height: 40px; cursor: pointer;">
                                    </div>
                                </div>
                                <div style="margin-top: 12px; max-width: 280px;">
                                    <label for="wptypescript_gradient_opacity" style="display: block; margin-bottom: 4px; font-weight: 500;"><?php _e('Gradient Opacity', 'wptypescript'); ?></label>
                                    <input type="number"
                                           id="wptypescript_gradient_opacity"
                                           name="wptypescript_gradient_opacity"
                                           value="<?php echo esc_attr(get_option('wptypescript_gradient_opacity', '0')); ?>"
                                           class="small-text"
                                           min="0"
                                           max="1"
                                           step="0.05">
                                    <p class="description"><?php _e('Opacity of the gradient overlay (0 = transparent, 1 = fully opaque).', 'wptypescript'); ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php _e('Background Preview', 'wptypescript'); ?>
                            </th>
                            <td>
                                <?php
                                $bg_image = esc_attr(get_option('wptypescript_background_image', ''));
                                $bg_size = esc_attr(get_option('wptypescript_background_image_size', 'cover'));
                                $bg_overlay = esc_attr(get_option('wptypescript_background_overlay_color', ''));
                                $bg_color = esc_attr(get_option('wptypescript_background_color', '#ffffff'));
                                $grad_top = esc_attr(get_option('wptypescript_gradient_top_color', ''));
                                $grad_center = esc_attr(get_option('wptypescript_gradient_center_color', ''));
                                $grad_bottom = esc_attr(get_option('wptypescript_gradient_bottom_color', ''));
                                $grad_opacity = esc_attr(get_option('wptypescript_gradient_opacity', '0'));
                                
                                $gradient_css = '';
                                if ($grad_top || $grad_center || $grad_bottom) {
                                    $stops = array();
                                    if ($grad_top) $stops[] = $grad_top . ' 0%';
                                    if ($grad_center) $stops[] = $grad_center . ' 50%';
                                    if ($grad_bottom) $stops[] = $grad_bottom . ' 100%';
                                    if (!empty($stops)) {
                                        $gradient_css = 'linear-gradient(to bottom, ' . implode(', ', $stops) . ')';
                                    }
                                }
                                ?>
                                <div class="wptypescript-background-preview-wrap" style="position: relative; width: 100%; height: 200px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background-color: <?php echo $bg_color; ?>;">
                                    <?php if ($bg_image) : ?>
                                        <div style="position: absolute; inset: 0; background-image: url(<?php echo $bg_image; ?>); background-size: <?php echo $bg_size; ?>; background-repeat: no-repeat; background-position: center center;"></div>
                                    <?php endif; ?>
                                    <?php if ($bg_overlay) : ?>
                                        <div style="position: absolute; inset: 0; background-color: <?php echo $bg_overlay; ?>; opacity: 0.7;"></div>
                                    <?php endif; ?>
                                    <?php if ($gradient_css) : ?>
                                        <div class="wptypescript-gradient-preview" style="position: absolute; inset: 0; background: <?php echo $gradient_css; ?>; opacity: <?php echo $grad_opacity; ?>;"></div>
                                    <?php endif; ?>
                                </div>
                                <p class="description" style="margin-top: 8px;"><?php _e('Live preview of your selected background image, overlay, and gradient.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_link_color"><?php _e('Content Link Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color"
                                       id="wptypescript_link_color"
                                       name="wptypescript_link_color"
                                       value="<?php echo esc_attr(get_option('wptypescript_link_color', '#0073aa')); ?>"
                                       class="color-picker">
                                <p class="description"><?php _e('Color used for content links throughout the site.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_link_style"><?php _e('Content Link Style', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_link_style" name="wptypescript_link_style">
                                    <option value="underline" <?php selected(get_option('wptypescript_link_style', 'underline'), 'underline'); ?>><?php _e('Underline', 'wptypescript'); ?></option>
                                    <option value="none" <?php selected(get_option('wptypescript_link_style', 'underline'), 'none'); ?>><?php _e('No Underline', 'wptypescript'); ?></option>
                                    <option value="overline" <?php selected(get_option('wptypescript_link_style', 'underline'), 'overline'); ?>><?php _e('Overline', 'wptypescript'); ?></option>
                                    <option value="underline overline" <?php selected(get_option('wptypescript_link_style', 'underline'), 'underline overline'); ?>><?php _e('Underline + Overline', 'wptypescript'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose the default decoration style for content links.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_link_hover_color"><?php _e('Content Link Hover Color', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="color"
                                       id="wptypescript_link_hover_color"
                                       name="wptypescript_link_hover_color"
                                       value="<?php echo esc_attr(get_option('wptypescript_link_hover_color', '#005b8f')); ?>"
                                       class="color-picker">
                                <p class="description"><?php _e('Hover color for content links.', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php _e('Link Preview', 'wptypescript'); ?>
                            </th>
                            <td>
                                <?php
                                $preview_link_color = esc_attr(get_option('wptypescript_link_color', '#0073aa'));
                                $preview_link_hover_color = esc_attr(get_option('wptypescript_link_hover_color', '#005b8f'));
                                $preview_link_style = esc_attr(get_option('wptypescript_link_style', 'underline'));
                                ?>
                                <div class="wptypescript-link-preview-wrap">
                                    <a href="#" onclick="return false;" class="wptypescript-link-preview" style="color: <?php echo $preview_link_color; ?>; text-decoration: <?php echo $preview_link_style; ?>; --link-hover-color: <?php echo $preview_link_hover_color; ?>;">
                                        <?php _e('This is a sample link', 'wptypescript'); ?>
                                    </a>
                                    <p class="description"><?php _e('Hover over the sample link to preview the selected colors and decoration.', 'wptypescript'); ?></p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
                
                <!-- Header Builder Settings -->
                <div class="tab-content" id="header-builder">
                <div class="card">
                    <h2><?php _e('Header Builder', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_layout"><?php _e('Header Layout', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_header_layout" name="wptypescript_header_layout">
                                    <option value="standard" <?php selected(get_option('wptypescript_header_layout', 'standard'), 'standard'); ?>>
                                        <?php _e('Standard', 'wptypescript'); ?>
                                    </option>
                                    <option value="centered" <?php selected(get_option('wptypescript_header_layout', 'standard'), 'centered'); ?>>
                                        <?php _e('Centered', 'wptypescript'); ?>
                                    </option>
                                    <option value="inline" <?php selected(get_option('wptypescript_header_layout', 'standard'), 'inline'); ?>>
                                        <?php _e('Inline Logo & Menu', 'wptypescript'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_height"><?php _e('Header Height (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_header_height" 
                                       name="wptypescript_header_height" 
                                       value="<?php echo esc_attr(get_option('wptypescript_header_height', '80')); ?>" 
                                       class="small-text"
                                       min="50"
                                       max="200"
                                       step="5">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_show_search"><?php _e('Show Search', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="wptypescript_header_show_search" 
                                       name="wptypescript_header_show_search" 
                                       value="1" 
                                       <?php checked(get_option('wptypescript_header_show_search', false), true); ?>>
                                <label for="wptypescript_header_show_search"><?php _e('Display search icon in header', 'wptypescript'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_header_social_icons"><?php _e('Show Social Icons', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="wptypescript_header_social_icons" 
                                       name="wptypescript_header_social_icons" 
                                       value="1" 
                                       <?php checked(get_option('wptypescript_header_social_icons', false), true); ?>>
                                <label for="wptypescript_header_social_icons"><?php _e('Display social icons in header', 'wptypescript'); ?></label>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
                
                <!-- Typography Settings -->
                <div class="tab-content" id="typography">
                <div class="card">
                    <h2><?php _e('Typography', 'wptypescript'); ?></h2>
                    <table class="form-table">

                        <tr>
                            <th scope="row">
                                <label for="wptypescript_custom_google_fonts"><?php _e('Custom Google Fonts', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="wptypescript_custom_google_fonts"
                                       name="wptypescript_custom_google_fonts"
                                       value="<?php echo esc_attr(get_option('wptypescript_custom_google_fonts', '')); ?>"
                                       class="regular-text"
                                       placeholder="e.g. Inter, Fira+Sans">
                                <p class="description"><?php _e('Enter one or more Google Font family names, comma-separated. Use + for spaces where required (e.g. Inter, Fira+Sans). These fonts will be enqueued automatically.', 'wptypescript'); ?></p>
                            </td>
                        </tr>

                        <!-- Per-heading controls for H1-H5 (compact grid) -->
                        <?php
                        $heading_font_options = array(
                            "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif" => __('System Sans-Serif', 'wptypescript'),
                            "'Georgia', 'Times New Roman', Times, serif" => __('Georgia / Times', 'wptypescript'),
                            "'Arial', 'Helvetica Neue', Helvetica, sans-serif" => __('Arial / Helvetica', 'wptypescript'),
                            "'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif" => __('Trebuchet MS', 'wptypescript'),
                            "'Verdana', Geneva, sans-serif" => __('Verdana', 'wptypescript'),
                            "'Courier New', Courier, monospace" => __('Courier New', 'wptypescript'),
                            "'Roboto', sans-serif" => __('Roboto (Google Font)', 'wptypescript'),
                            "'Open Sans', sans-serif" => __('Open Sans (Google Font)', 'wptypescript'),
                            "'Lato', sans-serif" => __('Lato (Google Font)', 'wptypescript'),
                            "'Montserrat', sans-serif" => __('Montserrat (Google Font)', 'wptypescript'),
                            "'Poppins', sans-serif" => __('Poppins (Google Font)', 'wptypescript'),
                        );

                        $weights = array('inherit','100','200','300','400','500','600','700','800','900');
                        $styles = array('normal','italic','oblique');

                        $controls = array('h1','h2','h3','h4','h5','h6','p');
                        $defaults = array(
                            'h1' => '48',
                            'h2' => '36',
                            'h3' => '28',
                            'h4' => '22',
                            'h5' => '18',
                            'h6' => '16',
                            'p' => get_option('wptypescript_body_size','16'),
                        );
                        $line_height_defaults = array(
                            'h1' => '1.2',
                            'h2' => '1.25',
                            'h3' => '1.3',
                            'h4' => '1.35',
                            'h5' => '1.4',
                            'h6' => '1.4',
                            'p'  => '1.6',
                        );

                        foreach ($controls as $h) :
                        ?>
                        <tr>
                            <th scope="row">
                                <label><?php echo strtoupper($h); ?> <?php _e('', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <div class="wptypescript-heading-grid">
                                    <div class="field family">
                                        <label for="wptypescript_<?php echo $h; ?>_font_family"><?php _e('Family', 'wptypescript'); ?></label>
                                        <select id="wptypescript_<?php echo $h; ?>_font_family" name="wptypescript_<?php echo $h; ?>_font_family">
                                            <?php foreach ($heading_font_options as $val => $label) : ?>
                                                <option value="<?php echo esc_attr($val); ?>" <?php selected(get_option('wptypescript_' . $h . '_font_family', "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"), $val); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="field weight">
                                        <label for="wptypescript_<?php echo $h; ?>_font_weight"><?php _e('Weight', 'wptypescript'); ?></label>
                                        <select id="wptypescript_<?php echo $h; ?>_font_weight" name="wptypescript_<?php echo $h; ?>_font_weight">
                                            <?php foreach ($weights as $w) : ?>
                                                <option value="<?php echo $w; ?>" <?php selected(get_option('wptypescript_' . $h . '_font_weight', 'inherit'), $w); ?>>
                                                    <?php echo esc_html($w); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="field style">
                                        <label for="wptypescript_<?php echo $h; ?>_font_style"><?php _e('Style', 'wptypescript'); ?></label>
                                        <select id="wptypescript_<?php echo $h; ?>_font_style" name="wptypescript_<?php echo $h; ?>_font_style">
                                            <?php foreach ($styles as $s) : ?>
                                                <option value="<?php echo esc_attr($s); ?>" <?php selected(get_option('wptypescript_' . $h . '_font_style', 'normal'), $s); ?>>
                                                    <?php echo esc_html(ucfirst($s)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="field color">
                                        <label for="wptypescript_<?php echo $h; ?>_color"><?php _e('Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_<?php echo $h; ?>_color"
                                               name="wptypescript_<?php echo $h; ?>_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_' . $h . '_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#333333">
                                    </div>

                                    <div class="field letter-spacing">
                                        <label for="wptypescript_<?php echo $h; ?>_letter_spacing"><?php _e('Letter Spacing (px)', 'wptypescript'); ?></label>
                                        <input type="number"
                                               id="wptypescript_<?php echo $h; ?>_letter_spacing"
                                               name="wptypescript_<?php echo $h; ?>_letter_spacing"
                                               value="<?php echo esc_attr(get_option('wptypescript_' . $h . '_letter_spacing', '')); ?>"
                                               class="small-text"
                                               min="-5"
                                               max="20"
                                               step="0.1"
                                               placeholder="0">
                                    </div>

                                    <div class="field size">
                                        <label for="wptypescript_<?php echo $h; ?>_size"><?php _e('Size (px)', 'wptypescript'); ?></label>
                                        <input type="number"
                                               id="wptypescript_<?php echo $h; ?>_size"
                                               name="wptypescript_<?php echo $h; ?>_size"
                                               value="<?php echo esc_attr(get_option('wptypescript_' . $h . '_size', $defaults[$h])); ?>"
                                               class="small-text"
                                               min="12"
                                               max="200"
                                               step="1">
                                    </div>

                                    <div class="field line-height">
                                        <label for="wptypescript_<?php echo $h; ?>_line_height"><?php _e('Line Height', 'wptypescript'); ?></label>
                                        <input type="number"
                                               id="wptypescript_<?php echo $h; ?>_line_height"
                                               name="wptypescript_<?php echo $h; ?>_line_height"
                                               value="<?php echo esc_attr(get_option('wptypescript_' . $h . '_line_height', $line_height_defaults[$h])); ?>"
                                               class="small-text"
                                               min="1"
                                               max="3"
                                               step="0.05">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Primary Button Typography -->
                        <tr>
                            <th scope="row">
                                <label><?php _e('Primary Button', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <div class="wptypescript-heading-grid">
                                    <div class="field family">
                                        <label for="wptypescript_primary_button_font_family"><?php _e('Family', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_font_family" name="wptypescript_primary_button_font_family">
                                            <?php foreach ($heading_font_options as $val => $label) : ?>
                                                <option value="<?php echo esc_attr($val); ?>" <?php selected(get_option('wptypescript_primary_button_font_family', ''), $val); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field weight">
                                        <label for="wptypescript_primary_button_font_weight"><?php _e('Weight', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_font_weight" name="wptypescript_primary_button_font_weight">
                                            <?php foreach ($weights as $w) : ?>
                                                <option value="<?php echo $w; ?>" <?php selected(get_option('wptypescript_primary_button_font_weight', 'inherit'), $w); ?>>
                                                    <?php echo esc_html($w); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field style">
                                        <label for="wptypescript_primary_button_font_style"><?php _e('Style', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_font_style" name="wptypescript_primary_button_font_style">
                                            <?php foreach ($styles as $s) : ?>
                                                <option value="<?php echo esc_attr($s); ?>" <?php selected(get_option('wptypescript_primary_button_font_style', 'normal'), $s); ?>>
                                                    <?php echo esc_html(ucfirst($s)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field letter-spacing">
                                        <label for="wptypescript_primary_button_letter_spacing"><?php _e('Letter Spacing (px)', 'wptypescript'); ?></label>
                                        <input type="number"
                                               id="wptypescript_primary_button_letter_spacing"
                                               name="wptypescript_primary_button_letter_spacing"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_letter_spacing', '')); ?>"
                                               class="small-text"
                                               min="-5" max="20" step="0.1" placeholder="0">
                                    </div>
                                    <div class="field color">
                                        <label for="wptypescript_primary_button_link_color"><?php _e('Link Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_primary_button_link_color"
                                               name="wptypescript_primary_button_link_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_link_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#ffffff">
                                    </div>
                                    <div class="field border-color">
                                        <label for="wptypescript_primary_button_border_color"><?php _e('Border Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_primary_button_border_color"
                                               name="wptypescript_primary_button_border_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_border_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#0073aa">
                                    </div>
                                    <div class="field border-radius">
                                        <label for="wptypescript_primary_button_border_radius"><?php _e('Border Radius (px)', 'wptypescript'); ?></label>
                                        <input type="number"
                                               id="wptypescript_primary_button_border_radius"
                                               name="wptypescript_primary_button_border_radius"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_border_radius', '0')); ?>"
                                               class="small-text"
                                               min="0" max="50" step="1" placeholder="0">
                                    </div>
                                    <div class="field background-color">
                                        <label for="wptypescript_primary_button_background_color"><?php _e('Background Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_primary_button_background_color"
                                               name="wptypescript_primary_button_background_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_background_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#0073aa">
                                    </div>
                                    <div class="field font-icon">
                                        <label for="wptypescript_primary_button_font_icon"><?php _e('Font Icon', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_font_icon" name="wptypescript_primary_button_font_icon">
                                            <option value="none" <?php selected(get_option('wptypescript_primary_button_font_icon', 'none'), 'none'); ?>><?php _e('None', 'wptypescript'); ?></option>
                                            <option value="dashicons" <?php selected(get_option('wptypescript_primary_button_font_icon', 'none'), 'dashicons'); ?>><?php _e('Dashicons', 'wptypescript'); ?></option>
                                            <option value="fontawesome" <?php selected(get_option('wptypescript_primary_button_font_icon', 'none'), 'fontawesome'); ?>><?php _e('Font Awesome', 'wptypescript'); ?></option>
                                            <option value="material" <?php selected(get_option('wptypescript_primary_button_font_icon', 'none'), 'material'); ?>><?php _e('Material Icons', 'wptypescript'); ?></option>
                                        </select>
                                    </div>
                                    <div class="field button-type">
                                        <label for="wptypescript_primary_button_type"><?php _e('Button Type', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_type" name="wptypescript_primary_button_type">
                                            <option value="filled" <?php selected(get_option('wptypescript_primary_button_type', 'filled'), 'filled'); ?>><?php _e('Filled', 'wptypescript'); ?></option>
                                            <option value="outline" <?php selected(get_option('wptypescript_primary_button_type', 'filled'), 'outline'); ?>><?php _e('Outline', 'wptypescript'); ?></option>
                                            <option value="text" <?php selected(get_option('wptypescript_primary_button_type', 'filled'), 'text'); ?>><?php _e('Text Only', 'wptypescript'); ?></option>
                                            <option value="3d" <?php selected(get_option('wptypescript_primary_button_type', 'filled'), '3d'); ?>><?php _e('3D', 'wptypescript'); ?></option>
                                        </select>
                                    </div>
                                    <div class="field hover-link-color">
                                        <label for="wptypescript_primary_button_hover_link_color"><?php _e('Hover Link Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_primary_button_hover_link_color"
                                               name="wptypescript_primary_button_hover_link_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_hover_link_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#ffffff">
                                    </div>
                                    <div class="field hover-type">
                                        <label for="wptypescript_primary_button_hover_type"><?php _e('Hover Type', 'wptypescript'); ?></label>
                                        <select id="wptypescript_primary_button_hover_type" name="wptypescript_primary_button_hover_type">
                                            <option value="none" <?php selected(get_option('wptypescript_primary_button_hover_type', 'none'), 'none'); ?>><?php _e('None', 'wptypescript'); ?></option>
                                            <option value="darken" <?php selected(get_option('wptypescript_primary_button_hover_type', 'none'), 'darken'); ?>><?php _e('Darken', 'wptypescript'); ?></option>
                                            <option value="lighten" <?php selected(get_option('wptypescript_primary_button_hover_type', 'none'), 'lighten'); ?>><?php _e('Lighten', 'wptypescript'); ?></option>
                                            <option value="underline" <?php selected(get_option('wptypescript_primary_button_hover_type', 'none'), 'underline'); ?>><?php _e('Underline', 'wptypescript'); ?></option>
                                        </select>
                                    </div>
                                    <div class="field hover-background-color">
                                        <label for="wptypescript_primary_button_hover_background_color"><?php _e('Hover Background Color', 'wptypescript'); ?></label>
                                        <input type="text"
                                               id="wptypescript_primary_button_hover_background_color"
                                               name="wptypescript_primary_button_hover_background_color"
                                               value="<?php echo esc_attr(get_option('wptypescript_primary_button_hover_background_color', '')); ?>"
                                               class="regular-text color-picker"
                                               placeholder="#005b8f">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_body_size"><?php _e('Body Font Size (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_body_size" 
                                       name="wptypescript_body_size" 
                                       value="<?php echo esc_attr(get_option('wptypescript_body_size', '16')); ?>" 
                                       class="small-text"
                                       min="12"
                                       max="24"
                                       step="1">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_line_height"><?php _e('Line Height', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_line_height" 
                                       name="wptypescript_line_height" 
                                       value="<?php echo esc_attr(get_option('wptypescript_line_height', '1.6')); ?>" 
                                       class="small-text"
                                       min="1"
                                       max="3"
                                       step="0.1">
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
                
                <!-- Sidebar Layout Settings -->
                <div class="tab-content" id="sidebar-layout">
                <div class="card">
                    <h2><?php _e('Sidebar Layout', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_default_layout"><?php _e('Default Layout', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_default_layout" name="wptypescript_default_layout">
                                    <option value="full-width" <?php selected(get_option('wptypescript_default_layout', 'full-width'), 'full-width'); ?>>
                                        <?php _e('Full Width', 'wptypescript'); ?>
                                    </option>
                                    <option value="left-sidebar" <?php selected(get_option('wptypescript_default_layout', 'full-width'), 'left-sidebar'); ?>>
                                        <?php _e('Left Sidebar', 'wptypescript'); ?>
                                    </option>
                                    <option value="right-sidebar" <?php selected(get_option('wptypescript_default_layout', 'full-width'), 'right-sidebar'); ?>>
                                        <?php _e('Right Sidebar', 'wptypescript'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_sidebar_width"><?php _e('Sidebar Width (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_sidebar_width" 
                                       name="wptypescript_sidebar_width" 
                                       value="<?php echo esc_attr(get_option('wptypescript_sidebar_width', '300')); ?>" 
                                       class="small-text"
                                       min="200"
                                       max="500"
                                       step="10">
                            </td>
                        </tr>
                    </table>
                </div>
                </div>

                <!-- Top Header Settings -->
                <div class="tab-content" id="top-header">
                <div class="card">
                    <h2><?php _e('Top Header', 'wptypescript'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_enable_top_header"><?php _e('Enable Top Header', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="wptypescript_enable_top_header" 
                                       name="wptypescript_enable_top_header" 
                                       value="1" 
                                       <?php checked(get_option('wptypescript_enable_top_header', false), true); ?>>
                                <label for="wptypescript_enable_top_header"><?php _e('Show a top header bar above the main header', 'wptypescript'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_top_header_hide_scroll"><?php _e('Hide on Scroll', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="wptypescript_top_header_hide_scroll" 
                                       name="wptypescript_top_header_hide_scroll" 
                                       value="1" 
                                       <?php checked(get_option('wptypescript_top_header_hide_scroll', false), true); ?>>
                                <label for="wptypescript_top_header_hide_scroll"><?php _e('Hide the top header bar when scrolling down, show when scrolling up', 'wptypescript'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_top_header_content"><?php _e('Content', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <textarea id="wptypescript_top_header_content" 
                                          name="wptypescript_top_header_content" 
                                          rows="4" 
                                          class="large-text code"><?php echo esc_textarea(get_option('wptypescript_top_header_content', '')); ?></textarea>
                                <p class="description"><?php _e('HTML content for the top header bar (text, links, phone, etc.)', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
            </div>
            
            <?php submit_button(__('Save Settings', 'wptypescript')); ?>
        </form>
    </div>
    

    <?php
}

/**
 * Output analytics code in head
 */
function wptypescript_analytics_code() {
    if (get_option('wptypescript_enable_analytics', false)) {
        $analytics_code = get_option('wptypescript_analytics_code', '');
        if (!empty($analytics_code)) {
            echo $analytics_code;
        }
    }
}
add_action('wp_head', 'wptypescript_analytics_code');

/**
 * Output top header bar if enabled
 */
function wptypescript_top_header() {
    if (!get_option('wptypescript_enable_top_header', false)) {
        return;
    }
    $content = get_option('wptypescript_top_header_content', '');
    $hide_scroll = get_option('wptypescript_top_header_hide_scroll', false) ? ' data-hide-scroll="true"' : '';
    ?>
    <div class="top-header-bar"<?php echo $hide_scroll; ?>>
        <div class="container">
            <?php if (!empty($content)) : ?>
                <div class="top-header-content"><?php echo wp_kses_post($content); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Output header CSS & JS in head (after analytics)
 */
function wptypescript_header_css() {
    $val = get_option('wptypescript_header_css', '');
    if (!empty($val)) {
        echo '<style id="wptypescript-header-css">' . $val . '</style>';
    }
}
add_action('wp_head', 'wptypescript_header_css', 11);

function wptypescript_header_js() {
    $val = get_option('wptypescript_header_js', '');
    if (!empty($val)) {
        echo '<script>' . $val . '</script>';
    }
}
add_action('wp_head', 'wptypescript_header_js', 12);

/**
 * Output body CSS & JS after opening <body> tag
 */
function wptypescript_body_css() {
    $val = get_option('wptypescript_body_css', '');
    if (!empty($val)) {
        echo '<style id="wptypescript-body-css">' . $val . '</style>';
    }
}
add_action('wp_body_open', 'wptypescript_body_css', 9);

function wptypescript_body_js() {
    $val = get_option('wptypescript_body_js', '');
    if (!empty($val)) {
        echo '<script>' . $val . '</script>';
    }
}
add_action('wp_body_open', 'wptypescript_body_js', 10);

/**
 * Output footer CSS & JS before closing </body> tag
 */
function wptypescript_footer_css() {
    $val = get_option('wptypescript_footer_css', '');
    if (!empty($val)) {
        echo '<style id="wptypescript-footer-css">' . $val . '</style>';
    }
}
add_action('wp_footer', 'wptypescript_footer_css', 99);

function wptypescript_footer_js() {
    $val = get_option('wptypescript_footer_js', '');
    if (!empty($val)) {
        echo '<script>' . $val . '</script>';
    }
}
add_action('wp_footer', 'wptypescript_footer_js', 100);

/**
 * Sanitize a CSS custom property value safely for inline style output.
 */
function wptypescript_sanitize_css_value($value) {
    $value = wp_strip_all_tags($value);
    $value = str_replace(array("\r", "\n"), '', $value);
    return trim($value);
}

/**
 * Sanitize a CSS opacity value (0–1).
 */
function wptypescript_sanitize_opacity($value) {
    $value = floatval($value);

    if ($value < 0) {
        $value = 0;
    } elseif ($value > 1) {
        $value = 1;
    }

    return (string) $value;
}

/**
 * Return layout CSS vars as a string
 */
function wptypescript_get_layout_styles_css() {
    $container_width = get_option('wptypescript_container_width', '1200');
    $primary_color = get_option('wptypescript_primary_color', '#0073aa');
    $secondary_color = get_option('wptypescript_secondary_color', '#23282d');
    $text_color = get_option('wptypescript_text_color', '#333333');
    $background_color = get_option('wptypescript_background_color', '#ffffff');
    $background_image = get_option('wptypescript_background_image', '');
    $background_image_size = get_option('wptypescript_background_image_size', 'cover');
    $background_overlay_color = get_option('wptypescript_background_overlay_color', '');
    $gradient_top_color = get_option('wptypescript_gradient_top_color', '');
    $gradient_center_color = get_option('wptypescript_gradient_center_color', '');
    $gradient_bottom_color = get_option('wptypescript_gradient_bottom_color', '');
    $gradient_opacity = get_option('wptypescript_gradient_opacity', '0');
    $link_color = get_option('wptypescript_link_color', '#0073aa');
    $link_hover_color = get_option('wptypescript_link_hover_color', '#005b8f');
    $link_style = get_option('wptypescript_link_style', 'underline');
    $default_body_font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
    $default_heading_font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
    $h1_size = get_option('wptypescript_h1_size', '48');
    $h2_size = get_option('wptypescript_h2_size', '36');
    $h3_size = get_option('wptypescript_h3_size', '28');
    $h4_size = get_option('wptypescript_h4_size', '24');
    $h5_size = get_option('wptypescript_h5_size', '20');
    $h6_size = get_option('wptypescript_h6_size', '18');
    $body_size = get_option('wptypescript_body_size', '16');
    $line_height = get_option('wptypescript_line_height', '1.6');
    $h1_font = trim(get_option('wptypescript_h1_font_family', '')) ?: $default_heading_font;
    $h2_font = trim(get_option('wptypescript_h2_font_family', '')) ?: $default_heading_font;
    $h3_font = trim(get_option('wptypescript_h3_font_family', '')) ?: $default_heading_font;
    $h4_font = trim(get_option('wptypescript_h4_font_family', '')) ?: $default_heading_font;
    $h5_font = trim(get_option('wptypescript_h5_font_family', '')) ?: $default_heading_font;
    $h6_font = trim(get_option('wptypescript_h6_font_family', '')) ?: $default_heading_font;
    $p_font  = trim(get_option('wptypescript_p_font_family', '')) ?: $default_body_font;
    $p_color = trim(get_option('wptypescript_p_color', '')) ?: $text_color;

    $props = array(
        'container-width' => $container_width . 'px',
        'primary-color' => $primary_color,
        'secondary-color' => $secondary_color,
        'text-color' => $text_color,
        'background-color' => $background_color,
        'link-color' => $link_color,
        'link-hover-color' => $link_hover_color,
        'link-style' => $link_style,
        'background-image' => $background_image ? 'url(' . esc_url($background_image) . ')' : 'none',
        'background-image-size' => $background_image_size,
        'background-overlay-color' => $background_overlay_color ?: 'transparent',
        'gradient-top-color' => $gradient_top_color ?: 'transparent',
        'gradient-center-color' => $gradient_center_color ?: 'transparent',
        'gradient-bottom-color' => $gradient_bottom_color ?: 'transparent',
        'gradient-opacity' => $gradient_opacity,
        'body-font' => $p_font,
        'heading-font' => $h1_font,
        'h1-font' => $h1_font,
        'h2-font' => $h2_font,
        'h3-font' => $h3_font,
        'h4-font' => $h4_font,
        'h5-font' => $h5_font,
        'h6-font' => $h6_font,
        'p-font' => $p_font,
        'h1-color' => get_option('wptypescript_h1_color', ''),
        'h2-color' => get_option('wptypescript_h2_color', ''),
        'h3-color' => get_option('wptypescript_h3_color', ''),
        'h4-color' => get_option('wptypescript_h4_color', ''),
        'h5-color' => get_option('wptypescript_h5_color', ''),
        'h6-color' => get_option('wptypescript_h6_color', ''),
        'p-color' => $p_color,
        'h1-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h1_letter_spacing', '')),
        'h2-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h2_letter_spacing', '')),
        'h3-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h3_letter_spacing', '')),
        'h4-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h4_letter_spacing', '')),
        'h5-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h5_letter_spacing', '')),
        'h6-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_h6_letter_spacing', '')),
        'p-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_p_letter_spacing', '')),
        'h1-size' => $h1_size . 'px',
        'h2-size' => $h2_size . 'px',
        'h3-size' => $h3_size . 'px',
        'h4-size' => $h4_size . 'px',
        'h5-size' => $h5_size . 'px',
        'h6-size' => get_option('wptypescript_h6_size', '16') . 'px',
        'p-size' => get_option('wptypescript_p_size', '16') . 'px',
        'body-size' => $body_size . 'px',
        'line-height' => $line_height,
        'h1-line-height' => get_option('wptypescript_h1_line_height', '1.2'),
        'h2-line-height' => get_option('wptypescript_h2_line_height', '1.25'),
        'h3-line-height' => get_option('wptypescript_h3_line_height', '1.3'),
        'h4-line-height' => get_option('wptypescript_h4_line_height', '1.35'),
        'h5-line-height' => get_option('wptypescript_h5_line_height', '1.4'),
        'h6-line-height' => get_option('wptypescript_h6_line_height', '1.4'),
        'p-line-height' => get_option('wptypescript_p_line_height', '1.6'),
        'header-height' => get_option('wptypescript_header_height', '80') . 'px',
        'sidebar-width' => get_option('wptypescript_sidebar_width', '300') . 'px',
        'h1-weight' => get_option('wptypescript_h1_font_weight', 'inherit'),
        'h2-weight' => get_option('wptypescript_h2_font_weight', 'inherit'),
        'h3-weight' => get_option('wptypescript_h3_font_weight', 'inherit'),
        'h4-weight' => get_option('wptypescript_h4_font_weight', 'inherit'),
        'h5-weight' => get_option('wptypescript_h5_font_weight', 'inherit'),
        'p-weight' => get_option('wptypescript_p_font_weight', 'inherit'),
        'h1-style' => get_option('wptypescript_h1_font_style', 'normal'),
        'h2-style' => get_option('wptypescript_h2_font_style', 'normal'),
        'h3-style' => get_option('wptypescript_h3_font_style', 'normal'),
        'h4-style' => get_option('wptypescript_h4_font_style', 'normal'),
        'h5-style' => get_option('wptypescript_h5_font_style', 'normal'),
        'p-style' => get_option('wptypescript_p_font_style', 'normal'),
        'primary-button-font' => wptypescript_sanitize_css_value(trim(get_option('wptypescript_primary_button_font_family', '')) ?: $p_font),
        'primary-button-weight' => get_option('wptypescript_primary_button_font_weight', 'inherit'),
        'primary-button-style' => get_option('wptypescript_primary_button_font_style', 'normal'),
        'primary-button-letter-spacing' => wptypescript_css_spacing(get_option('wptypescript_primary_button_letter_spacing', '')),
        'primary-button-link-color' => get_option('wptypescript_primary_button_link_color', ''),
        'primary-button-border-color' => get_option('wptypescript_primary_button_border_color', ''),
        'primary-button-border-radius' => get_option('wptypescript_primary_button_border_radius', '0') . 'px',
        'primary-button-font-icon' => get_option('wptypescript_primary_button_font_icon', 'none'),
        'primary-button-type' => get_option('wptypescript_primary_button_type', 'filled'),
        'primary-button-background-color' => get_option('wptypescript_primary_button_background_color', ''),
        'primary-button-hover-link-color' => get_option('wptypescript_primary_button_hover_link_color', ''),
        'primary-button-hover-type' => get_option('wptypescript_primary_button_hover_type', 'none'),
        'primary-button-hover-background-color' => get_option('wptypescript_primary_button_hover_background_color', ''),
    );

    $css = ":root {\n";
    foreach ($props as $name => $value) {
        $css .= "  --{$name}: {$value};\n";
    }
    $css .= "}\n";
    return $css;
}

/**
 * Return conditional layout CSS as a string
 */
function wptypescript_get_layout_conditionals_css() {
    $header_layout = get_option('wptypescript_header_layout', 'standard');
    $default_layout = get_option('wptypescript_default_layout', 'full-width');
    $layout_type = get_option('wptypescript_layout_type', 'full-width');
    $header_style = get_option('wptypescript_header_style', 'standard');

    $css = '';
    if ($header_layout === 'centered') {
        $css .= ".site-header .container { text-align: center; }\n";
        $css .= ".site-header .primary-navigation { justify-content: center; }\n";
    } elseif ($header_layout === 'inline') {
        $css .= ".site-header .container { display: flex; align-items: center; justify-content: space-between; }\n";
        $css .= ".site-header .site-title { margin: 0; }\n";
    }
    if ($default_layout === 'left-sidebar') {
        $css .= ".site-content { display: grid; grid-template-columns: var(--sidebar-width) 1fr; gap: 2rem; }\n";
    } elseif ($default_layout === 'right-sidebar') {
        $css .= ".site-content { display: grid; grid-template-columns: 1fr var(--sidebar-width); gap: 2rem; }\n";
    }
    if ($layout_type === 'boxed') {
        $css .= "body { background-color: #f0f0f0; }\n";
        $css .= ".site-wrapper { max-width: var(--container-width); margin: 0 auto; background-color: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1); }\n";
    } elseif ($layout_type === 'wide') {
        $css .= ".site-content { max-width: 100%; padding: 0; }\n";
    }
    if ($header_style === 'sticky') {
        $css .= ".site-header { position: sticky; top: 0; z-index: 1000; }\n";
    } elseif ($header_style === 'transparent') {
        $css .= ".site-header { background-color: transparent; position: absolute; top: 0; left: 0; right: 0; z-index: 1000; }\n";
    }
    return $css;
}

/**
 * Helper: format letter-spacing value
 */
function wptypescript_css_spacing($val) {
    return trim($val) !== '' ? $val . 'px' : 'normal';
}

/**
 * Return block library CSS as a string
 */
function wptypescript_get_block_library_css() {
    return ':root {
  --wp-block-synced-color: #7a00df;
  --wp-block-synced-color--rgb: 122,0,223;
  --wp-bound-block-color: var(--wp-block-synced-color);
  --wp-editor-canvas-background: #ddd;
  --wp-admin-theme-color: #007cba;
  --wp-admin-theme-color--rgb: 0,124,186;
  --wp-admin-theme-color-darker-10: #006ba1;
  --wp-admin-theme-color-darker-10--rgb: 0,107,160.5;
  --wp-admin-theme-color-darker-20: #005a87;
  --wp-admin-theme-color-darker-20--rgb: 0,90,135;
  --wp-admin-border-width-focus: 2px;
  --wp--preset--font-size--normal: 16px;
  --wp--preset--font-size--huge: 42px;
}
.wp-element-button { cursor: pointer; }
.has-very-light-gray-background-color { background-color: #eee; }
.has-very-dark-gray-background-color { background-color: #313131; }
.has-very-light-gray-color { color: #eee; }
.has-very-dark-gray-color { color: #313131; }
.has-vivid-green-cyan-to-vivid-cyan-blue-gradient-background { background: linear-gradient(135deg,#00d084,#0693e3); }
.has-purple-crush-gradient-background { background: linear-gradient(135deg,#34e2e4,#4721fb 50%,#ab1dfe); }
.has-hazy-dawn-gradient-background { background: linear-gradient(135deg,#faaca8,#dad0ec); }
.has-subdued-olive-gradient-background { background: linear-gradient(135deg,#fafae1,#67a671); }
.has-atomic-cream-gradient-background { background: linear-gradient(135deg,#fdd79a,#004a59); }
.has-nightshade-gradient-background { background: linear-gradient(135deg,#330968,#31cdcf); }
.has-midnight-gradient-background { background: linear-gradient(135deg,#020381,#2874fc); }
.has-regular-font-size { font-size: 1em; }
.has-larger-font-size { font-size: 2.625em; }
.has-normal-font-size { font-size: var(--wp--preset--font-size--normal); }
.has-huge-font-size { font-size: var(--wp--preset--font-size--huge); }
.has-text-align-center { text-align: center; }
.has-text-align-left { text-align: left; }
.has-text-align-right { text-align: right; }
.has-fit-text { white-space: nowrap !important; }
.aligncenter { clear: both; }
.items-justified-left { justify-content: flex-start; }
.items-justified-center { justify-content: center; }
.items-justified-right { justify-content: flex-end; }
.items-justified-space-between { justify-content: space-between; }
.screen-reader-text { word-wrap: normal !important; border: 0; clip-path: inset(50%); height: 1px; margin: -1px; overflow: hidden; padding: 0; position: absolute; width: 1px; }
.screen-reader-text:focus { background-color: #ddd; clip-path: none; color: #444; display: block; font-size: 1em; height: auto; left: 5px; line-height: normal; padding: 15px 23px 14px; text-decoration: none; top: 5px; width: auto; z-index: 100000; }
html :where(.has-border-color) { border-style: solid; }
html :where([style*=border-top-color]) { border-top-style: solid; }
html :where([style*=border-right-color]) { border-right-style: solid; }
html :where([style*=border-bottom-color]) { border-bottom-style: solid; }
html :where([style*=border-left-color]) { border-left-style: solid; }
html :where([style*=border-color]) { border-style: solid; }
html :where([style*=border-width]) { border-style: solid; }
html :where([style*=border-top-width]) { border-top-style: solid; }
html :where([style*=border-right-width]) { border-right-style: solid; }
html :where([style*=border-bottom-width]) { border-bottom-style: solid; }
html :where([style*=border-left-width]) { border-left-style: solid; }
html :where(img[class*=wp-image-]) { height: auto; max-width: 100%; }
:where(figure) { margin: 0 0 1em; }
html :where(.is-position-sticky) { --wp-admin--admin-bar--position-offset: var(--wp-admin--admin-bar--height, 0px); }
@media (min-resolution:192dpi) { :root { --wp-admin-border-width-focus: 1.5px; } }
@media screen and (max-width:600px) { html :where(.is-position-sticky) { --wp-admin--admin-bar--position-offset: 0px; } }
';
}

/**
 * Return any cached inline CSS captured from late-registered handles.
 */
function wptypescript_get_captured_inline_css() {
    return get_option('wptypescript_captured_inline_css', '');
}

/**
 * Serve all former inline CSS as a single linked stylesheet
 */
function wptypescript_serve_dynamic_css() {
    if (isset($_GET['wptypescript_dynamic_css'])) {
        header('Content-Type: text/css');
        echo wptypescript_get_block_library_css();
        echo "\n";
        echo wptypescript_get_layout_styles_css();
        echo "\n";
        echo wptypescript_get_layout_conditionals_css();
        $captured = wptypescript_get_captured_inline_css();
        if ($captured !== '') {
            echo "\n" . $captured . "\n";
        }
        exit;
    }
}
add_action('template_redirect', 'wptypescript_serve_dynamic_css');

/**
 * Enqueue the dynamic CSS stylesheet (replaces previous inline <style> tags)
 */
function wptypescript_enqueue_dynamic_css() {
    $theme_version = wp_get_theme()->get('Version');
    wp_enqueue_style(
        'wptypescript-dynamic',
        home_url('/?wptypescript_dynamic_css=1'),
        array('wptypescript-styles', 'wptypescript-root-style'),
        $theme_version
    );
}
add_action('wp_enqueue_scripts', 'wptypescript_enqueue_dynamic_css', 200);

/**
 * Enqueue Google Fonts for any selected font options
 */
function wptypescript_enqueue_google_fonts() {
    $known_google_fonts = array(
        'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins', 'Oswald', 'Raleway', 'Merriweather', 'Source Sans Pro', 'Nunito'
    );

    $font_weights = array();
    $family_data = array(
        array(
            'font' => get_option('wptypescript_h1_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h1_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h2_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h2_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h3_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h3_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h2_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h2_font_weight', get_option('wptypescript_body_font_weight', 'inherit'))),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h3_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h3_font_weight', get_option('wptypescript_body_font_weight', 'inherit'))),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h4_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h4_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_h5_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_h5_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_p_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_p_font_weight', 'inherit')),
            'google' => true,
        ),
        array(
            'font' => get_option('wptypescript_primary_button_font_family', ''),
            'weights' => wptypescript_parse_font_weight(get_option('wptypescript_primary_button_font_weight', 'inherit')),
            'google' => true,
        ),
    );

    foreach ($family_data as $data) {
        $name = wptypescript_extract_local_font_family($data['font']);
        if (empty($name)) {
            continue;
        }

        if ($data['google'] && !wptypescript_is_known_google_font($name, $known_google_fonts)) {
            continue;
        }

        if (!isset($font_weights[$name])) {
            $font_weights[$name] = array();
        }

        $font_weights[$name] = array_merge($font_weights[$name], $data['weights']);
    }

    $custom = get_option('wptypescript_custom_google_fonts', '');
    if (!empty($custom)) {
        $parts = array_map('trim', explode(',', $custom));
        foreach ($parts as $part) {
            $name = trim($part, "'\" ");
            if (empty($name)) {
                continue;
            }
            if (!isset($font_weights[$name])) {
                $font_weights[$name] = array();
            }
            $font_weights[$name] = array_merge($font_weights[$name], array('400'));
        }
    }

    if (empty($font_weights)) {
        return;
    }

    $families = array();
    foreach ($font_weights as $font => $weights) {
        $weights = array_filter(array_unique($weights), 'is_numeric');
        if (empty($weights)) {
            $weights = array('400');
        }

        sort($weights, SORT_NUMERIC);
        $family_name = str_replace(' ', '+', $font);
        $families[] = $family_name . ':wght@' . implode(';', $weights);
    }

    $href = 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
    wp_enqueue_style('wptypescript-google-fonts', esc_url_raw($href), array(), null);
}

function wptypescript_parse_font_weight($weight) {
    if (empty($weight) || $weight === 'inherit') {
        return array();
    }

    return is_numeric($weight) ? array($weight) : array();
}

function wptypescript_extract_local_font_family($font_family) {
    if (empty($font_family)) {
        return '';
    }

    $font_family = trim($font_family);
    if (strpos($font_family, ',') !== false) {
        $font_family = trim(substr($font_family, 0, strpos($font_family, ',')));
    }

    return trim($font_family, "'\" ");
}

function wptypescript_is_known_google_font($font_name, $known_fonts) {
    foreach ($known_fonts as $known) {
        if (strcasecmp($font_name, $known) === 0) {
            return true;
        }
    }

    return false;
}
add_action('wp_enqueue_scripts', 'wptypescript_enqueue_google_fonts');
