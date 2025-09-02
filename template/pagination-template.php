<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// File: pagination-template.php
// Use $query if available (AJAX), otherwise fall back to $GLOBALS['pagimore_loop']
$wp_query = isset($query) ? $query : (isset($GLOBALS['pagimore_loop']) ? $GLOBALS['pagimore_loop'] : null);
if (!$wp_query) {
    return; // Exit if no query is available
}
$total_pages = $wp_query->max_num_pages;


 $current_page = get_query_var('paged') ? get_query_var('paged') : max(1, isset($_POST['page']) ? intval($_POST['page']) : (get_query_var('paged') ? get_query_var('paged') : 1));
    if (get_query_var('page')) {
        $current_page = get_query_var('page');
    }
 
 $ppp = isset($_POST['per_page']) ? intval($_POST['per_page']) : (isset($wp_query->query_vars['posts_per_page']) ? $wp_query->query_vars['posts_per_page'] : get_option('pagimore_posts_per_page', 5));
// Get settings
$enable_pagination = get_option('pagimore_enable_pagination', 1);
$enable_load_more = get_option('pagimore_enable_load_more', 1);
$enable_pagination_mobile = get_option('pagimore_enable_pagination_mobile', 1);
$enable_load_more_mobile = get_option('pagimore_enable_load_more_mobile', 1);
$enable_pagination_pc = get_option('pagimore_enable_pagination_pc', 1);
$enable_load_more_pc = get_option('pagimore_enable_load_more_pc', 1);
$pagimore_preloader_text = get_option('pagimore_preloader_text', 'Loading...');
// Determine device type (basic check using wp_is_mobile())
$is_mobile = wp_is_mobile();
$show_pagination = $enable_pagination && ($is_mobile ? $enable_pagination_mobile : $enable_pagination_pc);
$show_load_more = $enable_load_more && ($is_mobile ? $enable_load_more_mobile : $enable_load_more_pc);
    $prev_icon = esc_url(get_option('pagimore_prev_arrow_icon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg'));
    $next_icon = esc_url(get_option('pagimore_next_arrow_icon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg'));
if (!$show_pagination && !$show_load_more) {
    return; // Exit if both pagination and Load More are disabled
}

?>
 
<div class="page-loading"><?php echo esc_html($pagimore_preloader_text); ?></div>
<div class="ajax-pagination product-pagi" data-posts-per-page="<?php echo esc_attr($ppp); ?>">
    <div class="product-pagi__wrapper">
    <?php if ($show_pagination): ?>
        <?php if ($current_page > 1): ?>
            <span class="product-pagi__prev pagination-btn" data-page="<?php echo esc_attr($current_page) - 1 ?>" style="background-image: url('<?php echo esc_url($prev_icon); ?>');"></span>
        <?php else: ?>
            <span class="product-pagi__prev disabled" style="background-image: url('<?php echo esc_url($prev_icon); ?>');"></span>
        <?php endif; ?>

        <?php
        $range = 1;
$dot_shown_left = false;
$dot_shown_right = false;

for ($i = 1; $i <= $total_pages; $i++):
    if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
        // Show page number
        $active_class = ($i == $current_page) ? ' active' : ' pagination-btn';
        echo '<span class="product-pagi__page' . esc_attr($active_class) . '" data-page="' . esc_attr($i) . '">' . esc_attr($i) . '</span>';
    } elseif ($i < $current_page && !$dot_shown_left) {
        // Show left dots once
        echo '<span class="page-dots">…</span>';
        $dot_shown_left = true;
    } elseif ($i > $current_page && !$dot_shown_right) {
        // Show right dots once
        echo '<span class="page-dots">…</span>';
        $dot_shown_right = true;
    }
endfor;?>

        <?php if ($current_page < $total_pages): ?>
            <span class="product-pagi__next pagination-btn" data-page="<?php echo esc_attr($current_page) + 1 ?>" style="background-image: url('<?php echo esc_url($next_icon); ?>');"></span>
        <?php else: ?>
            <span class="product-pagi__next disabled" style="background-image: url('<?php echo esc_url($next_icon); ?>');"></span>
        <?php endif; ?>
    <?php endif; ?>
</div>
    <?php if ($show_load_more && $current_page < $total_pages): ?>
        <?php $Load_more = esc_attr(get_option('pagimore_load_more_text', 'Load More')); ?>
        <div class="load-pagimore"><?php echo esc_html($Load_more); ?></div>
    <?php endif; ?>
</div>
 