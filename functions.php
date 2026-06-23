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
}
add_action('admin_init', 'wptypescript_register_settings');

/**
 * Theme options page callback
 */
function wptypescript_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wptypescript_options');
            do_settings_sections('wptypescript_options');
            ?>
            
            <div class="wptypescript-options-container">
                <!-- Analytics Settings -->
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
                
                <!-- Global Layout Settings -->
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
            
            <?php submit_button(__('Save Settings', 'wptypescript')); ?>
        </form>
    </div>
    
    <style>
        .wptypescript-options-container {
            max-width: 800px;
            margin-top: 20px;
        }
        .wptypescript-options-container .card {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccd0d4;
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .wptypescript-options-container .card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
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
    
    ?>
    <style>
        :root {
            --container-width: <?php echo esc_attr($container_width); ?>px;
        }
        
        .container {
            max-width: var(--container-width);
        }
        
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
