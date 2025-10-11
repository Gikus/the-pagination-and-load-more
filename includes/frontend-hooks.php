<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// File: frontend-hooks.php
function cubeb_plugin_load_textdomain() {
    load_plugin_textdomain( 'cubeb-pagination-and-load-more', false, dirname( plugin_basename( CUBEPAGI_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'init', 'cubeb_plugin_load_textdomain' );
 

class CubebCustomUrlParameters {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_filter('query_vars', array($this, 'query_vars'));
    }
    
    public function init() {
        // Register all your rewrite rules here
        $this->add_rewrite_rules();
    }
    
    public function query_vars($vars) {
        $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
        $vars[] = $more_param;
        return $vars;
    }
    
    private function add_rewrite_rules() {

        $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
    

        // Get WooCommerce permalinks settings
$wooperma = get_option('woocommerce_permalinks');

// Get product category base (returns 'product-category' by default)
$product_categ_base = $wooperma['category_base'] ?? 'product-category';

// Clean up the value (might contain slashes)
$product_categ_base = trim($product_categ_base, '/');
// Match any number of category segments
add_rewrite_rule(
    '^' . $product_categ_base . '/(.+)/page/([0-9]+)/' . $more_param . '/([0-9]+)/?$',
    'index.php?product_cat=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
    'top'
);

 // Get WooCommerce permalink structure and the actual product-tag base.
    if (function_exists('wc_get_permalink_structure')) {
        $wc_permalinks = wc_get_permalink_structure();
        $tag_base = ! empty($wc_permalinks['tag_base']) ? trim($wc_permalinks['tag_base'], '/') : 'product-tag';
    } else {
        $tag_base = 'product-tag'; // fallback
    }
    

// Add rewrite rule for product tags
add_rewrite_rule(
        '^' . preg_quote($tag_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?product_tag=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );


// WordPress post tag base
$post_tag_base = get_option('tag_base', 'tag'); // default WP post tag base

    add_rewrite_rule(
        '^' . preg_quote($post_tag_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?tag=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );
 
// Get the product_brand taxonomy object
$taxonomy = get_taxonomy('product_brand');

if ( $taxonomy && ! empty($taxonomy->rewrite['slug']) ) {
    $brand_base = $taxonomy->rewrite['slug']; // e.g. 'product-brand'
} else {
    $brand_base = 'product-brand'; // fallback default
}

// Clean it up
$brand_base = trim($brand_base, '/');


// Match any number of brand/subbrand segments
add_rewrite_rule(
    '^' . preg_quote($brand_base, '#') . '/(.+)/page/([0-9]+)/?$',
    'index.php?product_brand=$matches[1]&paged=$matches[2]',
    'top'
);

// Match with pagination + "more" param
add_rewrite_rule(
    '^' . preg_quote($brand_base, '#') . '/(.+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
    'index.php?product_brand=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
    'top'
);

// Match any page slug + pagination + "more" param
    add_rewrite_rule(
        '^([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?pagename=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );
    // Front page pagination + "more" param: /page/3/more/2/
add_rewrite_rule(
    '^page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
    'index.php?pagename=' . get_option('page_on_front') . '&paged=$matches[1]&' . $more_param . '=$matches[2]',
    'top'
);
    }
    
    // Flush rules on activation
    public static function activate() {
        flush_rewrite_rules();
    }
    
    // Flush rules on deactivation
    public static function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new CubebCustomUrlParameters();

// Hook activation and deactivation
register_activation_hook(__FILE__, array('CustomUrlParameters', 'activate'));
register_deactivation_hook(__FILE__, array('CustomUrlParameters', 'deactivate'));
 
// Sortcode for the pagimore_loop

add_action('template_redirect', 'handle_ports_parameter_templates');
function handle_ports_parameter_templates() {
    $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
    $ports = get_query_var($more_param);
    
    if (!$ports) return;
    
    global $wp_query;
    
    // Clear 404 status
    $wp_query->is_404 = false;
    status_header(200);
    
    // Determine the context and set appropriate flags
    if (get_query_var('category_name')) {
        $wp_query->is_category = true;
        $wp_query->is_archive = true;
    }
    else if (get_query_var('product_cat')) {
        $wp_query->is_tax = true;
        $wp_query->is_archive = true;
    }
    else if ($ports && get_query_var('product_tag')) {
        $wp_query->is_404 = false;
        $wp_query->is_tax = true;
        $wp_query->is_archive = true;
        $wp_query->queried_object = get_term_by('slug', get_query_var('product_tag'), 'product_tag');
    } else if ($ports && get_query_var('tag')) {
        $wp_query->is_404 = false;
        $wp_query->is_tag = true;
        $wp_query->is_archive = true;
        $wp_query->queried_object = get_term_by('slug', get_query_var('tag'), 'post_tag');
    }
    else {
        // It's a custom paginated page with ports parameter
        $wp_query->is_page = true;
        $wp_query->is_singular = false;
    }
}

  // Force the correct template
add_filter('template_include', 'custom_ports_template', 99);
function custom_ports_template($template) {
      global $post, $wp_query;

      $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
    $ports = get_query_var($more_param);

    function is_gutenberg_page( $post = null ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return false;
    }

    // Check for block syntax in post content
    if ( has_blocks( $post ) ) {
        return true;
    }

    // Fallback manual check for very old WP versions
    if ( strpos( $post->post_content, '<!-- wp:' ) !== false ) {
        return true;
    }

    return false;
}

function is_elementor_page( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    return (bool) get_post_meta( $post_id, '_elementor_edit_mode', true );
}


function is_wpbakery_page( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    $status = get_post_meta( $post_id, '_wpb_vc_js_status', true );

    if ( $status === 'true' ) {
        return true;
    }

    $post = get_post( $post_id );
    if ( $post && strpos( $post->post_content, '[vc_row' ) !== false ) {
        return true;
    }

    return false;
}

 
 function detect_page_builder( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();

    if ( is_elementor_page( $post_id ) ) {
        return 'elementor';
    }
    if ( is_wpbakery_page( $post_id ) ) {
        return 'wpbakery';
    }
    if ( is_gutenberg_page( $post_id ) ) {
        return 'gutenberg';
    }

    return 'classic';
}

$pbuilder = detect_page_builder();
      
   // Get the template assigned in Page Attributes
    $assigned_template = get_page_template_slug( $post->ID ); 

    if ( $assigned_template && file_exists( get_stylesheet_directory() . '/' . $assigned_template ) ) {
        // Use the assigned template
         $templates = get_stylesheet_directory() . '/' . $assigned_template;
          
    } elseif ( is_404() || $wp_query->is_404 ) {
 
    // Scan the theme directory for custom 404-like templates
    $theme_dir = get_stylesheet_directory();
    $theme_templates404 = scandir( $theme_dir );

    // Match files ending with -page404.php or -404.php
    $custom_templates404 = preg_grep( '/-(page404|404)\.php$/', $theme_templates404 );

    // Build template priority
    $templates = array_merge(
        $custom_templates404 ? $custom_templates404 : [],
        [
            '404.php',
            'notfound.php',
            'index.php',
        ]
    );
 
     
} elseif ( $pbuilder != 'gutenberg' && $pbuilder != 'elementor' && $pbuilder != 'wpbakery' && $ports && !$wp_query->is_404 && !is_404() && !is_single() && !is_singular('product') && !is_category() && !get_query_var('product_cat') && !get_query_var('category_name') && !get_query_var('product_tag') && !get_query_var('product_brand') && !get_query_var('tag')) {
    // Custom templates in the theme
    $theme_templates = scandir( get_stylesheet_directory() );

    // Filter templates ending with -page.php, -template.php, -home.php, -post-type.php, -posts.php
    $templates = preg_grep( '/-(page|template|home|post-type|posts)\.php$/', $theme_templates );
}    
   
if(!empty($templates)) {
 // Locate the first existing template
    $found_template = locate_template( $templates );

   
    if ( $found_template ) {
        return $found_template;
    }

    }
// Fallback
return $template;
}

add_action('template_redirect', 'force_template_for_woo_ports');
function force_template_for_woo_ports() {
    $more_param   = esc_attr( get_option('pagimore_more_url_param', 'more') );
    $ports        = get_query_var( $more_param );
    $product_cat  = get_query_var( 'product_cat' );
    $product_tag  = get_query_var( 'product_tag' );
    $product_brand = get_query_var( 'product_brand' );  

    if ( $ports && ( $product_cat || $product_tag || $product_brand ) ) {
        global $wp_query;

        // Clear 404 status
        $wp_query->is_404 = false;
        status_header(200);

        // Mark as WooCommerce taxonomy archive
        $wp_query->is_tax              = true;
        $wp_query->is_archive          = true;
        $wp_query->is_product_taxonomy = true;

        // Optional: mark specifically what kind of taxonomy
        if ( $product_cat ) {
            $wp_query->queried_object = get_term_by( 'slug', $product_cat, 'product_cat' );
        } elseif ( $product_tag ) {
            $wp_query->queried_object = get_term_by( 'slug', $product_tag, 'product_tag' );
        } elseif ( $product_brand ) {
            $wp_query->queried_object = get_term_by( 'slug', $product_brand, 'product_brand' );
        }
    }
}


// Force WooCommerce template
add_filter('template_include', 'force_woocommerce_template_for_ports', 99);
function force_woocommerce_template_for_ports($template) {
    $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
    $ports = get_query_var($more_param);
    $product_cat = get_query_var('product_cat');
    
    if ($ports && $product_cat) {
        // Use WooCommerce taxonomy template
        $templates = array();

if ( $product_cat ) {
    $templates[] = "taxonomy-product_cat-{$product_cat}.php";
}

$templates[] = "taxonomy-product_cat.php";
$templates[] = "archive-product.php";
$templates[] = "category.php";
$templates[] = "archive.php";
        
        $found_template = locate_template($templates);
        if ($found_template) {
            return $found_template;
        }
    }
    
    return $template;
}

function pagimore_shortcode_handle() {
    
    ob_start();

    
$post_type = get_option('pagimore_post_type', 'post');
  
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
    if (get_query_var('page')) {
        $paged = get_query_var('page');
    }
 
  $args = [
    'post_type'      => $post_type,
    'posts_per_page' => (int) get_option('pagimore_post_per_page', 6),
    'paged'          => $paged,
    'ignore_sticky_posts' => true,
     'post_status'         => 'publish',
     'orderby' => 'post_date', // More specific than just 'date'
    'order' => 'DESC',
    
];


 
if ( function_exists( 'is_category' ) && is_category() ) {
    $term = get_queried_object();
 

    if ( $term && ! is_wp_error( $term ) ) {
        // Get descendant category IDs (children and deeper)
        $child_cat_ids = get_term_children( $term->term_id, 'category' );
        
        // Include the current category ID itself
        $all_cat_ids = array_merge( array( $term->term_id ), $child_cat_ids );

        // Only proceed if there are categories to query
        if ( ! empty( $all_cat_ids ) ) {
            $args['tax_query'] = [ ['taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $all_cat_ids, ] ];
        }
    }
}




    // If on WooCommerce product category
    if (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();
        $args['post_type'] = 'product';
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ],
        ];
    }

    // If on WooCommerce product tag
if (function_exists('is_tax') && is_tax('product_tag')) {
    $termtag = get_queried_object();
    
    $args['post_type'] = 'product';
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_tag',
            'field'    => 'term_id',
            'terms'    => $termtag->term_id,
        ],
    ];
    
}

    $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
 $ports = get_query_var($more_param );
 
if ($ports) {
    $args['meta_query'] = array(
        array(
            'key' => 'port_number',
            'value' => $ports,
            'compare' => '='
        )
    );
}
 if(get_query_var('tag')) {
    $tag_slug = get_query_var('tag');
   $args['tag'] = $tag_slug; 

 } 

 if(get_query_var('product_brand')) {
    $br_slug = get_query_var('product_brand');
   $args['product_brand'] = $br_slug; 

 } 

   $sclass = esc_attr(get_option('pagimore_query_selector', 'post-list'));

    $qselect = ltrim($sclass, '.#'); // removes leading dot or hash if exists
 
// create query for template rendering
$blog_posts = new WP_Query($args);

if ( ! $blog_posts->have_posts() && !$ports) {
         global $wp_query;
        status_header(404);
        $wp_query->set_404();
        $wp_query->is_404 = true;
         // Load the theme’s 404.php template directly
   // 🔍 Scan theme directory for custom 404 templates
    $theme_dir = get_stylesheet_directory();
    $theme_templates404 = scandir( $theme_dir );

    // Match files ending with -page404.php or -404.php
    $custom_templates404 = preg_grep( '/-(page404|404)\.php$/', $theme_templates404 );

    // Build priority list: custom matches first, then fallbacks
    $templates = array_merge(
        $custom_templates404 ? $custom_templates404 : [],
        [
            '404.php',
            'notfound.php',
            'index.php',
        ]
    );

    // ✅ Try to locate the first existing template
    $theme_404 = locate_template( $templates );

    if ( $theme_404 ) {
        include $theme_404;
        exit;
    }
    }  

    ?>
<section class="<?php echo esc_attr($qselect); ?>">
    <?php if ($blog_posts->have_posts()): while ($blog_posts->have_posts()): $blog_posts->the_post(); ?>
   <?php  $current_type = get_post_type(); get_template_part( 'template-parts/content', $current_type ); ?>
  <?php endwhile; endif; wp_reset_postdata(); ?>
</section>
<?php
// render pagination controls
if ( $blog_posts->max_num_pages > 1 ) {
    $GLOBALS['pagimore_loop'] = $blog_posts;

    $template = locate_template( 'pagination-template.php' );
    if ( ! $template ) {
        $template = CUBEPAGI_PLUGIN_DIR . '/template/pagination-template.php';
    }

    include $template;
}
   return ob_get_clean();
}
add_shortcode('cubeab_code', 'pagimore_shortcode_handle');

add_action('wp_enqueue_scripts', function() {
    $enable_pagination = get_option('pagimore_enable_pagination', 1);
    $enable_load_more = get_option('pagimore_enable_load_more', 1);
    $enable_pagination_mobile = get_option('pagimore_enable_pagination_mobile', 1);
    $enable_load_more_mobile = get_option('pagimore_enable_load_more_mobile', 1);
    $enable_pagination_pc = get_option('pagimore_enable_pagination_pc', 1);
    $enable_load_more_pc = get_option('pagimore_enable_load_more_pc', 1);
    


   global $pagimore_template_query_args;
$pagimore_template_query_args = null;
 
    $default_paged = max(1, get_query_var('paged'));
    $query_args = [];

    if (!empty($pagimore_template_query_args) && is_array($pagimore_template_query_args)) {
        // Ensure paged exists
        $query_args = $pagimore_template_query_args;
        if (empty($query_args['paged'])) {
            $query_args['paged'] = $default_paged;
        }
    } else {
        $post_type = get_option('pagimore_post_type', 'post');
        $query_args = [
            'post_type' => $post_type,
            'posts_per_page' => 5,
            'ignore_sticky_posts' => true, 
            'paged' => $default_paged,
                'orderby' => 'post_date', // More specific than just 'date'
    'order' => 'DESC',
        ];
    }

  

$current_product_cat_slug = '';

// Check WooCommerce product category only if WooCommerce is active
if (function_exists('is_product_category') && is_product_category()) {
    $current_cat = get_queried_object(); // WP_Term object
    if ($current_cat && !is_wp_error($current_cat)) {
        $current_product_cat_slug = $current_cat->slug;
    }
}

$current_product_tag_slug = '';
// Check WooCommerce product tag only if WooCommerce is active
if (function_exists('is_tax') && is_tax('product_tag')) {
    $current_tag = get_queried_object(); // WP_Term object
    if ($current_tag && !is_wp_error($current_tag)) {
        $current_product_tag_slug = $current_tag->slug;
    }
}

$current_cat_slug = '';
// Check normal WP category
if (function_exists('is_category') && is_category()) {
    $current_cat = get_queried_object();
    if ($current_cat && !is_wp_error($current_cat)) {
        $current_cat_slug = $current_cat->slug;
    }
}

$current_tag_slug = '';

// Check normal WP post tag
if (function_exists('is_tag') && is_tag()) {
    $current_p_tag = get_queried_object(); // WP_Term object
    if ($current_p_tag && !is_wp_error($current_p_tag)) {
        $current_tag_slug = $current_p_tag->slug;
    }
}

$current_brand_slug = '';
// Check WooCommerce product brand
if ( function_exists( 'is_tax' ) && is_tax( 'product_brand' ) ) {
    $current_brand = get_queried_object(); // WP_Term object
    if ( $current_brand && ! is_wp_error( $current_brand ) ) {
        $current_brand_slug = $current_brand->slug;
    }
}

// Get the category base slug (returns 'category' by default)
$category_base = get_option('category_base');

// If empty, WordPress uses 'category' as default
if (empty($category_base)) {
    $category_base = 'category';
}
 

// Get WooCommerce permalinks settings
$woopermalinks = get_option('woocommerce_permalinks');

// Get product category base (returns 'product-category' by default)
$product_category_base = $woopermalinks['category_base'] ?? 'product-category';

// Clean up the value (might contain slashes)
$product_category_base = trim($product_category_base, '/');

 


    $query = new WP_Query($query_args);

    // Only enqueue scripts/styles if pagination or load more is enabled for the device
    if ($enable_pagination || $enable_load_more) {
        wp_enqueue_script('pagimore-pagination', plugins_url('/assets/pagination.js', CUBEPAGI_PLUGIN_FILE), ['jquery'], CUBEPAGI_VERSION, true);
 
        wp_localize_script('pagimore-pagination', 'pagimore_ajax_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('pagimore_nonce'),
            'query_selector' => get_option('pagimore_query_selector', '.post-list'),
            'max_pages' => $query->max_num_pages,
            'enable_pagination' => $enable_pagination,
            'enable_load_more' => $enable_load_more,
            'enable_pagination_mobile' => $enable_pagination_mobile,
            'enable_load_more_mobile' => $enable_load_more_mobile,
            'enable_pagination_pc' => $enable_pagination_pc,
            'enable_load_more_pc' => $enable_load_more_pc,
            'query_args' => $query_args,
            'post_type' => get_option('pagimore_post_type', 'post'),
            'per_page'       => (int) get_option('pagimore_post_per_page', 6),
            'pagimore_more_url_param' => esc_attr(get_option('pagimore_more_url_param', 'more')),
            'prev_arrow_icon' => esc_url(get_option('pagimore_prev_arrow_icon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg')),
            'next_arrow_icon' => esc_url(get_option('pagimore_next_arrow_icon', CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg')),
            'pagimore_preloader_text' => esc_attr(get_option('pagimore_preloader_text', 'Loading...')),
            'product_cat'=> $current_product_cat_slug,
            'product_tag' => $current_product_tag_slug,
            'current_cat'   => $current_cat_slug,
            'zapisi_tag' => $current_tag_slug,
            'cat_base' => $category_base,
            'woo_cat_base' => $product_category_base,
            'woo_brand_slug' =>  $current_brand_slug,
            'remove_pages' => (bool) get_option('pagimore_remove_pages', 0),
            'query_selector' => esc_attr(get_option('pagimore_query_selector', 'post-list')),
        ]);
        if ( ! get_option('pagimore_disable_styles', 0) ) {
    $active_color       = esc_attr(get_option('pagimore_pagi_active_color', '#0073aa'));
    $active_hover_color = esc_attr(get_option('pagimore_pagi_hover_active_color', '#005177'));

    $pagimore_css = "
    .load-pagimore, .product-pagi__page.active {
        background: {$active_color};
        border-color: {$active_color};
    }
    .load-pagimore:hover, .product-pagi__page.active:hover {
        background: {$active_hover_color};
        border-color: {$active_hover_color};
    }";

    // Enqueue your main stylesheet first
    wp_enqueue_style(
        'pagimore-pagination-style',
        CUBEPAGI_PLUGIN_URL . '/assets/pagination.css',
        [],
        time() // cache-busting
    );

    // Then attach the inline CSS to the same handle
    wp_add_inline_style('pagimore-pagination-style', $pagimore_css);
}

    }
});


add_action('wp_ajax_pagimore_load_posts', 'pagimore_load_posts');
add_action('wp_ajax_nopriv_pagimore_load_posts', 'pagimore_load_posts');

function pagimore_load_posts() {
if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'pagimore_nonce')) {
        wp_send_json_error('Nonce verification failed', 400);
        wp_die();
    }
    
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 5;
    $accumulated_pages = isset($_POST['accumulated_pages']) ? array_map('intval', $_POST['accumulated_pages']) : [$paged];
  $slug = sanitize_text_field( wp_unslash($_POST['category_slug']) ?? wp_unslash(_GET['category_slug']) ?? '' );
$current_cat = get_term_by('slug', $slug, 'product_cat');

  $slugTag = '';
if ( isset($_POST['woo_tag']) ) {
    $slugTag = sanitize_text_field( wp_unslash( $_POST['woo_tag'] ) );
} elseif ( isset($_GET['woo_tag']) ) {
    $slugTag = sanitize_text_field( wp_unslash( $_GET['woo_tag'] ) );
}

$current_tag = $slugTag ? get_term_by( 'slug', $slugTag, 'product_tag' ) : false;

 $postcatslug = sanitize_text_field( wp_unslash($_POST['post_cat']) ?? wp_unslash($_GET['post_cat']) ?? '' );

 $tagTag = '';
if ( isset($_POST['cubeb_post_tag']) ) {
    $tagTag = sanitize_text_field( wp_unslash( $_POST['cubeb_post_tag'] ) );
} elseif ( isset($_GET['post_tag']) ) {
    $tagTag = sanitize_text_field( wp_unslash( $_GET['post_tag'] ) );
}

$tag_slug = '';
if ( $tagTag ) {
    $term = get_term_by( 'slug', $tagTag, 'post_tag' );
    if ( $term && ! is_wp_error( $term ) ) {
        $tag_slug = $term->slug;
    }
}


$current_brand = sanitize_text_field(
    wp_unslash( $_POST['brand_slug'] ?? $_GET['brand_slug'] ?? '' )
);
 
    // Only reset accumulated_pages if not appending (replace)
    $append = isset( $_POST['append'] ) ? sanitize_text_field( wp_unslash( $_POST['append'] ) ) : '';

if ( empty( $append ) ) {
    $accumulated_pages = [ $paged ];
}

$post_type = get_option('pagimore_post_type', 'post');

if ($current_cat) {
  
$args = [
    'post_type'           => $post_type,
    'posts_per_page'      => $per_page,
    'ignore_sticky_posts' => true,
    'paged'               => $paged, // use $paged directly
    'post_status'         => 'publish',
    'orderby' => 'post_date', // More specific than just 'date'
    'order' => 'DESC',
    'tax_query'           => [
        [
            'taxonomy'         => 'product_cat',
            'field'            => 'slug',
            'terms'            => [$current_cat->slug],
            'include_children' => true, // Include child categories
        ]
    ]
];

} else if ( ! empty( $postcatslug ) ) {
    // Get the term object from queried slug
    $term = get_term_by( 'slug', $postcatslug, 'category' );

    if ( $term && ! is_wp_error( $term ) ) {
        $args = [
            'post_type'           => $post_type,
            'posts_per_page'      => $per_page,
            'ignore_sticky_posts' => true,
            'paged'               => $paged,
            'post_status'         => 'publish',
            'tax_query'           => [
                [
                    'taxonomy'         => 'category',
                    'field'            => 'term_id',
                    'terms'            => $term->term_id,
                    'include_children' => true, 
                ],
            ],
            'orderby'             => 'post_date',
            'order'               => 'DESC',
        ];
    }
} else if ($current_tag) {
   
  $args = [
    'post_type'           => 'product',
    'posts_per_page'      => $per_page,
    'ignore_sticky_posts' => true,
    'paged'               => $paged, // pagination
    'post_status'         => 'publish',
    'orderby'             => 'post_date',
    'order'               => 'DESC',
];
 
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_tag',
            'field'    => 'term_id',
            'terms'    => $current_tag->term_id,
        ],
    ];
 

 } else if ($tag_slug) {
    $args = [
        'post_type'           => $post_type,
        'posts_per_page'      => $per_page,
        'tag'                 => $tag_slug,
        'ignore_sticky_posts' => true,
        'paged'               => $paged,
        'post_status'         => 'publish',
        'orderby'             => 'post_date',
        'order'               => 'DESC',
    ];
} elseif ( $current_brand ) {

    $args = [
        'post_type'           => $post_type,
        'posts_per_page'      => $per_page,
        'ignore_sticky_posts' => true,
        'paged'               => $paged, // use $paged directly
        'post_status'         => 'publish',
        'orderby'             => 'post_date', // More specific than just 'date'
        'order'               => 'DESC',
        'tax_query'           => [
            [
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => [ $current_brand ],
            ],
        ],
    ];
} else {
   
    $args = [
        'post_type' => $post_type,
        'posts_per_page' => $per_page,
        'ignore_sticky_posts' => true,
        'paged' => $paged, // <--- Use $paged, not max($accumulated_pages)
        'post_status' => 'publish',
        'orderby' => 'post_date', // More specific than just 'date'
        'order' => 'DESC',
    ];
}

    
$ports = get_query_var($more_param );
 
if ($ports) {
    $args['meta_query'] = array(
        array(
            'key' => 'port_number',
            'value' => $ports,
            'compare' => '='
        )
    );
}
    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post(); $current_type = get_post_type(); ?>
            <?php get_template_part('template-parts/content', $current_type); ?>
        <?php endwhile;
    endif;
    $html = ob_get_clean();

    ob_start();
    include CUBEPAGI_PLUGIN_DIR . 'template/pagination-template.php';
    $pagination = ob_get_clean();

    wp_send_json([
        'html' => $html,
        'pagination' => $pagination,
        'max_pages' => $query->max_num_pages,
        'posts_per_page' => $per_page,
        'category_slug' => $current_cat->slug,
        'post_cat' => $post_cat,
    ]);
    wp_die();
}


// 1. Allow SVG upload in Media Library
function mytheme_allow_svg_uploads($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'mytheme_allow_svg_uploads');

// 2. Sanitize SVGs upon upload
function mytheme_sanitize_svg($file) {
    if (isset($file['type']) && $file['type'] === 'image/svg+xml') {
        // Read SVG content
        $svg = file_get_contents($file['tmp_name']);
        
        // Remove scripts and potentially harmful elements
        $svg = preg_replace('/<script.*?<\/script>/is', '', $svg);
        $svg = preg_replace('/on\w+="[^"]*"/i', '', $svg); // removes onload= etc.
        $svg = preg_replace('/javascript:/i', '', $svg);
        
        // Save cleaned SVG back
        file_put_contents($file['tmp_name'], $svg);
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'mytheme_sanitize_svg');

  add_action('template_redirect', 'force_correct_template_pagination');
function force_correct_template_pagination() {

    // Get WooCommerce permalinks settings
$woopermalinks = get_option('woocommerce_permalinks');

// Get product category base (returns 'product-category' by default)
$product_category_base = $woopermalinks['category_base'] ?? 'product-category';

// Clean up the value (might contain slashes)
$product_category_base = trim($product_category_base, '/');

// Get the category base slug (returns 'category' by default)
$category_base = get_option('category_base');

// If empty, WordPress uses 'category' as default
if (empty($category_base)) {
    $category_base = 'category';
}

   if (function_exists('wc_get_permalink_structure')) {
        $wc_permalinks = wc_get_permalink_structure();
       $product_tag_base = ! empty($wc_permalinks['tag_base']) ? trim($wc_permalinks['tag_base'], '/') : 'product-tag';
    } else {
        $product_tag_base = 'product-tag'; // fallback
    }

    global $wp_query;
    
    // Check if we're on a paginated category page but WordPress doesn't recognize it
    if ($wp_query->is_404 && !$wp_query->is_category && !$wp_query->is_tax && !$wp_query->is_tag) {
        $request = $_SERVER['REQUEST_URI'];
        
      // Check for product category pagination with subcategories
if (preg_match('#/' . $product_category_base . '/(.+)/page/([0-9]+)/?#', $request, $matches)) {
    $term_slug = $matches[1]; // could be 'parent-category/child-category'
    $term = get_term_by('slug', $term_slug, 'product_cat');

    // If exact slug not found, try the last segment (child slug)
    if (!$term) {
        $segments = explode('/', $term_slug);
        $term = get_term_by('slug', end($segments), 'product_cat');
    }

    if ($term && !is_wp_error($term)) {
        $wp_query->is_404 = false;
        $wp_query->is_tax = true;
        $wp_query->is_archive = true;
        $wp_query->query_vars['product_cat'] = $term->slug;
        $wp_query->query_vars['paged'] = $matches[2];
    }
}
        
       // Check for post category pagination with subcategories
if (preg_match('#/' . $category_base . '/(.+)/page/([0-9]+)/?#', $request, $matches)) {
    $term_slug = $matches[1]; // could be 'parent-category/child-category'
    $term = get_term_by('slug', $term_slug, 'category');

    // If exact full slug not found, fallback to the last segment (child category)
    if (!$term) {
        $segments = explode('/', $term_slug);
        $term = get_term_by('slug', end($segments), 'category');
    }

    if ($term && !is_wp_error($term)) {
        $wp_query->is_404 = false;
        $wp_query->is_category = true;
        $wp_query->is_archive = true;
        $wp_query->query_vars['category_name'] = $term->slug;
        $wp_query->query_vars['paged'] = $matches[2];
    }
}


        // Check for product tag pagination
    if (preg_match('#/' . $product_tag_base . '/([^/]+)/page/([0-9]+)/?#', $request, $matches)) {
        $term = get_term_by('slug', $matches[1], 'product_tag');
        if ($term && !is_wp_error($term)) {
            $wp_query->is_404 = false;
            $wp_query->is_tax = true;
            $wp_query->is_archive = true;
            $wp_query->query_vars['product_tag'] = $matches[1];
            $wp_query->query_vars['paged'] = $matches[2];
        }
    }

    // Check for post tag pagination in custom URLs
 
 $post_tag_base = get_option('tag_base');

// If empty, WordPress uses 'tag' as default
if (empty($post_tag_base)) {
    $post_tag_base = 'tag';
}

if (preg_match('#/' . preg_quote($post_tag_base, '#') . '/([^/]+)/page/([0-9]+)/?#', $request, $matches)) {
    $term = get_term_by('slug', $matches[1], 'post_tag');
    if ($term && !is_wp_error($term)) {
        global $wp_query;

        $wp_query->is_404 = false;
        $wp_query->is_tag = true;      // mark as tag archive
        $wp_query->is_archive = true;
        $wp_query->queried_object = $term;
        $wp_query->query_vars['tag'] = $matches[1];
        $wp_query->query_vars['paged'] = $matches[2];
    }
}

// Get the product_brand taxonomy object
$btaxonomy = get_taxonomy('product_brand');

if ( $btaxonomy && ! empty($btaxonomy->rewrite['slug']) ) {
    $brand_base = $btaxonomy->rewrite['slug']; // e.g. 'product-brand'
} else {
    $brand_base = 'product-brand'; // fallback default
}

// Clean it up
$brand_base = trim($brand_base, '/');

// Match URLs like /brand/nike/page/3/
if (preg_match('#/' . preg_quote($brand_base, '#') . '/([^/]+)/page/([0-9]+)/?#', $request, $matches)) {
    $term = get_term_by('slug', $matches[1], 'product_brand');
    if ($term && !is_wp_error($term)) {
        global $wp_query;

        $wp_query->is_404 = false;
        $wp_query->is_archive = true;
        $wp_query->is_tax = true; // explicitly mark taxonomy archive
        $wp_query->queried_object = $term;

        // Inject query vars
        $wp_query->query_vars['product_brand'] = $matches[1];
        $wp_query->query_vars['paged'] = (int) $matches[2];
    }
}
    }
} 

 add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
    $more_param = esc_attr(get_option('pagimore_more_url_param', 'more'));
    if ( strpos( $requested_url, $more_param ) !== false ) {
        return false; // skip canonical redirect for /more/ links
    }
    return $redirect_url;
}, 10, 2 );

// Hide /more/ from search index

add_action( 'wp_head', function() {
    global $wp;

    $load_more_slug = esc_attr(get_option('pagimore_more_url_param', 'more'));

    // Check if the URL contains /$load_more_slug/
    if ( strpos( $wp->request, "/{$load_more_slug}/" ) !== false || preg_match("#/{$load_more_slug}/([0-9]+)/?#", $wp->request) ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
});

?>
