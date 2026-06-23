<?php
/**
 * Header template
 * 
 * @package wpTypeScript
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">
<header class="site-header">
    <div class="container">
        <?php if (has_custom_logo()) : ?>
            <div class="site-logo">
                <?php the_custom_logo(); ?>
            </div>
        <?php else : ?>
            <h1 class="site-title">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <?php bloginfo('name'); ?>
                </a>
            </h1>
        <?php endif; ?>
        
        <button class="mobile-menu-toggle" aria-label="<?php _e('Toggle menu', 'wptypescript'); ?>">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <?php if (has_nav_menu('primary')) : ?>
            <nav class="primary-navigation" role="navigation" aria-label="<?php _e('Primary Menu', 'wptypescript'); ?>">
                <?php wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'primary-menu',
                    'container' => false,
                )); ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
