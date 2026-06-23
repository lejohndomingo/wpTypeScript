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
    if ('toplevel_page_wptypescript-options' !== $hook) {
        return;
    }
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker', array('jquery'));
}
add_action('admin_enqueue_scripts', 'wptypescript_admin_enqueue_assets');

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
    
    register_setting('wptypescript_options', 'wptypescript_heading_font', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_body_font', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
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
    
    register_setting('wptypescript_options', 'wptypescript_heading_font_weight', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '700',
    ));
    
    register_setting('wptypescript_options', 'wptypescript_heading_font_style', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'normal',
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
 * Theme options page callback
 */
function wptypescript_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <!-- Tab Navigation -->
        <div class="wptypescript-tabs">
            <button class="tab-button active" data-tab="analytics"><?php _e('Analytics', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="global-layout"><?php _e('Global Layout', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="colors-fonts"><?php _e('Colors', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="header-builder"><?php _e('Header Builder', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="typography"><?php _e('Typography', 'wptypescript'); ?></button>
            <button class="tab-button" data-tab="sidebar-layout"><?php _e('Sidebar Layout', 'wptypescript'); ?></button>
        </div>
        
        <form action="options.php" method="post">
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
                                <label for="wptypescript_heading_font"><?php _e('Heading Font Family', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_heading_font" name="wptypescript_heading_font">
                                    <option value="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif" <?php selected(get_option('wptypescript_heading_font'), '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif'); ?>>
                                        <?php _e('System Sans-Serif', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Georgia', 'Times New Roman', Times, serif" <?php selected(get_option('wptypescript_heading_font'), "'Georgia', 'Times New Roman', Times, serif"); ?>>
                                        <?php _e('Georgia / Times', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Arial', 'Helvetica Neue', Helvetica, sans-serif" <?php selected(get_option('wptypescript_heading_font'), "'Arial', 'Helvetica Neue', Helvetica, sans-serif"); ?>>
                                        <?php _e('Arial / Helvetica', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif" <?php selected(get_option('wptypescript_heading_font'), "'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif"); ?>>
                                        <?php _e('Trebuchet MS', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Verdana', Geneva, sans-serif" <?php selected(get_option('wptypescript_heading_font'), "'Verdana', Geneva, sans-serif"); ?>>
                                        <?php _e('Verdana', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Courier New', Courier, monospace" <?php selected(get_option('wptypescript_heading_font'), "'Courier New', Courier, monospace"); ?>>
                                        <?php _e('Courier New', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Font family for headings (h1-h6)', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_heading_font_weight"><?php _e('Heading Font Weight', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_heading_font_weight" name="wptypescript_heading_font_weight">
                                    <option value="100" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '100'); ?>>
                                        <?php _e('100 - Thin', 'wptypescript'); ?>
                                    </option>
                                    <option value="200" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '200'); ?>>
                                        <?php _e('200 - Extra Light', 'wptypescript'); ?>
                                    </option>
                                    <option value="300" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '300'); ?>>
                                        <?php _e('300 - Light', 'wptypescript'); ?>
                                    </option>
                                    <option value="400" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '400'); ?>>
                                        <?php _e('400 - Normal', 'wptypescript'); ?>
                                    </option>
                                    <option value="500" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '500'); ?>>
                                        <?php _e('500 - Medium', 'wptypescript'); ?>
                                    </option>
                                    <option value="600" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '600'); ?>>
                                        <?php _e('600 - Semi Bold', 'wptypescript'); ?>
                                    </option>
                                    <option value="700" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '700'); ?>>
                                        <?php _e('700 - Bold', 'wptypescript'); ?>
                                    </option>
                                    <option value="800" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '800'); ?>>
                                        <?php _e('800 - Extra Bold', 'wptypescript'); ?>
                                    </option>
                                    <option value="900" <?php selected(get_option('wptypescript_heading_font_weight', '700'), '900'); ?>>
                                        <?php _e('900 - Black', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Font weight for headings', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_heading_font_style"><?php _e('Heading Font Style', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_heading_font_style" name="wptypescript_heading_font_style">
                                    <option value="normal" <?php selected(get_option('wptypescript_heading_font_style', 'normal'), 'normal'); ?>>
                                        <?php _e('Normal', 'wptypescript'); ?>
                                    </option>
                                    <option value="italic" <?php selected(get_option('wptypescript_heading_font_style', 'normal'), 'italic'); ?>>
                                        <?php _e('Italic', 'wptypescript'); ?>
                                    </option>
                                    <option value="oblique" <?php selected(get_option('wptypescript_heading_font_style', 'normal'), 'oblique'); ?>>
                                        <?php _e('Oblique', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Font style for headings', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_body_font"><?php _e('Body Font', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <select id="wptypescript_body_font" name="wptypescript_body_font">
                                    <option value="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif" <?php selected(get_option('wptypescript_body_font'), '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif'); ?>>
                                        <?php _e('System Sans-Serif', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Georgia', 'Times New Roman', Times, serif" <?php selected(get_option('wptypescript_body_font'), "'Georgia', 'Times New Roman', Times, serif"); ?>>
                                        <?php _e('Georgia / Times', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Arial', 'Helvetica Neue', Helvetica, sans-serif" <?php selected(get_option('wptypescript_body_font'), "'Arial', 'Helvetica Neue', Helvetica, sans-serif"); ?>>
                                        <?php _e('Arial / Helvetica', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif" <?php selected(get_option('wptypescript_body_font'), "'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif"); ?>>
                                        <?php _e('Trebuchet MS', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Verdana', Geneva, sans-serif" <?php selected(get_option('wptypescript_body_font'), "'Verdana', Geneva, sans-serif"); ?>>
                                        <?php _e('Verdana', 'wptypescript'); ?>
                                    </option>
                                    <option value="'Courier New', Courier, monospace" <?php selected(get_option('wptypescript_body_font'), "'Courier New', Courier, monospace"); ?>>
                                        <?php _e('Courier New', 'wptypescript'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Font family for body text', 'wptypescript'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_h1_size"><?php _e('H1 Font Size (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_h1_size" 
                                       name="wptypescript_h1_size" 
                                       value="<?php echo esc_attr(get_option('wptypescript_h1_size', '48')); ?>" 
                                       class="small-text"
                                       min="20"
                                       max="100"
                                       step="1">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_h2_size"><?php _e('H2 Font Size (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_h2_size" 
                                       name="wptypescript_h2_size" 
                                       value="<?php echo esc_attr(get_option('wptypescript_h2_size', '36')); ?>" 
                                       class="small-text"
                                       min="18"
                                       max="80"
                                       step="1">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wptypescript_h3_size"><?php _e('H3 Font Size (px)', 'wptypescript'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="wptypescript_h3_size" 
                                       name="wptypescript_h3_size" 
                                       value="<?php echo esc_attr(get_option('wptypescript_h3_size', '28')); ?>" 
                                       class="small-text"
                                       min="16"
                                       max="60"
                                       step="1">
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
            </div>
            
            <?php submit_button(__('Save Settings', 'wptypescript')); ?>
        </form>
    </div>
    
    <style>
        .wptypescript-tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 13px;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .tab-button:hover {
            color: #0073aa;
            border-bottom-color: #0073aa;
        }
        
        .tab-button.active {
            color: #0073aa;
            border-bottom-color: #0073aa;
            font-weight: 600;
        }
        
        .wptypescript-options-container {
            max-width: 1200px;
            font-size: 13px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .wptypescript-options-container .card {
            padding: 20px;
            border: 1px solid #ccd0d4;
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            height: fit-content;
        }
        
        .wptypescript-options-container .card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 1.1rem;
        }
        
        .wptypescript-options-container label {
            font-size: 13px;
        }
        
        .wptypescript-options-container .description {
            font-size: 12px;
        }
        
        .color-picker {
            width: 60px;
            height: 40px;
            padding: 0;
            border: 1px solid #ccc;
            cursor: pointer;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        $('.color-picker').wpColorPicker();
        
        // Tab switching functionality
        $('.tab-button').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all buttons
            $('.tab-button').removeClass('active');
            
            // Add active class to clicked button
            $(this).addClass('active');
            
            // Hide all tab content
            $('.tab-content').removeClass('active');
            
            // Show selected tab content
            var tabId = $(this).data('tab');
            $('#' + tabId).addClass('active');
        });
    });
    </script>
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
 * Output global layout styles
 */
function wptypescript_layout_styles() {
    $container_width = get_option('wptypescript_container_width', '1200');
    $layout_type = get_option('wptypescript_layout_type', 'full-width');
    $header_style = get_option('wptypescript_header_style', 'standard');
    
    // Colors & Fonts
    $primary_color = get_option('wptypescript_primary_color', '#0073aa');
    $secondary_color = get_option('wptypescript_secondary_color', '#23282d');
    $text_color = get_option('wptypescript_text_color', '#333333');
    $background_color = get_option('wptypescript_background_color', '#ffffff');
    $heading_font = get_option('wptypescript_heading_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif');
    $body_font = get_option('wptypescript_body_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif');
    
    // Typography
    $h1_size = get_option('wptypescript_h1_size', '48');
    $h2_size = get_option('wptypescript_h2_size', '36');
    $h3_size = get_option('wptypescript_h3_size', '28');
    $body_size = get_option('wptypescript_body_size', '16');
    $line_height = get_option('wptypescript_line_height', '1.6');
    $heading_font_weight = get_option('wptypescript_heading_font_weight', '700');
    $heading_font_style = get_option('wptypescript_heading_font_style', 'normal');
    
    // Header
    $header_layout = get_option('wptypescript_header_layout', 'standard');
    $header_height = get_option('wptypescript_header_height', '80');
    
    // Sidebar
    $default_layout = get_option('wptypescript_default_layout', 'full-width');
    $sidebar_width = get_option('wptypescript_sidebar_width', '300');
    
    ?>
    <style>
        :root {
            --container-width: <?php echo esc_attr($container_width); ?>px;
            --primary-color: <?php echo esc_attr($primary_color); ?>;
            --secondary-color: <?php echo esc_attr($secondary_color); ?>;
            --text-color: <?php echo esc_attr($text_color); ?>;
            --background-color: <?php echo esc_attr($background_color); ?>;
            --heading-font: <?php echo esc_attr($heading_font); ?>;
            --body-font: <?php echo esc_attr($body_font); ?>;
            --h1-size: <?php echo esc_attr($h1_size); ?>px;
            --h2-size: <?php echo esc_attr($h2_size); ?>px;
            --h3-size: <?php echo esc_attr($h3_size); ?>px;
            --body-size: <?php echo esc_attr($body_size); ?>px;
            --line-height: <?php echo esc_attr($line_height); ?>;
            --header-height: <?php echo esc_attr($header_height); ?>px;
            --sidebar-width: <?php echo esc_attr($sidebar_width); ?>px;
            --heading-font-weight: <?php echo esc_attr($heading_font_weight); ?>;
            --heading-font-style: <?php echo esc_attr($heading_font_style); ?>;
        }
        
        .container {
            max-width: var(--container-width);
        }
        
        body {
            color: var(--text-color);
            background-color: var(--background-color);
            font-family: var(--body-font);
            font-size: var(--body-size);
            line-height: var(--line-height);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--heading-font);
        }
        
        h1 {
            font-size: var(--h1-size);
        }
        
        h2 {
            font-size: var(--h2-size);
        }
        
        h3 {
            font-size: var(--h3-size);
        }
        
        .site-header {
            background-color: var(--primary-color);
            min-height: var(--header-height);
        }
        
        <?php if ($header_layout === 'centered') : ?>
        .site-header .container {
            text-align: center;
        }
        
        .site-header .primary-navigation {
            justify-content: center;
        }
        <?php elseif ($header_layout === 'inline') : ?>
        .site-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .site-header .site-title {
            margin: 0;
        }
        <?php endif; ?>
        
        .site-footer {
            background-color: var(--secondary-color);
        }
        
        <?php if ($default_layout === 'left-sidebar') : ?>
        .site-content {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            gap: 2rem;
        }
        <?php elseif ($default_layout === 'right-sidebar') : ?>
        .site-content {
            display: grid;
            grid-template-columns: 1fr var(--sidebar-width);
            gap: 2rem;
        }
        <?php endif; ?>
        
        <?php if ($layout_type === 'boxed') : ?>
        body {
            background-color: #f0f0f0;
        }
        
        .site-wrapper {
            max-width: var(--container-width);
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        <?php elseif ($layout_type === 'wide') : ?>
        .site-content {
            max-width: 100%;
            padding: 0;
        }
        <?php endif; ?>
        
        <?php if ($header_style === 'sticky') : ?>
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        <?php elseif ($header_style === 'transparent') : ?>
        .site-header {
            background-color: transparent;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        <?php endif; ?>
    </style>
    <?php
}
add_action('wp_head', 'wptypescript_layout_styles');
