<?php
/**
 * Footer template
 * 
 * @package wpTypeScript
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<?php
$footer_columns = get_option('wptypescript_footer_columns', '1');
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid footer-<?php echo esc_attr($footer_columns); ?>-columns">
            <?php for ($i = 1; $i <= $footer_columns; $i++) : ?>
                <div class="footer-column">
                    <?php if (is_active_sidebar('footer-' . $i)) : ?>
                        <?php dynamic_sidebar('footer-' . $i); ?>
                    <?php else : ?>
                        <h3><?php printf(__('Footer Column %d', 'wptypescript'), $i); ?></h3>
                        <p><?php _e('Add widgets to this footer column in Appearance > Widgets', 'wptypescript'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        
        <div class="footer-bottom">
            <?php if (has_nav_menu('footer')) : ?>
                <nav class="footer-navigation" role="navigation" aria-label="<?php _e('Footer Menu', 'wptypescript'); ?>">
                    <?php wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class' => 'footer-menu',
                        'container' => false,
                    )); ?>
                </nav>
            <?php endif; ?>
            
            <p class="copyright">
                <?php
                printf(
                    __('&copy; %1$s %2$s. All rights reserved.', 'wptypescript'),
                    date_i18n('Y'),
                    get_bloginfo('name')
                );
                ?>
            </p>
        </div>
    </div>
</footer>
</div><!-- .site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
