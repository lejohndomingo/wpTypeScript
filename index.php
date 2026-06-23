<?php
/**
 * Main template file
 * 
 * @package wpTypeScript
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();
?>

<?php
$default_layout = get_option('wptypescript_default_layout', 'full-width');
?>

<?php if ($default_layout === 'left-sidebar' && is_active_sidebar('sidebar-1')) : ?>
<aside class="site-sidebar">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
<?php endif; ?>

<main class="site-content">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
        
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p><?php _e('No posts found.', 'wptypescript'); ?></p>
    <?php endif; ?>
</main>

<?php if ($default_layout === 'right-sidebar' && is_active_sidebar('sidebar-1')) : ?>
<aside class="site-sidebar">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
<?php endif; ?>

<?php
get_footer();
