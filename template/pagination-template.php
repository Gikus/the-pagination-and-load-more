<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// File: pagination-template.php
// Use $query if available (AJAX), otherwise fall back to $GLOBALS['cubeabf_loop']
$wp_query = isset($query) ? $query : (isset($GLOBALS['cubeabf_loop']) ? $GLOBALS['cubeabf_loop'] : null);
if (!$wp_query) {
    return; // Exit if no query is available
}


 $total_posts = $wp_query->found_posts;

 $more_param = get_option('cubeabf_more_url_param', 'after');

// Use $_SERVER['REQUEST_URI'] directly or sanitize properly
$request_uri = !empty($_POST['current_url'])
    ? esc_url_raw($_POST['current_url']) // Use esc_url_raw instead of sanitize_text_field
    : $_SERVER['REQUEST_URI'];

$parsed_url = parse_url($request_uri);

// Path remains same
$request_path = trim($parsed_url['path'] ?? '', '/');

// Get the query string as is
$query_string = $parsed_url['query'] ?? '';

// Rebuild query suffix with a leading ? if necessary
$query_suffix = !empty($query_string)
    ? '?' . $query_string
    : '';

// Remove ANY `/page/N/` or `/more/N/` from the entire path
$base_path = preg_replace(
    sprintf('/\/(page|%s)\/\d+\b/i', preg_quote($more_param, '/')),
    '',
    '/' . $request_path
);

// Normalize slashes and trim
$base_path = trim(preg_replace('#/+#', '/', $base_path), '/');

// Rebuild final URL
$base_url = home_url('/' . $base_path . '/');


// Segments

// Strip pagination pattern: page/{number}
$req_path = preg_replace('#/page/\d+/?$#', '', $request_path);

$segments = array_filter(explode('/', $req_path)); // remove empty
  
// Check if only **one segment** exists
if (count($segments) === 1 && $segments[0] !== '') {
    $deeppage = true;
} else {
    $deeppage = false;
}

if (strpos($base_url, '/page/') !== false) {
    // /page/ exists in the URL
    $has_page = true;
} else {
    // /page/ does NOT exist
    $has_page = false;
}
 
 $ppp = isset($_POST['per_page']) ? intval($_POST['per_page']) : (isset($wp_query->query_vars['posts_per_page']) ? $wp_query->query_vars['posts_per_page'] : get_option('cubeabf_posts_per_page', 5));

 
//CSS classes
$cubeabf_wrapper_css = get_option('cubeabf_wrapper_css', 'ajax-pagination');
$cubeabf_add_wrapper_css = get_option('cubeabf_add_wrapper_css', 'product-pagi');
$cubeabf_wrapper_numbers_css = get_option('cubeabf_wrapper_numbers_css', 'product-pagi__wrapper');
$cubeabf_pagi_btn_css = get_option('cubeabf_pagi_btn_css', 'pagination-btn');
$cubeabf_pagi_prev_btn_css = get_option('cubeabf_pagi_prev_btn_css', 'product-pagi__prev');
$cubeabf_pagi_next_btn_css = get_option('cubeabf_pagi_next_btn_css', 'product-pagi__next');
$cubeabf_pagi_page_css = get_option('cubeabf_pagi_page_css', 'product-pagi__page');
$cubeabf_pagi_page_dots_css = get_option('cubeabf_pagi_page_dots_css', 'page-dots');
$cubeabf_pagi_load_more_css = get_option('cubeabf_pagi_load_more_css', 'load-cubeabf');



?>
 <?php
// Hybrid handling of lmore value
if ( defined('DOING_AJAX') && DOING_AJAX ) {

$total_pages = $wp_query->max_num_pages;
    // ✅ AJAX context — use the variable passed from handler
    $post_type = isset($pagination_post_type) ? $pagination_post_type : 'post';
    $lmore = isset($pagination_lmore) ? $pagination_lmore : 'right';
    $Load_more = isset($pagination_lmoretext) ? $pagination_lmoretext : 'Load More';  
    $prev_icon = isset($pagination_pagelefticon) ? $pagination_pagelefticon : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg');  
    $next_icon = isset($pagination_pagerighticon) ? $pagination_pagerighticon : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg');  
    $pagelefttext = isset($pagination_pagelefttext) ? $pagination_pagelefttext : 'false';
    $pagerighttext = isset($pagination_pagerighttext) ? $pagination_pagerighttext : 'false';
    $rightleftempty = isset($pagination_rightleftempty) ? $pagination_rightleftempty : 'true';
    $rightlefthide = isset($pagination_rightlefthide) ? $pagination_rightlefthide : 'false';
    $firstlast = isset($pagination_firstlast) ? $pagination_firstlast : 'false';
    $firsttext = isset($pagination_firsttext) ? $pagination_firsttext : 'false';
    $lasttext = isset($pagination_lasttext) ? $pagination_lasttext : 'false';

    $first_icon = isset($pagination_first_icon) ? $pagination_first_icon : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg');  
    $last_icon = isset($pagination_last_icon) ? $pagination_last_icon : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg');
    
 
$enable_pagination = isset($pagination_enable_pagination) ? $pagination_enable_pagination : 'true';
$enable_load_more = isset($pagination_enable_load_more) ? $pagination_enable_load_more : 'true';
$enable_pagination_mobile = isset($pagination_enable_pagination_mobile) ? $pagination_enable_pagination_mobile : 'true';
$enable_load_more_mobile = isset($pagination_enable_load_more_mobile) ? $pagination_enable_load_more_mobile : 'true';
$enable_pagination_pc = isset($pagination_enable_pagination_pc) ? $pagination_enable_pagination_pc : 'true';
$enable_load_more_pc = isset($pagination_enable_load_more_pc) ? $pagination_enable_load_more_pc : 'true';
$arrowsonly = isset($pagination_arrowsonly) ? $pagination_arrowsonly : 'false';
   // Determine device type (basic check using wp_is_mobile())
$is_maxwidth = isset($pagination_maxwidth) ? $pagination_maxwidth : 767;

$ajaxoff = isset($pagination_ajaxoff) ? $pagination_ajaxoff : 'false';

 $carinfinite = isset($pagination_infinite) ? $pagination_infinite : 'true';
 $enable_infinite_mobile = isset($pagination_enable_infinite_mobile) ? $pagination_enable_infinite_mobile : 'true';
$enable_infinite_pc = isset($pagination_enable_infinite_pc) ? $pagination_enable_infinite_pc : 'true';
$pagination_isfilter = !empty($pagination_isfilter);
$current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;

$mid_desktop = isset($pagination_mid_desktop) ? $pagination_mid_desktop : '2';
$mid_mobile = isset($pagination_mid_mobile) ? $pagination_mid_mobile : '1';
$mobile_bp = isset($pagination_mobile_bp) ? $pagination_mobile_bp : '767';
 $pagination_max_pages = isset($pagination_max_pages) ? $pagination_max_pages : 6;

 $max_pages = ceil($pagination_max_pages);
// Если страниц 0 или меньше, записываем пустую строку, иначе — число
$max_pages_val = ($max_pages > 0) ? $max_pages : ''; 

} else {
    // ✅ Normal page load — use query var (e.g. from URL or shortcode injection)
  $total_pages = $wp_query->max_num_pages;
   $current_page = get_query_var('paged') ? get_query_var('paged') : max(1, isset($_POST['page']) ? intval($_POST['page']) : (get_query_var('paged') ? get_query_var('paged') : 1));
    if (get_query_var('page')) {
        $current_page = get_query_var('page');
    }
    $post_type = get_query_var('ptype', 'post');
    $lmore = get_query_var('lmore', 'right');
    $Load_more = get_query_var('lmoretext', 'Load more');
    $prev_icon = get_query_var('pagelefticon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg');
     $next_icon = get_query_var('pagerighticon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg');
    $pagelefttext = get_query_var('pagelefttext', 'false');
    $pagerighttext = get_query_var('pagerighttext', 'false');
    $rightleftempty = get_query_var('rightleftempty', 'true');
    $rightlefthide = get_query_var('rightlefthide', 'false');
    $firstlast = get_query_var('firstlast', 'false');
    $firsttext = get_query_var('firsttext', 'false');
    $lasttext = get_query_var('lasttext', 'false');

      $first_icon = get_query_var('firsticon', CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-start.svg');
     $last_icon = get_query_var('plasticon', CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-end.svg');
 
     // Get settings
$enable_pagination         = get_query_var('enable_pagination', 'true');
$enable_load_more          = get_query_var('enable_load_more', 'true');
$enable_pagination_mobile  = get_query_var('enable_pagination_mobile', 'true');
$enable_load_more_mobile   = get_query_var('enable_load_more_mobile', 'true');
$enable_pagination_pc      = get_query_var('enable_pagination_pc', 'true');
$enable_load_more_pc       = get_query_var('enable_load_more_pc', 'true');
$arrowsonly       = get_query_var('arrowsonly', 'false');
  // Determine device type (basic check using wp_is_mobile())
$is_maxwidth = (int) get_query_var('maxwidth', 767);

$ajaxoff = get_query_var('ajaxoff', 'false');

$carinfinite = get_query_var('infinite', 'true');
$enable_infinite_mobile   = get_query_var('enable_infinite_mobile', 'true');
$enable_infinite_pc      = get_query_var('enable_infinite_pc', 'true');

$midsize_raw  = get_query_var('mid_size', '2,1,767');

// explode: "2,1,768px"
$midsize_parts = array_map('trim', explode(',', $midsize_raw));

// defaults
$mid_desktop = 2;
$mid_mobile  = 1;
$mobile_bp   = '767';

// overwrite if provided
if (isset($midsize_parts[0]) && $midsize_parts[0] !== '') {
    $mid_desktop = (int) $midsize_parts[0];
}

if (isset($midsize_parts[1]) && $midsize_parts[1] !== '') {
    $mid_mobile = (int) $midsize_parts[1];
}

if (isset($midsize_parts[2]) && $midsize_parts[2] !== '') {
    $mobile_bp = preg_replace('/[^0-9a-z%]/i', '', $midsize_parts[2]);
}
 
 
}


$screen_width = isset($_COOKIE['screen_width']) ? (int) $_COOKIE['screen_width'] : 1200;
$is_mobile = $screen_width <= $is_maxwidth;

$is_bpmobile = $screen_width <= $mobile_bp;

$mid_size = $is_bpmobile ? $mid_mobile : $mid_desktop;

// Convert 'true'/'false' strings to actual Boolean
$enable_pagination        = ($enable_pagination === 'true');
$enable_load_more         = ($enable_load_more === 'true');
$enable_pagination_mobile = ($enable_pagination_mobile === 'true');
$enable_load_more_mobile  = ($enable_load_more_mobile === 'true');
$enable_pagination_pc     = ($enable_pagination_pc === 'true');
$enable_load_more_pc      = ($enable_load_more_pc === 'true');

$carinfinite        = ($carinfinite === 'true');
$enable_infinite_mobile     = ($enable_infinite_mobile === 'true');
$enable_infinite_pc      = ($enable_infinite_pc === 'true');
 
$infinite = $carinfinite && ($is_mobile ? $enable_infinite_mobile : $enable_infinite_pc);


$show_pagination = $enable_pagination && ($is_mobile ? $enable_pagination_mobile : $enable_pagination_pc);
$show_load_more = $enable_load_more && ($is_mobile ? $enable_load_more_mobile : $enable_load_more_pc);
  
 function load_more_html_right($post_type) {

    return apply_filters(
        'cubeabf_load_more_right',
        '',
        $post_type
    );
}

function load_more_html_left($post_type) {

    return apply_filters(
        'cubeabf_load_more_left',
        '',
        $post_type
    );
}
  
if (!$show_pagination && !$show_load_more && !$infinite) {
    return; // Exit if both pagination and Load More are disabled
}


?>
 
<div class="<?php echo esc_attr( trim( $cubeabf_wrapper_css . ' ' . $cubeabf_add_wrapper_css ) ); ?>" data-posts-per-page="<?php echo esc_attr($ppp); ?>" data-current-page="<?php echo esc_attr($current_page); ?>" data-total-pages="<?php echo esc_attr($total_pages); ?>" data-total-count="<?php echo esc_attr($total_posts ?? ''); ?>" data-total-max-pages="<?php echo esc_attr($max_pages_val ?? ''); ?>" data-page-id="<?php echo get_queried_object_id(); ?>"> 
   <?php if ($total_pages > 1): ?>
     
<?php if ( $lmore === 'left' && $arrowsonly != 'true' && !$infinite) : ?>
        <?php if ($show_load_more && $current_page < $total_pages): ?>
       
        <div class="<?php echo $cubeabf_pagi_load_more_css; ?>"><?php if ( ! function_exists( 'load_more_html_left' ) ) { echo load_more_html_left($post_type); } ?> <?php echo esc_html($Load_more); ?> <?php 
            if ( ! function_exists( 'load_more_html_right' ) ) {
            echo load_more_html_right($post_type); } ?></div>
    <?php endif; ?>
     <?php endif; ?>
     <div class="cubeabf-pgntn-wrapper">
     <?php if($firstlast === 'true' && $arrowsonly != 'true' && $current_page <= $total_pages): ?>
<?php if($ajaxoff == 'true'): ?>
    <?php if($current_page == '1' && $rightleftempty == 'true'): ?>
            <span class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css ) ); ?> disabled" <?php if($firsttext === 'false'): ?> style="background-image: url('<?php echo esc_url($first_icon); ?>');"<?php endif; ?>><?php echo $firsttext != 'false' ? esc_html($firsttext) : '' ?></span>
                <?php else: ?>
                    <?php echo ''; ?>
                    <?php if($current_page >= '2'): ?>
            <a href="<?php echo $base_url ? esc_url($base_url) : '/'; ?>page/1/<?php echo $query_suffix; ?>" class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css . ' ' . $cubeabf_pagi_btn_css ) ); ?>" <?php if($firsttext === 'false'): ?> style="background-image: url('<?php echo esc_url($first_icon); ?>');"<?php endif; ?>><?php echo $firsttext != 'false' ? esc_html($firsttext) : '' ?></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php else: ?>
                  <?php if($current_page == '1' && $rightleftempty == 'true'): ?>
            <span class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css ) ); ?> disabled" <?php if($firsttext === 'false'): ?> style="background-image: url('<?php echo esc_url($first_icon); ?>');"<?php endif; ?>><?php echo $firsttext != 'false' ? esc_html($firsttext) : '' ?></span>
                <?php else: ?>
                    <?php echo ''; ?>
                  <?php if($current_page >= '2'): ?>  
            <span class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css . ' ' . $cubeabf_pagi_btn_css ) ); ?>" data-page="1" <?php if($firsttext === 'false'): ?> style="background-image: url('<?php echo esc_url($first_icon); ?>');" <?php endif; ?>><?php echo $firsttext != 'false' ? esc_html($firsttext) : '' ?></span>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    <div class="<?php echo $cubeabf_wrapper_numbers_css; ?>">
    <?php if ($show_pagination): ?> 
<?php if ($rightlefthide != 'true'): ?>
        <?php if ($current_page > 1 && $current_page <= $total_pages): ?>
           <?php if($ajaxoff == 'true'): ?>
            <a href="<?php echo $base_url ? esc_url($base_url) : '/'; ?>page/<?php echo esc_attr($current_page) - 1 ?>/<?php echo esc_url($query_suffix); ?>"><span class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css . ' ' . $cubeabf_pagi_btn_css . ' ' . ( $arrowsonly == 'true' && $pagelefttext == 'false' ? 'arrow-padding' : '' )) ); ?>" data-page="<?php echo esc_attr($current_page) - 1 ?>" <?php if($pagelefttext === 'false'): ?> style="background-image: url('<?php echo esc_url($prev_icon); ?>');" <?php endif; ?>><?php echo $pagelefttext != 'false' ? esc_html($pagelefttext) : '' ?><?php echo $arrowsonly == 'true' ? '<span>' . esc_attr($current_page) - 1 . '</span>' : ''; ?></span></a>
       <?php else: ?>
                <span class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css . ' ' . $cubeabf_pagi_btn_css . ' ' . ( $arrowsonly == 'true' && $pagelefttext == 'false' ? 'arrow-padding' : '' )) ); ?>" data-page="<?php echo esc_attr($current_page) - 1 ?>" <?php if($pagelefttext === 'false'): ?> style="background-image: url('<?php echo esc_url($prev_icon); ?>');" <?php endif; ?>><?php echo $pagelefttext != 'false' ? esc_html($pagelefttext) : '' ?><?php echo $arrowsonly == 'true' ? '<span>' . esc_attr($current_page) - 1 . '</span>' : ''; ?></span>
                <?php endif; ?>
                <?php elseif($current_page == 1 && $rightleftempty == 'true' && $current_page < $total_pages): ?>
            <span class="<?php echo $cubeabf_pagi_prev_btn_css; ?> disabled" <?php if($pagelefttext === 'false'): ?> style="background-image: url('<?php echo esc_url($prev_icon); ?>');" <?php endif; ?>><?php echo $pagelefttext != 'false' ? esc_html($pagelefttext) : '' ?></span>
        <?php else: ?>
           <?php echo ''; ?> 
        <?php endif; ?>
        <?php endif; ?>
         

        <?php
        if($arrowsonly != 'true') {
        $range = $mid_size;
$dot_shown_left = false;
$dot_shown_right = false;

for ($i = 1; $i <= $total_pages; $i++):
    if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
        // Show page number
        $active_class = ($i == $current_page) ? 'active' : $cubeabf_pagi_btn_css;
       if($ajaxoff == 'true') {
        echo '<a href="' . ( $base_url ? esc_url($base_url) : '/' ) . 'page/' . esc_attr($i) . '/' . $query_suffix . '"><span class="' . esc_attr($cubeabf_pagi_page_css) . ' ' . esc_attr($active_class) . '" data-page="' . esc_attr($i) . '">' . esc_attr($i) . '</span></a>';
       } else {
       echo '<span class="' . esc_attr($cubeabf_pagi_page_css) . ' ' . esc_attr($active_class) . '" data-page="' . esc_attr($i) . '">' . esc_attr($i) . '</span>';
       }
    } elseif ($i < $current_page && !$dot_shown_left) {
        // Show left dots once
        echo '<span class="' . $cubeabf_pagi_page_dots_css .'">…</span>';
        $dot_shown_left = true;
    } elseif ($i > $current_page && !$dot_shown_right) {
        // Show right dots once
        echo '<span class="' . $cubeabf_pagi_page_dots_css .'">…</span>';
        $dot_shown_right = true;
    }
endfor;

} elseif(($show_load_more && $current_page < $total_pages) && $arrowsonly == 'true') { ?>
<div class="<?php echo $cubeabf_pagi_load_more_css; ?>"><?php echo esc_html($Load_more); ?></div>
<?php } ?>

        <?php if ($current_page < $total_pages): ?>
            <?php if ($rightlefthide != 'true'): ?>
            <?php if($ajaxoff == 'true'): ?>
            <a href="<?php echo $base_url ? esc_url($base_url) : '/'; ?>page/<?php echo esc_attr($current_page) + 1 ?>/<?php echo esc_url($query_suffix); ?>"><span class="<?php echo esc_attr( trim( $cubeabf_pagi_next_btn_css . ' ' . $cubeabf_pagi_btn_css . ' ' . ( $arrowsonly == 'true' && $pagerighttext == 'false' ? 'arrow-padding' : '' )) ); ?>" data-page="<?php echo esc_attr($current_page) + 1 ?>" <?php if($pagerighttext === 'false'): ?> style="background-image: url('<?php echo esc_url($next_icon); ?>');"<?php endif; ?>><?php echo $pagerighttext != 'false' ? esc_html($pagerighttext) : ''; ?><?php echo $arrowsonly == 'true' ? '<span>' . esc_attr($current_page) + 1 . '</span>' : ''; ?></span></a>
       <?php else: ?>
        <span class="<?php echo esc_attr( trim( $cubeabf_pagi_next_btn_css . ' ' . $cubeabf_pagi_btn_css . ' ' . ( $arrowsonly == 'true' && $pagerighttext == 'false' ? 'arrow-padding' : '' )) ); ?>" data-page="<?php echo esc_attr($current_page) + 1 ?>" <?php if($pagerighttext === 'false'): ?> style="background-image: url('<?php echo esc_url($next_icon); ?>');" <?php endif; ?>><?php echo $pagerighttext != 'false' ? esc_html($pagerighttext) : ''; ?><?php echo $arrowsonly == 'true' ? '<span>' . esc_attr($current_page) + 1 . '</span>' : ''; ?></span>
        <?php endif; ?>
        <?php endif; ?>
            <?php elseif($current_page == $total_pages && $rightleftempty == 'true' && $rightlefthide != 'true'): ?>
            <span class="<?php echo $cubeabf_pagi_next_btn_css; ?> disabled" <?php if($pagerighttext === 'false'): ?> style="background-image: url('<?php echo esc_url($next_icon); ?>');" <?php endif; ?>><?php echo $pagerighttext != 'false' ? esc_html($pagerighttext) : ''; ?></span>
           <?php else: ?>
           <?php echo ''; ?> 
        
        <?php endif; ?>
        <?php endif; ?>
</div>
<?php if($firstlast === 'true' && $arrowsonly != 'true'): ?>
<?php if($ajaxoff == 'true'): ?>
    <?php if($current_page == $total_pages && $rightleftempty == 'true'): ?>
            <span class="<?php echo esc_attr( trim( $cubeabf_pagi_next_btn_css ) ); ?> disabled" <?php if($lasttext === 'false'): ?> style="background-image: url('<?php echo esc_url($last_icon); ?>');"<?php endif; ?>><?php echo $lasttext != 'false' ? esc_html($lasttext) : '' ?></span>
                <?php else: ?>
                    <?php echo ''; ?>
                     <?php if($current_page < $total_pages): ?>
            <a href="<?php echo $base_url ? esc_url($base_url) : '/'; ?>page/<?php echo esc_attr($total_pages); ?>/<?php echo $query_suffix; ?>" class="<?php echo esc_attr( trim( $cubeabf_pagi_prev_btn_css . ' ' . $cubeabf_pagi_btn_css ) ); ?> <?php echo ($current_page == $total_pages) ? 'disabled' : ''; ?>" <?php if($lasttext === 'false'): ?> style="background-image: url('<?php echo esc_url($last_icon); ?>');"<?php endif; ?>><?php echo $lasttext != 'false' ? esc_html($lasttext) : '' ?></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php else: ?>
            <?php if($current_page == $total_pages && $rightleftempty == 'true'): ?>
            <span class="<?php echo esc_attr( trim( $cubeabf_pagi_next_btn_css ) ); ?> disabled" <?php if($lasttext === 'false'): ?> style="background-image: url('<?php echo esc_url($last_icon); ?>');"<?php endif; ?>><?php echo $lasttext != 'false' ? esc_html($lasttext) : '' ?></span>
                <?php else: ?>
                    <?php echo ''; ?>
                     <?php if($current_page < $total_pages): ?>
                    <span class="<?php echo esc_attr( trim( $cubeabf_pagi_next_btn_css . ' ' . $cubeabf_pagi_btn_css ) ); ?> <?php echo ($current_page == $total_pages) ? 'disabled' : ''; ?>" data-page="<?php echo esc_attr($total_pages); ?>" <?php if($lasttext === 'false'): ?> style="background-image: url('<?php echo esc_url($last_icon); ?>');" <?php endif; ?>><?php echo $lasttext != 'false' ? esc_html($lasttext) : '' ?></span>
            <?php endif; ?>
              <?php endif; ?>
              <?php endif; ?>
        <?php endif; ?>
        </div>
<?php if ( $lmore === 'right' && $arrowsonly != 'true' && !$infinite) : ?>
    <?php if ($show_load_more && $current_page < $total_pages): ?>
        
        <div class="<?php echo $cubeabf_pagi_load_more_css; ?>"><?php 
if ( ! function_exists( 'load_more_html_left' ) ) {
        echo load_more_html_left($post_type); } ?> <?php echo esc_html($Load_more); ?> <?php 
            if ( ! function_exists( 'load_more_html_right' ) ) {
            echo load_more_html_right($post_type); } ?>
     </div>
    <?php endif; ?>
    <?php endif;  ?>
    <?php endif;  ?>
</div>
