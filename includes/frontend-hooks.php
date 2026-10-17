<?php
if ( ! defined( 'ABSPATH' ) ) 
    exit; 
// Exit if accessed directly
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
        $vars[] = 'cubeabf_filters'; 
         $vars[] = 'cubeabf_more';
    return $vars;
    }
    
     private function add_rewrite_rules() {

 $more_param = get_option('cubeabf_more_url_param', 'after');
$more_param_quoted = preg_quote($more_param, '#');
 
     // -------------------------------
    // 1. WooCommerce Taxonomies
    // -------------------------------

    // Get WooCommerce permalinks
    
$wooperma = get_option('woocommerce_permalinks');
$product_categ_base = trim($wooperma['category_base'] ?? 'product-category', '/');

// 1) Product Category root archive (no pagination, no more)
add_rewrite_rule(
    '^' . preg_quote($product_categ_base, '#') . '/([^/]+)/?$',
    'index.php?product_cat=$matches[1]',
    'top'
);

// 2) Pagination
add_rewrite_rule(
    '^' . preg_quote($product_categ_base, '#') . '/([^/]+)/page/([0-9]+)/?$',
    'index.php?product_cat=$matches[1]&paged=$matches[2]',
    'top'
);

// 3) Pagination + /more/ param
add_rewrite_rule(
    '^' . preg_quote($product_categ_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
    'index.php?product_cat=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
    'top'
);

// 1) Hierarchical pagination + more  (MOST specific first)
add_rewrite_rule(
  '^' . preg_quote($product_categ_base, '#') . '/(.+)/page/([0-9]+)/' . $more_param_quoted . '/([0-9]+)/?$',
  'index.php?product_cat=$matches[1]&paged=$matches[2]&cubeabf_more=$matches[3]',
  'top'
);

// 2) Hierarchical pagination
add_rewrite_rule(
  '^' . preg_quote($product_categ_base, '#') . '/(.+)/page/([0-9]+)/?$',
  'index.php?product_cat=$matches[1]&paged=$matches[2]',
  'top'
);

// 3) Hierarchical root (LEAST specific last)
add_rewrite_rule(
  '^' . preg_quote($product_categ_base, '#') . '/(.+)/?$',
  'index.php?product_cat=$matches[1]',
  'top'
);

    // Product Tags
    if (function_exists('wc_get_permalink_structure')) {
        $wc_permalinks = wc_get_permalink_structure();
        $tag_base = !empty($wc_permalinks['tag_base']) ? trim($wc_permalinks['tag_base'], '/') : 'product-tag';
    } else {
        $tag_base = 'product-tag';
    }

    add_rewrite_rule(
        '^' . preg_quote($tag_base, '#') . '/([^/]+)/?$',
        'index.php?product_tag=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^' . preg_quote($tag_base, '#') . '/([^/]+)/page/([0-9]+)/?$',
        'index.php?product_tag=$matches[1]&paged=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^' . preg_quote($tag_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?product_tag=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );

    // Product Brand
   $brand_taxonomy   = get_taxonomy('product_brand');
$brand_base = ($brand_taxonomy && !empty($brand_taxonomy->rewrite['slug']))
    ? trim($brand_taxonomy->rewrite['slug'], '/')
    : 'product_brand';
 
$brand_base_quoted = preg_quote($brand_base, '#');
     
   

    add_rewrite_rule(
        '^' . preg_quote($brand_base, '#') . '/([^/]+)/?$',
        'index.php?product_brand=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^' . preg_quote($brand_base, '#') . '/([^/]+)/page/([0-9]+)/?$',
        'index.php?product_brand=$matches[1]&paged=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^' . preg_quote($brand_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?product_brand=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );

 // Product Brand (supports hierarchical URLs by taking the LAST slug)

// 1) Hierarchical pagination + more
add_rewrite_rule(
  '^' . $brand_base_quoted . '/(.+)/page/([0-9]+)/' . $more_param_quoted . '/([0-9]+)/?$',
  'index.php?taxonomy=product_brand&term=$matches[1]&paged=$matches[2]&cubeabf_more=$matches[3]',
  'top'
);

// 2) Hierarchical pagination
add_rewrite_rule(
  '^' . $brand_base_quoted . '/(.+)/page/([0-9]+)/?$',
  'index.php?taxonomy=product_brand&term=$matches[1]&paged=$matches[2]',
  'top'
);

// 3) Hierarchical root
add_rewrite_rule(
  '^' . $brand_base_quoted . '/(.+)/?$',
  'index.php?taxonomy=product_brand&term=$matches[1]',
  'top'
);


// Filters

$filter_segment =
    '(?:' .
      '[a-z0-9_]+_range=[^/]+' .
      '|' .
      '(?:' .
        'product_cat|product_tag|product_brand|category|post_tag|rating' .
        '|pa_[A-Za-z0-9_-]+' .
        '|meta_[A-Za-z0-9_-]+' .
      ')-[^/]+' .
    ')';


// Pick an anchor page for root filter URLs (front page or posts page)
$show_on_front = get_option('show_on_front'); // 'posts' or 'page'
$front_id = (int) get_option('page_on_front');
$posts_id = (int) get_option('page_for_posts');

$anchor_page_id = 0;
if ($show_on_front === 'page' && $front_id > 0) {
    $anchor_page_id = $front_id;
} elseif ($show_on_front === 'page' && $posts_id > 0) {
    $anchor_page_id = $posts_id;
}


// Anchor page path (e.g. "sample-page" or "shop/filters")
$anchor_path = '';
if ($anchor_page_id > 0) {
    $anchor_path = trim(get_page_uri($anchor_page_id), '/'); // "sample-page"
}

// Build targets
$root_target = $anchor_page_id > 0
    ? 'index.php?page_id=' . $anchor_page_id . '&cubeabf_filters=$matches[1]'
    : 'index.php?cubeabf_filters=$matches[1]';

$root_target_paged = $anchor_page_id > 0
    ? 'index.php?page_id=' . $anchor_page_id . '&cubeabf_filters=$matches[1]&paged=$matches[2]'
    : 'index.php?cubeabf_filters=$matches[1]&paged=$matches[2]';

$root_target_more = $anchor_page_id > 0
    ? 'index.php?page_id=' . $anchor_page_id . '&cubeabf_filters=$matches[1]&paged=$matches[2]&cubeabf_more=$matches[3]'
    : 'index.php?cubeabf_filters=$matches[1]&paged=$matches[2]&cubeabf_more=$matches[3]';

// -------------------------------
// 2. Filters (ROOT)
// -------------------------------
add_rewrite_rule(
  '^(' . $filter_segment . '(?:/' . $filter_segment . ')*)/?$',
  $root_target,
  'top'
);

add_rewrite_rule(
  '^(' . $filter_segment . '(?:/' . $filter_segment . ')*)/page/([0-9]+)/?$',
  $root_target_paged,
  'top'
);

add_rewrite_rule(
  '^(' . $filter_segment . '(?:/' . $filter_segment . ')*)/page/([0-9]+)/' . $more_param_quoted . '/([0-9]+)/?$',
  $root_target_more,
  'top'
);
 

    // The main cause the filters on such pages - /sample-page/ -don't go to 404, must be 'bottom'

    add_rewrite_rule( '^(.+?)/(' . $filter_segment . '(?:/' . $filter_segment . ')*)/?$', 'index.php?pagename=$matches[1]&cubeabf_filters=$matches[2]', 'bottom' );

    // WordPress Post Tags
    $post_tag_base = get_option('tag_base', 'tag');
    add_rewrite_rule(
        '^' . preg_quote($post_tag_base, '#') . '/([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?tag=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );

    // -------------------------------
    // 3. Search URLs
    // -------------------------------
    add_rewrite_rule(
        "search/([^/]+)/page/([0-9]+)/{$more_param}/([0-9]+)/?$",
        'index.php?s=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );
    add_rewrite_rule(
        "search/([^/]+)/page/([0-9]+)/?$",
        'index.php?s=$matches[1]&paged=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        "search/([^/]+)/{$more_param}/([0-9]+)/?$",
        'index.php?s=$matches[1]&' . $more_param . '=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        'search/([^/]+)/?$',
        'index.php?s=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        "search/([^/]+)/page/1/?$",
        'index.php?s=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        "search/([^/]+)/page/1/{$more_param}/([0-9]+)/?$",
        'index.php?s=$matches[1]',
        'top'
    );
    
    

    // -------------------------------
    // 4. Base pages fallback
    // -------------------------------
    // Base page only
    add_rewrite_rule(
        '^([^/]+)/?$',
        'index.php?pagename=$matches[1]',
        'top'
    );

    // Base + pagination
    add_rewrite_rule(
        '^([^/]+)/page/([0-9]+)/?$',
        'index.php?pagename=$matches[1]&paged=$matches[2]',
        'top'
    );

    // Base + pagination + more
    add_rewrite_rule(
        '^([^/]+)/page/([0-9]+)/' . preg_quote($more_param, '#') . '/([0-9]+)/?$',
        'index.php?pagename=$matches[1]&paged=$matches[2]&' . $more_param . '=$matches[3]',
        'top'
    );

    // Front page pagination + more
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


// The part of the main cause the filters on such pages - /sample-page/ - don't go to 404

 add_action('init', function () {

    $more_param = get_option('cubeabf_more_url_param', 'after');
    $more_param_quoted = preg_quote($more_param, '#');

    $filter_segment =
        '(?:' .
          '[a-z0-9_]+_range=[^/]+' .
          '|' .
          '(?:' .
            'product_cat|product_tag|product_brand|category|post_tag|rating' .
            '|pa_[A-Za-z0-9_-]+' .
            '|meta_[A-Za-z0-9_-]+' .
          ')-[^/]+' .
        ')';

    add_filter('rewrite_rules_array', function ($rules) use ($filter_segment, $more_param_quoted) {

        $new = [];

        // /{pagename}/{filters}/
        $new['^(.+?)/(' . $filter_segment . '(?:/' . $filter_segment . ')*)/?$']
            = 'index.php?pagename=$matches[1]&cubeabf_filters=$matches[2]';

        // /{pagename}/{filters}/page/{n}/
        $new['^(.+?)/(' . $filter_segment . '(?:/' . $filter_segment . ')*)/page/([0-9]+)/?$']
            = 'index.php?pagename=$matches[1]&cubeabf_filters=$matches[2]&paged=$matches[3]';

        // /{pagename}/{filters}/page/{n}/{more_param}/{m}/
        $new['^(.+?)/(' . $filter_segment . '(?:/' . $filter_segment . ')*)/page/([0-9]+)/' . $more_param_quoted . '/([0-9]+)/?$']
            = 'index.php?pagename=$matches[1]&cubeabf_filters=$matches[2]&paged=$matches[3]&cubeabf_more=$matches[4]';

        return $new + $rules; // prepend so it wins
    }, 1);

}, 20);



 add_action('parse_request', function($wp){
    if (isset($wp->query_vars['taxonomy'], $wp->query_vars['term']) && $wp->query_vars['taxonomy'] === 'product_brand') {
        $term = trim($wp->query_vars['term'], '/');
        if (strpos($term, '/') !== false) {
            $parts = array_values(array_filter(explode('/', $term)));
            $wp->query_vars['term'] = end($parts); // last slug only
        }
    }
}, 0);

 
add_action('template_redirect', function () {
    $filters_raw = get_query_var('cubeabf_filters', '');
    if (!empty($filters_raw)) {
        global $wp_query;
        $wp_query->is_404 = false;
        status_header(200);
        // Optional: avoid canonical redirect issues on custom URLs
        remove_action('template_redirect', 'redirect_canonical');
    }
}, 1);



add_action( 'parse_request', function( $wp ) {
    if ( isset( $wp->query_vars['cubeabf_filters'] ) ) {
        $slug = trim( $wp->query_vars['cubeabf_filters'], '/' );
        if ( empty( $slug ) ) {
            return;
        }
        
        $path_parts = explode( '/', $slug );
        
        // Find the longest matching page prefix
        $page_found = false;
        for ( $i = count( $path_parts ); $i >= 1; $i-- ) {
            $potential_path = implode( '/', array_slice( $path_parts, 0, $i ) );
            $page = get_page_by_path( $potential_path );
            if ( $page ) {
                $wp->query_vars['pagename'] = $potential_path;
                $remaining_index = $i;
                $remaining = implode( '/', array_slice( $path_parts, $i ) );
                if ( ! empty( $remaining ) ) {
                    $wp->query_vars['cubeabf_filters'] = $remaining;
                } else {
                    unset( $wp->query_vars['cubeabf_filters'] );
                }
                $page_found = true;
                break;
            }
        }
        
        // If no page prefix found, treat full slug as filter (no change)
        if ( ! $page_found ) {
            // Optionally unset pagename if it was set incorrectly, but usually not needed
        }
    }

    
} );

add_filter('pre_handle_404', function($preempt, $wp_query) {

    if (
        !is_admin() &&
        $wp_query->is_main_query() &&
        (
            $wp_query->is_post_type_archive('product') ||
            $wp_query->is_shop()
        )
    ) {
        $paged = (int) $wp_query->get('paged');

        if ($paged > 1) {
            return true;
        }
    }

    return $preempt;

}, 10, 2);
 
add_action('template_redirect', function() {
    if (is_search() && !is_admin() && !wp_doing_ajax()) {
        $more_param = esc_attr(get_option('cubeabf_more_url_param', 'after'));
        $s = get_query_var('s');

        // Only redirect if the current URL has ?s= in the query string
        if ($s && isset($_GET['s'])) {
            $redirect = home_url("/search/" . rawurlencode($s) . "/");

            $paged = get_query_var('paged');
            $more  = get_query_var($more_param);

            if ($paged && $paged > 1) {
                $redirect .= "page/{$paged}/";
            }
            if ($more) {
                $redirect .= "{$more_param}/{$more}/";
            }

            // Preserve any other GET parameters
            $other_params = $_GET;
            unset($other_params['s']); // already in path
            if ($other_params) {
                $redirect .= '?' . http_build_query($other_params);
            }

            // Only redirect if we're not already on the target URL
            if (untrailingslashit($_SERVER['REQUEST_URI']) !== untrailingslashit(parse_url($redirect, PHP_URL_PATH))) {
                wp_redirect($redirect, 301);
                exit;
            }
        }
    }
});

 
 

add_action('template_redirect', 'handle_ports_parameter_templates');
function handle_ports_parameter_templates() {
    $more_param = esc_attr(get_option('cubeabf_more_url_param', 'after'));
    $ports = get_query_var($more_param);
   

    if (!$ports) return;
    
     global $wp_query, $post;
    
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
    } else if ($ports && get_query_var('product_brand')) {
        $wp_query->is_404 = false;
        $wp_query->is_tax = true;
        $wp_query->is_archive = true;
        $wp_query->queried_object = get_term_by('slug', get_query_var('product_brand'), 'product_brand');
    } else {
        // It's a custom paginated page with ports parameter
        $wp_query->is_page = true;
        $wp_query->is_singular = false;
    }
}



 
 
// Hook into template_include filter with high priority to override template loading.
 
 
 add_filter('template_include', 'cubeabf_keep_assigned_templates_on_more', 99);

function cubeabf_keep_assigned_templates_on_more( $template ) {
    global $wp_query;

    $more_param = get_option('cubeabf_more_url_param', 'after'); // keep your custom slug (default "more")

    if ( is_tax() || is_category() || is_tag() ) {
    return $template;
}

   // 1. Проверяем напрямую из $_GET, если ЧПУ еще не настроено
    $filters_raw = get_query_var('cubeabf_filters') ?: ($_GET['cubeabf_filters'] ?? '');
    
    $filters = trim($filters_raw, "/");

    if ( !empty($filters) ) {
        // Если фильтры есть, возвращаем текущий шаблон
        return $template;
    }

     

    $uri_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    if ( ! $uri_path ) return $template;

    // Only act on URLs that end with /more/{n}/
    if ( ! preg_match('#/(?:' . preg_quote($more_param, '#') . ')/(\d+)/?$#', $uri_path, $m) ) {
        return $template;
    }

    // If WP already resolved the request fine, DON'T touch anything (keeps assigned templates)
    if ( ! is_404() ) {
        return $template;
    }

    // Strip /more/{n}/ and (optionally) trailing /page/{n}/ to find the base URL that WP understands
    $base_path = preg_replace('#/(?:' . preg_quote($more_param, '#') . ')/\d+/?$#', '/', $uri_path);
    $base_path = preg_replace('#/page/\d+/?$#', '/', $base_path);

    $base_url = home_url( $base_path );
    $post_id  = url_to_postid( $base_url );

    // We will serve a real template, so kill 404
    $wp_query->is_404 = false;
    status_header(200);

    // If base resolves to a PAGE, use the page's assigned template if any
    if ( $post_id ) {
        $post = get_post( $post_id );

        if ( $post && $post->post_type === 'page' ) {
            $assigned = get_page_template_slug( $post_id );
            if ( $assigned ) {
                $found = locate_template( $assigned );
                if ( $found ) return $found;
            }

            // Otherwise, let WP pick the normal page template (page-{slug}.php, page-{id}.php, page.php, etc.)
            $page_tpl = get_query_template('page');
            if ( $page_tpl ) return $page_tpl;
        }

        // If base is some other singular (product/post/custom post type), use WP's normal singular template
        $singular_tpl = get_query_template('singular');
        if ( $singular_tpl ) return $singular_tpl;
    }

    // If base URL doesn't map to a post/page, fall back to WP normal templates (front-page/home/index)
    $front = get_query_template('frontpage'); // front-page.php
    if ( $front ) return $front;

    $home = get_query_template('home'); // home.php
    if ( $home ) return $home;

    $index = get_query_template('index'); // index.php
    return $index ? $index : $template;
}

 
 
 

add_action('wp_head', function() {
    ?>
    <script>
    (function() {
        function setScreenWidthCookie() {
            var width = window.innerWidth;
            var cookie = document.cookie.match(/(?:^|; )screen_width=(\d+)/);
            if (!cookie || parseInt(cookie[1]) !== width) {
                document.cookie = "screen_width=" + width + "; path=/";
                // Reload once to let PHP pick up the new width
                if (!sessionStorage.getItem("width_sent")) {
                    sessionStorage.setItem("width_sent", "1");
                    location.reload();
                }
            }
        }

        // Run on initial load
        setScreenWidthCookie();

        // Also update cookie if user resizes window
        window.addEventListener('resize', function() {
            setScreenWidthCookie();
        });
    })();
    </script>
    <?php
});
 

// Global Config Code

/**
 * Converts config keys to real taxonomies.
 * Supported keys:
 * - post_cat => category
 * - post_tag => post_tag
 * - product_cat => product_cat
 * - product_tag => product_tag
 * - product_brand => product_brand
 * - pa_color => pa_color (any pa_*)
 * - pa => __pa_all__ (only for pa_all style, not pa-xxx)
 */
function cubeabf_scope_key_to_taxonomy_config(string $key): string {
    if ($key === 'post_cat')      return 'category';
    if ($key === 'post_tag')      return 'post_tag';
    if ($key === 'product_cat')   return 'product_cat';
    if ($key === 'product_tag')   return 'product_tag';
    if ($key === 'product_brand') return 'product_brand';

    if ($key === 'pa') return '__pa_all__';

    // allow any pa_*
    if (strpos($key, 'pa_') === 0) return $key;

    return '';
}

 function cubeabf_parse_scope_tokens_config(string $csv): array {
    $out = [
        'ids'   => [],      // id-123 OR post-123
        'stars' => [],      // star-1..5 (UI only)

        'tax_ids'   => [],  // [taxonomy => [term_ids]]
        'tax_slugs' => [],  // [taxonomy => [slugs]]
        'tax_all'   => [],  // [taxonomy => true]
        'pa_all'    => false,

         
        'meta'      => [],   // meta_key => [values]
        'meta_all'  => false,
        'meta_keys' => [],   // whole meta field regardless of value     // [meta_key => [values]] (strings)
        'rating' => [],     // [1..5] selected ratings (ints) (use in config scope)
        'rating_mode' => 'bucket', // optional default, can override elsewhere
    ];

    $csv = trim($csv);
    if ($csv === '') return $out;

    $items = array_map('trim', explode(',', strtolower($csv)));
    $items = array_values(array_filter($items, fn($x) => $x !== ''));

    foreach ($items as $token) {

        // id-123
        if (preg_match('/^id-(\d+)$/', $token, $m)) {
            $out['ids'][] = (int)$m[1];
            continue;
        }

        // post-123
        if (preg_match('/^post-(\d+)$/', $token, $m)) {
            $out['ids'][] = (int)$m[1];
            continue;
        }
 
        // pa_all
        if ($token === 'pa_all') {
            $out['pa_all'] = true;
            continue;
        }

        // taxonomy_all (product_cat_all, post_tag_all, pa_color_all, etc)
        if (preg_match('/^([a-z0-9_]+)_all$/', $token, $m)) {
            $key = sanitize_key($m[1]);
            $tax = cubeabf_scope_key_to_taxonomy_config($key);

            if ($tax === '__pa_all__') {
                $out['pa_all'] = true;
            } elseif ($tax) {
                $out['tax_all'][$tax] = true;
            }
            continue;
        }

                // meta_all
if ($token === 'meta_all') {
    $out['meta_all'] = true;
    continue;
}

// meta_fabric_of_the_pro
if (
    (strpos($token, 'meta_') === 0 || strpos($token, 'meta-') === 0)
    && strpos($token, '=') === false
) {

    $meta_key = str_replace('-', '_', substr($token, 5));
    $meta_key = preg_replace('/[^a-z0-9_]/', '', $meta_key);

    if ($meta_key !== '') {
        $out['meta_keys'][$meta_key] = true;
    }

    continue;
}

        // ✅ key=value (supports dashed pa_ taxonomies)
        if (preg_match('/^([a-z0-9_-]+)=(.+)$/', $token, $m)) {



            $raw_key = strtolower(trim($m[1]));
            $raw_key = preg_replace('/[^a-z0-9_-]/', '', $raw_key);
            $value   = trim($m[2]);
            if ($value === '') continue;

            // ✅ META: meta_fabric_of_the_pro=wool  OR meta-fabric_of_the_pro=wool (both ok)
            // We'll accept keys starting with "meta_" or "meta-"
            if (strpos($raw_key, 'meta_') === 0 || strpos($raw_key, 'meta-') === 0) {
                $meta_key = str_replace('-', '_', substr($raw_key, 5)); // after meta_
                $meta_key = preg_replace('/[^a-z0-9_]/', '', $meta_key);
                if ($meta_key !== '') {
                    $out['meta'][$meta_key][] = sanitize_text_field($value);
                }
                continue;
            }

            // rating=3 or rating=3,4 or rating=3|4 etc (collect all 1..5)
if ($raw_key === 'rating') {
    preg_match_all('/\b([1-5])\b/', $value, $mm);
    if (!empty($mm[1])) {
        foreach ($mm[1] as $d) {
            $out['rating'][] = (int)$d;
        }
    }
    continue;
}

            // ✅ TAXONOMY: product_cat=26, pa_super-nov-pal=abrikos, etc
            $tax = taxonomy_exists($raw_key) ? $raw_key : cubeabf_scope_key_to_taxonomy_config($raw_key);
            if (!$tax) continue;

            if (ctype_digit($value)) $out['tax_ids'][$tax][] = (int)$value;
            else                     $out['tax_slugs'][$tax][] = sanitize_title($value);
            continue;
        }
    }

    // de-dupe
    $out['ids']   = array_values(array_unique(array_filter(array_map('absint', $out['ids']))));
    $out['stars'] = array_values(array_unique(array_filter(array_map('absint', $out['stars']))));
    $out['rating']= array_values(array_unique(array_filter(array_map('absint', $out['rating']))));

    foreach (['tax_ids','tax_slugs'] as $k) {
        foreach ($out[$k] as $tax => $vals) {
            if ($k === 'tax_ids') {
                $vals = array_values(array_unique(array_filter(array_map('absint', (array)$vals))));
            } else {
                $vals = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$vals))));
            }
            $out[$k][$tax] = $vals;
        }
    }

    foreach ($out['meta'] as $mk => $vals) {
        $vals = array_values(array_unique(array_filter(array_map('sanitize_text_field', (array)$vals))));
        if ($vals) $out['meta'][$mk] = $vals;
        else unset($out['meta'][$mk]);
    }

    return $out;
}

function cubeabf_get_scope_from_request_config(string $post_type): array {
    $scope_io = $_POST['scope_config'] ?? null;

    if (is_string($scope_io)) {
        $scope_io = json_decode(wp_unslash($scope_io), true);
    }

    $include_csv = is_array($scope_io) ? (string)($scope_io['include'] ?? '') : '';
    $exclude_csv = is_array($scope_io) ? (string)($scope_io['exclude'] ?? '') : '';

    return [
        'include' => cubeabf_parse_scope_tokens_config($include_csv),
        'exclude' => cubeabf_parse_scope_tokens_config($exclude_csv),
    ];
}


function cubeabf_build_rating_bucket_meta_group(array $vals, string $relation = 'OR'): array {
    $vals = array_values(array_unique(array_filter(array_map('absint', $vals))));
    $vals = array_values(array_filter($vals, fn($n) => $n >= 1 && $n <= 5));
    if (!$vals) return [];

    $relation = ($relation === 'AND') ? 'AND' : 'OR';

    $group = ['relation' => $relation];

    foreach ($vals as $r) {
        $min   = (float)$r;
        $upper = ($r === 5) ? 5.01 : (float)($r + 1);

        // bucket r..r+1
        $group[] = [
            'relation' => 'AND',
            ['key' => '_wc_average_rating', 'value' => $min,   'type' => 'NUMERIC', 'compare' => '>='],
            ['key' => '_wc_average_rating', 'value' => $upper, 'type' => 'NUMERIC', 'compare' => '<' ],
        ];
    }

    return $group;
}



function cubeabf_apply_post_scope_to_args_config(array $args, array $scope): array {
    $inc_ids = $scope['include']['ids'] ?? [];
    $exc_ids = $scope['exclude']['ids'] ?? [];

    $inc_meta_keys = $scope['include']['meta_keys'] ?? [];
$exc_meta_keys = $scope['exclude']['meta_keys'] ?? [];

$inc_meta_all = !empty($scope['include']['meta_all']);
$exc_meta_all = !empty($scope['exclude']['meta_all']);

    $inc_ids = array_values(array_unique(array_filter(array_map('absint', (array)$inc_ids))));
    $exc_ids = array_values(array_unique(array_filter(array_map('absint', (array)$exc_ids))));

    if ($inc_ids) {
        if (!empty($args['post__in'])) {
            $args['post__in'] = array_values(array_intersect(array_map('absint', (array)$args['post__in']), $inc_ids));
        } else {
            $args['post__in'] = $inc_ids;
        }
    }

    if ($exc_ids) {
        $existing = !empty($args['post__not_in']) ? (array)$args['post__not_in'] : [];
        $args['post__not_in'] = array_values(array_unique(array_merge(array_map('absint', $existing), $exc_ids)));
    }

    if (!empty($args['post__in']) && !empty($args['post__not_in'])) {
        $args['post__in'] = array_values(array_diff($args['post__in'], $args['post__not_in']));
    }

    // New

    $inc_meta = $scope['include']['meta'] ?? [];
$exc_meta = $scope['exclude']['meta'] ?? [];

if ($inc_meta || $inc_meta_keys) {
    $mq = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];
    if (isset($mq['relation'])) unset($mq['relation']);
    $mq = array_values($mq);

    foreach ($inc_meta_keys as $k => $_) {
    $mq[] = [
        'key'     => $k,
        'compare' => 'EXISTS',
    ];
}

    // include meta: require key has one of values
    foreach ($inc_meta as $k => $vals) {
        $mq[] = [
            'key'     => $k,
            'value'   => $vals,
            'compare' => 'IN',
        ];
    }
 

    $args['meta_query'] = array_merge(['relation' => 'AND'], $mq);
}

 

if ($exc_meta || $exc_meta_keys) {
    $mq = $args['meta_query'] ?? ['relation' => 'AND'];
    if (!is_array($mq)) $mq = ['relation' => 'AND'];
    if (empty($mq['relation'])) $mq = array_merge(['relation' => 'AND'], $mq);

    foreach ($exc_meta_keys as $meta_key => $_) {

    $mq[] = [
        'key'     => $meta_key,
        'compare' => 'NOT EXISTS',
    ];
}

    foreach ($exc_meta as $meta_key => $vals) {
        $vals = array_values(array_unique(array_filter(array_map('sanitize_text_field', (array)$vals))));
        if (!$meta_key || !$vals) continue;

        // ✅ exclude only those that match (keep NOT EXISTS + NOT IN)
        $mq[] = [
            'relation' => 'OR',
            [
                'key'     => $meta_key,
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => $meta_key,
                'value'   => $vals,
                'compare' => 'NOT IN',
            ],
        ];
    }

    $args['meta_query'] = $mq;
}

 $inc_rating = $scope['include']['rating'] ?? [];
$exc_rating = $scope['exclude']['rating'] ?? [];

// Ensure meta_query exists AND is top-level AND
$mq = $args['meta_query'] ?? [];
if (!is_array($mq) || empty($mq)) {
    $mq = ['relation' => 'AND'];
} elseif (empty($mq['relation'])) {
    $mq = array_merge(['relation' => 'AND'], $mq);
}

// --------------------------
// INCLUDE rating buckets
// rating=3,rating=4 => (bucket3 OR bucket4)
// --------------------------
if (!empty($inc_rating)) {
    $group = cubeabf_build_rating_bucket_meta_group((array)$inc_rating, 'OR');
    if ($group) {
        $mq[] = $group;
    }
}

// --------------------------
// EXCLUDE rating buckets
// We cannot express "NOT (bucket3 OR bucket4)" cleanly,
// so we add one NOT BETWEEN per bucket (AND across them).
// --------------------------
 

$mq = $args['meta_query'] ?? [];
if (!is_array($mq) || empty($mq)) $mq = ['relation' => 'AND'];
if (empty($mq['relation'])) $mq = array_merge(['relation' => 'AND'], $mq);

$vals = array_values(array_unique(array_filter(array_map('absint', (array)$exc_rating))));
$vals = array_values(array_filter($vals, fn($n) => $n >= 1 && $n <= 5));

foreach ($vals as $r) {

    // ✅ your bucket boundaries (same as your shortcode counting)
    $low  = (float)$r - 0.5;
    $high = ($r === 5) ? 5.0 : ((float)$r + 0.49);

    // ✅ keep posts that have NO rating meta
    $mq[] = [
        'relation' => 'OR',
        [
            'key'     => '_wc_average_rating',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key'     => '_wc_average_rating',
            'value'   => [$low, $high],
            'type'    => 'NUMERIC',
            'compare' => 'NOT BETWEEN',
        ],
    ];
}

$args['meta_query'] = $mq;

    return $args;
}


 function cubeabf_apply_scope_tax_to_args_config(array $args, array $scope): array {
    $inc = $scope['include'] ?? [];
    $exc = $scope['exclude'] ?? [];

    // existing tax_query (keep it as-is!)
    $existing_tax = (!empty($args['tax_query']) && is_array($args['tax_query'])) ? $args['tax_query'] : null;

    // collect SCOPE clauses only (do NOT start from $args['tax_query'])
    $scope_clauses = [];

    $add = function(array $clause) use (&$scope_clauses) {
        $scope_clauses[] = $clause;
    };

    // taxonomy_all
    foreach ((array)($inc['tax_all'] ?? []) as $tax => $_true) {
        $tax = sanitize_key($tax);
        if ($tax) $add(['taxonomy' => $tax, 'operator' => 'EXISTS']);
    }
    foreach ((array)($exc['tax_all'] ?? []) as $tax => $_true) {
        $tax = sanitize_key($tax);
        if ($tax) $add(['taxonomy' => $tax, 'operator' => 'NOT EXISTS']);
    }

    // term IDs
    foreach ((array)($inc['tax_ids'] ?? []) as $tax => $ids) {
        $tax = sanitize_key($tax);
        $ids = array_values(array_unique(array_filter(array_map('absint', (array)$ids))));
        if ($tax && $ids) $add(['taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids, 'operator' => 'IN']);
    }
    foreach ((array)($exc['tax_ids'] ?? []) as $tax => $ids) {
        $tax = sanitize_key($tax);
        $ids = array_values(array_unique(array_filter(array_map('absint', (array)$ids))));
        if ($tax && $ids) $add(['taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids, 'operator' => 'NOT IN']);
    }

    // slugs
    foreach ((array)($inc['tax_slugs'] ?? []) as $tax => $slugs) {
        $tax = sanitize_key($tax);
        $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
        if ($tax && $slugs) $add(['taxonomy' => $tax, 'field' => 'slug', 'terms' => $slugs, 'operator' => 'IN']);
    }
    foreach ((array)($exc['tax_slugs'] ?? []) as $tax => $slugs) {
        $tax = sanitize_key($tax);
        $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
        if ($tax && $slugs) $add(['taxonomy' => $tax, 'field' => 'slug', 'terms' => $slugs, 'operator' => 'NOT IN']);
    }

    // pa_all flags (posts_where handles it)
    if (!empty($inc['pa_all'])) {
        $args['cubeabf_pa_all'] = 'include';
        $args['cubeabf_scope_config_active'] = 1;
    }
    if (!empty($exc['pa_all'])) {
        $args['cubeabf_pa_all'] = 'exclude';
        $args['cubeabf_scope_config_active'] = 1;
    }

    // nothing to apply
    if (!$scope_clauses) {
        // error_log('SCOPE TAX clauses: none');
        return $args;
    }

    // ---------------------------------------
    // Split scope clauses: positives vs negatives
    // negatives MUST NOT be OR’ed
    // ---------------------------------------
    $pos = [];
    $neg = [];

    foreach ($scope_clauses as $c) {
        $op = strtoupper((string)($c['operator'] ?? 'IN'));
        if ($op === 'NOT IN' || $op === 'NOT EXISTS') $neg[] = $c;
        else $pos[] = $c; // IN / EXISTS
    }

    $groups = [];

    if ($neg) $groups[] = array_merge(['relation' => 'AND'], $neg);

    // scope “include” should normally restrict, so AND is default.
    // If you *really* want scope include to broaden, change to OR.
    if ($pos) $groups[] = array_merge(['relation' => 'AND'], $pos);

    $scope_group = (count($groups) === 1)
        ? $groups[0]
        : array_merge(['relation' => 'AND'], $groups);

    // ---------------------------------------
    // Merge with existing tax_query (keep existing as-is)
    // ---------------------------------------
    if ($existing_tax) {
        $args['tax_query'] = [
            'relation' => 'AND',
            $existing_tax,  // could be OR group — we do not touch it
            $scope_group,
        ];
    } else {
        $args['tax_query'] = $scope_group;
    }

    // Debug
    // error_log('SCOPE TAX clauses: ' . print_r($scope_clauses, true));
    // error_log('FINAL tax_query: ' . print_r($args['tax_query'], true));

    return $args;
}



add_action('pre_get_posts', function($q){
    
    if (is_admin() || !$q->is_main_query()) return;
$pp = (int) get_option('cubeabf_subbrands_ppp', 6);
    if ( $q->is_tax('product_brand') || $q->get('taxonomy') === 'product_brand' ) {
        $q->set('post_type', 'product');
        $q->set('posts_per_page', $pp); // match your shortcode setting

        // ensure include children if you need it
        $tax_query = (array) $q->get('tax_query');
        foreach ($tax_query as &$tq) {
            if (!empty($tq['taxonomy']) && $tq['taxonomy'] === 'product_brand') {
                $tq['include_children'] = true;
            }
        }
        $q->set('tax_query', $tax_query);
    }
}, 20);

  add_action( 'pre_get_posts', 'apply_home_shortcode_atts_to_query', 20 );
function apply_home_shortcode_atts_to_query( $query ) {
    // Only front-end + main query
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    
    // Only affect Home (front page or posts index)
    if ( ! ( is_home() || is_front_page() ) ) {
        return;
    }
    
    
    
    $atts = [];
    
    // PRIORITY: Try transient (set by shortcode)
    $transient_atts = get_transient( 'home_shortcode_atts' );
    if ( $transient_atts ) {
        $atts = $transient_atts;
        error_log( 'Using transient atts for home query: ' . print_r( $atts, true ) );  // Debug
    } else {
        // FALLBACK: Parse from content (static front page)
        $object_id = get_queried_object_id();
        if ( $object_id && $object_id !== 0 ) {  // Static page ID
            $page = get_post( $object_id );
            if ( $page && has_shortcode( $page->post_content, 'cubeabf_code' ) ) {
                $regex = get_shortcode_regex( [ 'cubeabf_code' ] );
                if ( preg_match_all( "/$regex/s", $page->post_content, $matches ) ) {
                    foreach ( $matches[0] as $k => $full ) {
                        if ( 'cubeabf_code' === $matches[2][ $k ] ) {
                            $atts = shortcode_parse_atts( $matches[3][ $k ] );
                            $atts['ptype'] = sanitize_text_field( $atts['ptype'] ?? '' );
                            $atts['pppage'] = intval( $atts['pppage'] ?? '' );
                            error_log( 'Parsed atts from content: ' . print_r( $atts, true ) );  // Debug
                            break;
                        }
                    }
                }
            }
        } else {
            // Posts index (latest posts): Hardcode or from option
            $atts = [  // Or get_option( 'home_shortcode_atts', [ 'ptype' => 'post', 'pppage' => 10 ] );
                'ptype' => 'product',    // Default; adjust
                'pppage' => 16,          // Default; adjust
            ];
            error_log( 'Using hardcoded atts for posts index home.' );  // Debug
        }
    }
    
    // Apply if valid atts
    if ( ! empty( $atts['ptype'] ) ) {
        $query->set( 'post_type', $atts['ptype'] );
    }
    if ( ! empty( $atts['pppage'] ) ) {
        $query->set( 'posts_per_page', $atts['pppage'] );
    }
    
    // Safety for custom types (e.g., prevent 404)
    if ( isset( $atts['ptype'] ) && $atts['ptype'] === 'product' ) {
        $query->set( 'post_status', 'publish' );
        $query->set( 'ignore_sticky_posts', 1 );
    }
    
    error_log( 'Final query vars applied - post_type: ' . $query->get( 'post_type' ) . ', posts_per_page: ' . $query->get( 'posts_per_page' ) );  // Debug
}

add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_main_query()) return;

   if ($query->is_search() && !is_admin() && $query->is_main_query()) {
        // AJAX: from $_POST
        if (wp_doing_ajax() && isset($_POST['per_page'])) {
            $per_page = absint($_POST['per_page']);
        }
        // Normal load: from $_GET
        elseif (isset($_GET['per_page'])) {
            $per_page = absint($_GET['per_page']);
        }
        // Fallback
        else {
            $per_page = 2;
        }

        $query->set('posts_per_page', $per_page);
    }
});

 

add_action('wp_enqueue_scripts', function() {
  

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

$enable_pagination = get_query_var('enable_pagination', 'true');
$enable_load_more = get_query_var('enable_load_more', 'true');

$cubeabf_wrapper_css = get_option('cubeabf_wrapper_css', 'ajax-pagination');
$cubeabf_pagi_load_more_css = get_option('cubeabf_pagi_load_more_css', 'load-cubeabf');
$cubeabf_pagi_loader_css = get_option('cubeabf_pagi_loader_css', 'page-loading');
$cubeabf_pagi_moreloader_css = get_option('cubeabf_pagi_moreloader_css', 'page-moreloading');
$cubeabf_pagi_btn_css = get_option('cubeabf_pagi_btn_css', 'pagination-btn');
global $wp_query;
$max_pages = ($wp_query instanceof WP_Query) ? (int)$wp_query->max_num_pages : 1;
$query_args = array();
    $query = new WP_Query($query_args);

  
 
  
        wp_enqueue_script('cubeabf-pagination', plugins_url('/assets/pagination.js', CUBEPAGI_PLUGIN_FILE), ['jquery'], CUBEPAGI_VERSION, true);

        wp_localize_script('cubeabf-pagination', 'cubeabf_ajax_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cubeabf_nonce'),
            'query_selector' => get_option('cubeabf_query_selector', 'post-list'),
            'max_pages' => $max_pages,
            'cubeabf_more_url_param' => esc_attr(get_option('cubeabf_more_url_param', 'after')),
            'product_cat'=> $current_product_cat_slug,
            'product_tag' => $current_product_tag_slug,
            'current_cat'   => $current_cat_slug,
            'zapisi_tag' => $current_tag_slug,
            'cat_base' => $category_base,
            'woo_cat_base' => $product_category_base,
            'woo_brand_slug' =>  $current_brand_slug,
            'query_selector' => esc_attr(get_option('cubeabf_query_selector', 'post-list')),
                    'wrapperCss'   => $cubeabf_wrapper_css,
        'loadMoreCss'  => $cubeabf_pagi_load_more_css,
        'loaderCss'    => $cubeabf_pagi_loader_css,
        'moreloaderCss'    => $cubeabf_pagi_moreloader_css,
        'pagiBtn' => $cubeabf_pagi_btn_css,
        'pagiPage' =>  get_option('cubeabf_pagi_page_css', 'product-pagi__page'),
         'page' => esc_html(get_option('cubeabf_title_page_name', 'Страница')),
        ]);
       

     
});

 function cubeabf_get_image_alt( $attachment_id = 0, $url = '', $fallback = '' ) {
    $alt = '';

    if ( $attachment_id ) {
        $alt = (string) get_post_meta( (int)$attachment_id, '_wp_attachment_image_alt', true );
    } elseif ( $url ) {
        $maybe_id = attachment_url_to_postid( $url );
        if ( $maybe_id ) {
            $alt = (string) get_post_meta( (int)$maybe_id, '_wp_attachment_image_alt', true );
        }
    }

    $alt = trim( wp_strip_all_tags( $alt ) );
    if ( $alt === '' ) $alt = (string) $fallback;

    return $alt;
}


function cubeabf_render_label_media( $url, $bg, $attachment_id = 0, $fallback_alt = '' ) {
    $url = $url ? esc_url($url) : '';
    if ( !$url ) return '';

    if ( $bg ) {
        // background-image doesn't use alt; consider adding aria-label for accessibility
        $aria = $fallback_alt !== '' ? ' aria-label="' . esc_attr($fallback_alt) . '"' : '';
        return '<span class="cubeabf-label-media"><span class="cubeabf-label-bg" style="background-image:url(' . $url . ');"' . $aria . '></span></span>';
    }

    $alt = cubeabf_get_image_alt( $attachment_id, $url, $fallback_alt );
    return '<span class="cubeabf-label-media"><img class="cubeabf-label-img" src="' . $url . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async"></span>';
}


 /**
 * Build optional image markup for a label, using TERM meta or explicit URL.
 *
 * Supports:
 *  img="thumbnail"                          -> term meta attachment ID -> image URL
 *  img="thumbnail,medium"                   -> term meta attachment ID -> image URL (custom size)
 *  img="url,/path/myimg.jpg"                -> <img>
 *  img="url,/path/myimg.jpg,bg"             -> background-image span
 *
 * NEW (recommended):
 *  img_map="blue|/img/blue.jpg,red|/img/red.jpg"      -> per-label URL mapping
 *  img_map_match="slug|name"                           -> how to match map keys (default: slug)
 *  img_map_mode="img|bg"                               -> render all mapped urls as <img> or bg (default: img)
 *
 * Notes:
 * - If img_map is present AND a match is found, it overrides img="url,..."
 * - If no map match is found, it falls back to normal img logic (thumbnail/url)
 *
  
 * @param string $img_attr
 * @param int    $term_id
 * @param array  $atts
 * @param object $term (optional but recommended)
 * @return string
 */
function cubeabf_build_term_img_markup( $img_attr, $term_id, $atts, $term = null ) {
    $img_attr = is_string($img_attr) ? trim($img_attr) : '';
    $img_map  = isset($atts['img_map']) ? trim((string)$atts['img_map']) : '';

    if ( $img_attr === '' && $img_map === '' ) return '';
    if ( $img_attr === 'false' ) return '';

    if ( ! $term && $term_id ) {
        $term = get_term( $term_id );
    }

    $size     = !empty($atts['img_size']) ? sanitize_key($atts['img_size']) : 'thumbnail';
    $meta_key = !empty($atts['img_meta']) ? sanitize_key($atts['img_meta']) : 'thumbnail_id';

    // NEW: global render mode for thumbnail branch (and optionally url branch if you want)
    // img_mode="img" or "bg" (default img)
    $img_mode = isset($atts['img_mode']) ? sanitize_key($atts['img_mode']) : 'img';

    $url = '';
    $bg  = false;

    /**
     * 1) Try img_map first (if provided)
     * Format (swapped separators):
     * img_map="Blue,/img/blue.jpg|Red,/img/red.jpg"
     */
    if ( $img_map !== '' && $term ) {
        $match_mode  = isset($atts['img_map_match']) ? sanitize_key($atts['img_map_match']) : 'slug'; // slug|name
        $render_mode = isset($atts['img_map_mode']) ? sanitize_key($atts['img_map_mode']) : 'img';   // img|bg

        $current_key = ( $match_mode === 'name' )
            ? mb_strtolower((string)($term->name ?? ''))
            : mb_strtolower((string)($term->slug ?? ''));

        $entries = array_filter(array_map('trim', explode('|', $img_map)));

        foreach ( $entries as $entry ) {
            $pair = array_map('trim', explode('::', $entry, 2)); // key,url
            if ( count($pair) !== 2 ) continue;

            $k = mb_strtolower($pair[0]);
            $u = $pair[1];

            if ( $k !== '' && $u !== '' && $k === $current_key ) {
                if ( strpos($u, '://') === false && $u[0] === '/' ) {
                    $u = home_url($u);
                }
                $url = $u;
                $bg  = ( $render_mode === 'bg' );
                break;
            }
        }
    }

    /**
     * 2) If img_map didn't match, fall back to img="..."
     */
    if ( ! $url && $img_attr !== '' ) {
        $parts = array_map('trim', explode(',', $img_attr));
        $mode  = strtolower($parts[0] ?? '');

        if ( $mode === 'thumbnail' ) {
            // optional: img="thumbnail,medium"
            if ( !empty($parts[1]) ) $size = sanitize_key($parts[1]);

           $meta_val = get_term_meta( $term_id, $meta_key, true );

// ACF might store ID, URL, or serialized array (array can come back as string sometimes)
$attachment_id = 0;

if ( is_numeric($meta_val) ) {
    // ACF return: ID
    $attachment_id = (int) $meta_val;
} elseif ( is_string($meta_val) && $meta_val !== '' ) {
    // ACF return: URL (or a non-empty string)
    if ( filter_var($meta_val, FILTER_VALIDATE_URL) ) {
        $url = $meta_val;
    }
} elseif ( is_array($meta_val) ) {
    // ACF return: array
    if ( !empty($meta_val['ID']) ) {
        $attachment_id = (int) $meta_val['ID'];
    } elseif ( !empty($meta_val['url']) ) {
        $url = $meta_val['url'];
    }
}

if ( $attachment_id ) {
    $url = wp_get_attachment_image_url( $attachment_id, $size );
}


            // NEW: apply img_mode to thumbnails
            // (only set if it hasn't already been set by img_map)
            if ( ! $bg ) {
                $bg = ( $img_mode === 'bg' );
            }
        }
        elseif ( $mode === 'url' ) {
            $raw = $parts[1] ?? '';
            if ( $raw !== '' ) {
                if ( strpos($raw, '://') === false && $raw[0] === '/' ) {
                    $raw = home_url($raw);
                }
                $url = $raw;
            }

            // Keep your existing explicit "bg" third param:
            if ( !empty($parts[2]) && strtolower($parts[2]) === 'bg' ) {
                $bg = true;
            } else {
                // OPTIONAL (recommended): allow img_mode to affect url mode too when no explicit bg param is provided
                // Comment out these 2 lines if you want url mode to be controlled ONLY by the third param.
                if ( ! $bg ) $bg = ( $img_mode === 'bg' );
            }
        }
        else {
            return '';
        }
    }

    $url = $url ? esc_url($url) : '';
    if ( !$url ) return '';

    if ( $bg ) {
        return '<span class="cubeabf-label-media"><span class="cubeabf-label-bg" style="background-image:url(' . $url . ');"></span></span>';
    }

   $fallback_alt = $term ? $term->name : '';
return cubeabf_render_label_media( $url, $bg, $attachment_id ?? 0, $fallback_alt );

}

// Incude and exclude logic

function cubeabf_parse_scope_tokens($csv) {
    $out = [
        'ids'   => [],
        'stars' => [],
        'tax'   => [ 'cat'=>[], 'tag'=>[], 'brand'=>[], 'attr'=>[] ],
        'tax_all' => [ 'cat'=>false, 'tag'=>false, 'brand'=>false, 'attr'=>false ],
        'pa'   => [],
        'pa_all'   => [],
    ];

    $csv = trim((string)$csv);
    if ($csv === '') return $out;

    $parts = array_filter(array_map('trim', explode(',', $csv)));

    foreach ($parts as $p) {
        if (preg_match('~^id-(\d+)$~i', $p, $m)) {
            $out['ids'][] = (int)$m[1];
            continue;
        }
        if (preg_match('~^star-(\d+)$~i', $p, $m)) {
            $s = (int)$m[1];
            if ($s >= 1 && $s <= 5) $out['stars'][] = $s;
            continue;
        }
        if (preg_match('~^(cat|tag|brand|attr)-(\d+)$~i', $p, $m)) {
            $key = strtolower($m[1]);
            $out['tax'][$key][] = (int)$m[2];
            continue;
        }
        if (preg_match('~^(cat|tag|brand|attr)$~i', $p, $m)) {
            $out['tax_all'][ strtolower($m[1]) ] = true;
            continue;
        }

 // token: pa_xxx=term-slug  (supports dashes in taxonomy name)
// Example: pa_super-nov-pal=abrikos
if (preg_match('/^(pa_[a-z0-9_-]+)=(.+)$/i', $p, $m)) {
    $tax_raw = strtolower(trim($m[1]));
    $tax_raw = preg_replace('/[^a-z0-9_-]/', '', $tax_raw); // keep dash + underscore

    $slug = sanitize_title($m[2]);

    // Use the taxonomy only if it really exists
    if ($tax_raw && taxonomy_exists($tax_raw) && $slug !== '') {
        $out['pa'][$tax_raw][] = $slug;
    }
    continue;
}

// token: pa_xxx  (any term in that attribute taxonomy; supports dashes)
// Example: pa_super-nov-pal
if (preg_match('/^(pa_[a-z0-9_-]+)$/i', $p, $m)) {
    $tax_raw = strtolower(trim($m[1]));
    $tax_raw = preg_replace('/[^a-z0-9_-]/', '', $tax_raw);

    if ($tax_raw && taxonomy_exists($tax_raw)) {
        $out['pa_all'][$tax_raw] = true;
    }
    continue;
}
    }

    // uniq
    $out['ids'] = array_values(array_unique($out['ids']));
    $out['stars'] = array_values(array_unique($out['stars']));
    foreach ($out['tax'] as $k => $arr) $out['tax'][$k] = array_values(array_unique($arr));

    return $out;
}

function cubeabf_detect_brand_taxonomy() {
    foreach (['product_brand', 'pwb-brand', 'yith_product_brand'] as $tax) {
        if (taxonomy_exists($tax)) return $tax;
    }
    return null;
}

function cubeabf_scope_tax_map($post_type) {
    $is_product = ($post_type === 'product');

    return [
        'cat'   => $is_product ? 'product_cat'   : 'category',
        'tag'   => $is_product ? 'product_tag'   : 'post_tag',
        'brand' => $is_product ? cubeabf_detect_brand_taxonomy() : null,
        'attr'  => null, // handled specially (pa_*), see below
    ];
}

function cubeabf_sanitize_tax_keep_dashes(string $tax): string {
    $tax = strtolower(trim($tax));
    // keep a-z 0-9 _ -
    return preg_replace('/[^a-z0-9_-]/', '', $tax);
}

function cubeabf_build_scope_sql($post_type, $include_csv, $exclude_csv) {
    global $wpdb;
    

    $inc = cubeabf_parse_scope_tokens($include_csv);
    $exc = cubeabf_parse_scope_tokens($exclude_csv);
    $map = cubeabf_scope_tax_map($post_type);

    $joins = '';
    $where = [];
    $params = [];

    // Always enforce post type/status elsewhere.

    // ---- Stars: return list of stars to SKIP (UI only, not SQL)
    $skip_stars = $exc['stars'];

    // ---- Post ID include/exclude
    if (!empty($inc['ids'])) {
        $place = implode(',', array_fill(0, count($inc['ids']), '%d'));
        $where[] = "p.ID IN ($place)";
        $params = array_merge($params, $inc['ids']);
    }
    if (!empty($exc['ids'])) {
        $place = implode(',', array_fill(0, count($exc['ids']), '%d'));
        $where[] = "p.ID NOT IN ($place)";
        $params = array_merge($params, $exc['ids']);
    }

    // ---- Tax include/exclude by IDs
    // Strategy:
    //  - INCLUDE: require existence in at least one included term set (AND across token groups)
    //  - EXCLUDE: forbid existence in any excluded term set
    //
    // For safety and simplicity, we do EXISTS subqueries (no exploding joins).
    foreach (['cat','tag','brand'] as $k) {
        $tax = $map[$k];
        if (!$tax) continue;

        // exclude="cat" means: exclude posts that have ANY term in that taxonomy
        if (!empty($exc['tax_all'][$k])) {
            $where[] = "NOT EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = p.ID AND tt.taxonomy = %s
            )";
            $params[] = $tax;
        }

        // include="cat" (all categories only) isn't very meaningful; skip unless you want it to mean "must have any cat"
        if (!empty($inc['tax_all'][$k])) {
            $where[] = "EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = p.ID AND tt.taxonomy = %s
            )";
            $params[] = $tax;
        }

        // exclude="cat-27,cat-37"
        if (!empty($exc['tax'][$k])) {
            $place = implode(',', array_fill(0, count($exc['tax'][$k]), '%d'));
            $where[] = "NOT EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = p.ID AND tt.taxonomy = %s AND tt.term_id IN ($place)
            )";
            $params[] = $tax;
            $params = array_merge($params, $exc['tax'][$k]);
        }

        // include="cat-27,cat-37"
        if (!empty($inc['tax'][$k])) {
            $place = implode(',', array_fill(0, count($inc['tax'][$k]), '%d'));
            $where[] = "EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = p.ID AND tt.taxonomy = %s AND tt.term_id IN ($place)
            )";
            $params[] = $tax;
            $params = array_merge($params, $inc['tax'][$k]);
        }
    }

    // ---- Attributes (pa_*)
    // exclude="attr" => exclude posts having ANY pa_* term
    if (!empty($exc['tax_all']['attr'])) {
        $where[] = "NOT EXISTS (
            SELECT 1 FROM {$wpdb->term_relationships} tr
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = p.ID AND tt.taxonomy LIKE 'pa\\_%'
        )";
    }
    // include="attr" => only posts that have ANY pa_* term
    if (!empty($inc['tax_all']['attr'])) {
        $where[] = "EXISTS (
            SELECT 1 FROM {$wpdb->term_relationships} tr
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = p.ID AND tt.taxonomy LIKE 'pa\\_%'
        )";
    }

    // ---- Attributes (explicit pa_* tokens)
// Supports tokens like: include="pa_color-blue,pa_size-large" exclude="pa_color-red"
// Also supports include="pa_color" / exclude="pa_color" meaning any term in that attribute taxonomy.
$inc_pa = !empty($inc['pa']) ? (array) $inc['pa'] : []; // ['pa_color' => ['blue','red'], 'pa_size' => ['large']]
$exc_pa = !empty($exc['pa']) ? (array) $exc['pa'] : [];
$inc_pa_all = !empty($inc['pa_all']) ? (array) $inc['pa_all'] : []; // ['pa_color' => true]
$exc_pa_all = !empty($exc['pa_all']) ? (array) $exc['pa_all'] : [];

// exclude="pa_color" => exclude posts that have ANY term in pa_color
foreach ($exc_pa_all as $pa_tax => $_true) {
    $pa_tax = sanitize_key($pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;

    $where[] = "NOT EXISTS (
        SELECT 1
        FROM {$wpdb->term_relationships} tr
        JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        WHERE tr.object_id = p.ID
          AND tt.taxonomy = %s
    )";
    $params[] = $pa_tax;
}

// include="pa_color" => include posts that have ANY term in pa_color
foreach ($inc_pa_all as $pa_tax => $_true) {
    $pa_tax = sanitize_key($pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;

    $where[] = "EXISTS (
        SELECT 1
        FROM {$wpdb->term_relationships} tr
        JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        WHERE tr.object_id = p.ID
          AND tt.taxonomy = %s
    )";
    $params[] = $pa_tax;
}

// exclude="pa_color-blue,pa_size-large" => exclude posts that have those specific term slugs in that pa taxonomy
 // exclude pa_* slugs
foreach ($exc_pa as $pa_tax => $slugs) {

    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
    if (!$slugs) continue;

    $place = implode(',', array_fill(0, count($slugs), '%s'));

    $where[] = "NOT EXISTS (
        SELECT 1
        FROM {$wpdb->term_relationships} tr
        JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
        WHERE tr.object_id = p.ID
          AND tt.taxonomy = %s
          AND t.slug IN ($place)
    )";
    $params[] = $pa_tax;
    $params = array_merge($params, $slugs);
}

// include pa_* slugs
foreach ($inc_pa as $pa_tax => $slugs) {

    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
    if (!$slugs) continue;

    $place = implode(',', array_fill(0, count($slugs), '%s'));

    $where[] = "EXISTS (
        SELECT 1
        FROM {$wpdb->term_relationships} tr
        JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
        WHERE tr.object_id = p.ID
          AND tt.taxonomy = %s
          AND t.slug IN ($place)
    )";
    $params[] = $pa_tax;
    $params = array_merge($params, $slugs);
}
    // This for ajax
 // ---- AJAX-friendly scope outputs (DO NOT rebuild SQL!)
    $include_ids = !empty($inc['ids']) ? array_values(array_unique(array_map('absint', (array)$inc['ids']))) : [];
    $exclude_ids = !empty($exc['ids']) ? array_values(array_unique(array_map('absint', (array)$exc['ids']))) : [];

    return [
        'joins'       => $joins,
        'where_sql'   => $where ? (' AND ' . implode(' AND ', $where)) : '',
        'params'      => $params,
        'skip_stars'  => $skip_stars,

        // for WP_Query / AJAX
        'include_ids' => $include_ids,
        'exclude_ids' => $exclude_ids,

        // OPTIONAL: if you later want to mirror include/exclude tax scope in WP_Query,
        // you can also return these term-id lists:
        'include_tax' => $inc['tax'] ?? [],
        'exclude_tax' => $exc['tax'] ?? [],
        'include_tax_all' => $inc['tax_all'] ?? [],
        'exclude_tax_all' => $exc['tax_all'] ?? [],
        'include_pa'     => $inc_pa,
        'exclude_pa'     => $exc_pa,
        'include_pa_all' => $inc_pa_all,
        'exclude_pa_all' => $exc_pa_all,
    ];
}

function cubeabf_get_scope_from_request( string $post_type ): array {
    $scope_io = $_POST['scope_io'] ?? null;

    if (is_string($scope_io)) {
        $scope_io = json_decode(wp_unslash($scope_io), true);
    }

    $include_csv = is_array($scope_io) ? (string)($scope_io['include'] ?? '') : '';
    $exclude_csv = is_array($scope_io) ? (string)($scope_io['exclude'] ?? '') : '';

    return cubeabf_build_scope_sql($post_type, $include_csv, $exclude_csv);
}


 function cubeabf_apply_scope_to_id_list(array $ids, array $scope): array {
    $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));

    $include = isset($scope['include_ids']) ? (array)$scope['include_ids'] : [];
    $exclude = isset($scope['exclude_ids']) ? (array)$scope['exclude_ids'] : [];

    $include = array_values(array_unique(array_filter(array_map('absint', $include))));
    $exclude = array_values(array_unique(array_filter(array_map('absint', $exclude))));

    if ($include) {
        $ids = array_values(array_intersect($ids, $include));
    }
    if ($exclude) {
        $ids = array_values(array_diff($ids, $exclude));
    }

    return $ids;
}

 function cubeabf_apply_scope_tax_to_args(array $args, array $scope, string $post_type): array {
    $map = cubeabf_scope_tax_map($post_type);

    $include_tax = $scope['include_tax'] ?? [];
    $exclude_tax = $scope['exclude_tax'] ?? [];
    $include_all = $scope['include_tax_all'] ?? [];
    $exclude_all = $scope['exclude_tax_all'] ?? [];

    // pa_* scoped tokens (you said you already parse these)
    $include_pa     = $scope['include_pa'] ?? [];       // [pa_color => ['blue','red']]
    $exclude_pa     = $scope['exclude_pa'] ?? [];
    $include_pa_all = $scope['include_pa_all'] ?? [];   // [pa_color => true]
    $exclude_pa_all = $scope['exclude_pa_all'] ?? [];

    $tax_query = isset($args['tax_query']) ? (array)$args['tax_query'] : [];
    if (isset($tax_query['relation'])) unset($tax_query['relation']);

    $add = function(array $clause) use (&$tax_query) {
        $tax_query[] = $clause;
    };

    // cat/tag/brand by term IDs (and cat/tag/brand exists/not exists)
    foreach (['cat','tag','brand'] as $k) {
        $tax = $map[$k] ?? '';
        if (!$tax) continue;

        if (!empty($exclude_all[$k])) {
            $add(['taxonomy' => $tax, 'operator' => 'NOT EXISTS']);
        }
        if (!empty($include_all[$k])) {
            $add(['taxonomy' => $tax, 'operator' => 'EXISTS']);
        }

        if (!empty($exclude_tax[$k])) {
            $ids = array_values(array_unique(array_filter(array_map('absint', (array)$exclude_tax[$k]))));
            if ($ids) {
                $add([
                    'taxonomy' => $tax,
                    'field'    => 'term_id',
                    'terms'    => $ids,
                    'operator' => 'NOT IN',
                ]);
            }
        }

        if (!empty($include_tax[$k])) {
            $ids = array_values(array_unique(array_filter(array_map('absint', (array)$include_tax[$k]))));
            if ($ids) {
                $add([
                    'taxonomy' => $tax,
                    'field'    => 'term_id',
                    'terms'    => $ids,
                    'operator' => 'IN',
                ]);
            }
        }
    }

    // pa_* exists / not exists
foreach ((array)$include_pa_all as $pa_tax => $_true) {
    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $add(['taxonomy' => $pa_tax, 'operator' => 'EXISTS']);
}

foreach ((array)$exclude_pa_all as $pa_tax => $_true) {
    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $add(['taxonomy' => $pa_tax, 'operator' => 'NOT EXISTS']);
}

// pa_* specific term slugs (pa_xxx=slug)
foreach ((array)$include_pa as $pa_tax => $slugs) {
    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
    if (!$slugs) continue;

    $add([
        'taxonomy' => $pa_tax,
        'field'    => 'slug',
        'terms'    => $slugs,
        'operator' => 'IN',
    ]);
}

foreach ((array)$exclude_pa as $pa_tax => $slugs) {
    $pa_tax = cubeabf_sanitize_tax_keep_dashes((string)$pa_tax);
    if (strpos($pa_tax, 'pa_') !== 0) continue;
    if (!taxonomy_exists($pa_tax)) continue;

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$slugs))));
    if (!$slugs) continue;

    $add([
        'taxonomy' => $pa_tax,
        'field'    => 'slug',
        'terms'    => $slugs,
        'operator' => 'NOT IN',
    ]);
}

    if ($tax_query) {
        $args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
    }

    return $args;
}

 function cubeabf_apply_post_scope_to_args( array $args, array $scope ): array {

    $include = isset($scope['include_ids']) ? (array)$scope['include_ids'] : [];
    $exclude = isset($scope['exclude_ids']) ? (array)$scope['exclude_ids'] : [];

    $include = array_values(array_unique(array_filter(array_map('absint', $include))));
    $exclude = array_values(array_unique(array_filter(array_map('absint', $exclude))));

    // Merge include by intersection
    if ( $include ) {
        if ( ! empty($args['post__in']) ) {
            $args['post__in'] = array_values(array_intersect(array_map('absint', (array)$args['post__in']), $include));
        } else {
            $args['post__in'] = $include;
        }
    }

    // Merge exclude by union
    if ( $exclude ) {
        $existing = ! empty($args['post__not_in']) ? (array)$args['post__not_in'] : [];
        $args['post__not_in'] = array_values(array_unique(array_merge(array_map('absint', $existing), $exclude)));
    }

    // Ensure include doesn't contain excluded ids
    if ( ! empty($args['post__in']) && ! empty($args['post__not_in']) ) {
        $args['post__in'] = array_values(array_diff($args['post__in'], $args['post__not_in']));
    }

    return $args;
}

// Union

 function cubeabf_is_assoc_array($arr): bool {
    if (!is_array($arr)) return false;
    foreach (array_keys($arr) as $k) {
        if (is_string($k)) return true;
    }
    return false;
}

/**
 * Normalize:
 * - [] => []
 * - single clause => ['relation'=>X, clause]
 * - list of clauses => ['relation'=>X, clause1, clause2]
 * - group with relation => keep relation, keep clauses
 */
function cubeabf_normalize_query_group($q, $default_relation = 'AND') {
    if (!is_array($q) || empty($q)) return [];

    // If it's already a group:
    if (isset($q['relation'])) {
        $relation = strtoupper($q['relation'] ?? $default_relation);
        unset($q['relation']);

        $items = [];
        foreach ($q as $clause) {
            if (!empty($clause)) $items[] = $clause;
        }
        return $items ? array_merge(['relation' => $relation], $items) : [];
    }

    // If it's a single clause (assoc array like ['taxonomy'=>..] or ['key'=>..])
    if (cubeabf_is_assoc_array($q)) {
        return ['relation' => strtoupper($default_relation), $q];
    }

    // Otherwise treat as list of clauses
    $items = [];
    foreach ($q as $clause) {
        if (!empty($clause)) $items[] = $clause;
    }
    return $items ? array_merge(['relation' => strtoupper($default_relation)], $items) : [];
}

function cubeabf_merge_tax_query_and($base, $extra) {
    $base  = cubeabf_normalize_query_group($base,  'AND');
    $extra = cubeabf_normalize_query_group($extra, 'AND');

    if (!$base)  return $extra;
    if (!$extra) return $base;

    // Keep your old nested behavior (AND of two groups)
    return [
        'relation' => 'AND',
        $base,
        $extra,
    ];
}

function cubeabf_merge_meta_query_and($base, $extra) {
    $base  = cubeabf_normalize_query_group($base,  'AND');
    $extra = cubeabf_normalize_query_group($extra, 'AND');

    if (!$base)  return $extra;
    if (!$extra) return $base;

    return [
        'relation' => 'AND',
        $base,
        $extra,
    ];
}

 /**
 * UNION mode: return IDs that match ANY active filter group (tax OR meta OR price OR rating),
 * while still respecting base constraints in $base_args (context, scope_config, etc).
 *
 * Safety:
 * - Caps total IDs collected (MAX_IDS)
 * - Caps number of meta_* queries (MAX_META_KEYS)
 *
 * Notes:
 * - $base_args should already include your base constraints (context tax_query, port meta_query, post__in/out, etc).
 * - $scope is only applied to the RATING union query (as you wanted earlier), but you can apply it elsewhere too.
 */
function cubeabf_union_filter_ids(
    array $base_args,
    array $filters,
    ?array $scope = null,
    string $rating_mode = 'bucket',
    string $rating_rel = 'OR',
    array $meta_db_keys = []
): array {

    // -------------------------
    // Caps (tuneable)
    // -------------------------
    $MAX_IDS       = 2000; // recommended: 1500–3000
    $MAX_META_KEYS = 5;    // recommended: 3–6

    $all_ids = [];

    // -------------------------
    // runner with early stop
    // -------------------------
    $run = function(array $sub_args) use (&$all_ids, $MAX_IDS) {
        if (count($all_ids) >= $MAX_IDS) return;

        $q = new WP_Query($sub_args);

        if (!empty($q->posts)) {
            $all_ids = array_merge($all_ids, array_map('absint', (array)$q->posts));

            if (count($all_ids) >= $MAX_IDS) {
                $all_ids = array_slice(array_values(array_unique($all_ids)), 0, $MAX_IDS);
            }
        }

        wp_reset_postdata();
    };

    // -------------------------
    // Make base args "IDs only" + cheap, but keep constraints
    // -------------------------
    $base_args['fields']                 = 'ids';
    $base_args['posts_per_page']         = -1;
    $base_args['no_found_rows']          = true;
    $base_args['cache_results']          = false;
    $base_args['update_post_meta_cache'] = false;
    $base_args['update_post_term_cache'] = false;

    // paging makes no sense for union-id queries
    unset($base_args['paged'], $base_args['offset']);

    // Normalize rating mode/rel
    $rating_mode = ($rating_mode === 'min') ? 'min' : 'bucket';
    $rating_rel  = strtoupper($rating_rel);
    $rating_rel  = ($rating_rel === 'AND') ? 'AND' : 'OR';

    // Map “filter keys” to real WP taxonomy names
    $tax_key_map = [
        'category' => 'category',
        'post_tag'      => 'post_tag',
    ];

    // -------------------------
    // A) TAXONOMIES (one query)
    // OR across taxonomies and OR within terms per taxonomy
    // -------------------------
    $tax_or = ['relation' => 'OR'];

    foreach ($filters as $taxonomy_key => $values) {
        if (empty($values)) continue;
        if (count($all_ids) >= $MAX_IDS) break;

        // skip non-tax keys
        if (str_ends_with($taxonomy_key, '_range') || $taxonomy_key === 'rating') continue;
        if (strpos((string)$taxonomy_key, 'meta_') === 0) continue;

        // normalize key -> real taxonomy
        $taxonomy = $tax_key_map[$taxonomy_key] ?? $taxonomy_key;
        $taxonomy = cubeabf_sanitize_tax_keep_dashes($taxonomy);

        // allow only certain taxonomies
        $allowed =
            $taxonomy === 'product_cat' ||
            $taxonomy === 'product_tag' ||
            $taxonomy === 'product_brand' ||
            $taxonomy === 'category' ||
            $taxonomy === 'post_tag' ||
            (is_string($taxonomy) && (function_exists('str_starts_with') ? str_starts_with($taxonomy, 'pa_') : (strpos($taxonomy, 'pa_') === 0)));

        if (!$allowed) continue;

        // allow pa_ even if taxonomy_exists can be flaky depending on init order
        if (!taxonomy_exists($taxonomy) && strpos($taxonomy, 'pa_') !== 0) continue;

        $terms = is_array($values) ? $values : [$values];
        $terms = array_values(array_filter(array_map('sanitize_title', $terms)));
        if (!$terms) continue;

        $tax_or[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN', // OR within the terms list
        ];
    }

    if (count($tax_or) > 1) {
        $a = $base_args;

        // merge base tax_query AND (this OR group)
        $a['tax_query'] = cubeabf_merge_tax_query_and($base_args['tax_query'] ?? [], $tax_or);

        $run($a);
    }

 // -------------------------
// B) META custom fields (meta_*)
// One query per meta key (OR within values)
// Repeater support via EXISTS + meta_key LIKE rep_%_sub
// -------------------------
 $meta_keys_seen = 0;



$meta_group_map = (
    isset($filters['__meta_group_map'])
    && is_array($filters['__meta_group_map'])
)
    ? $filters['__meta_group_map']
    : [];

if (empty($meta_group_map)) {
    $meta_group_map = $GLOBALS['cubeabf_meta_groups'] ?? [];
}

// These are internal filter config keys, not actual filters.
unset($filters['__meta_group_map']);
unset($filters['__meta_db_keys']);

$repeater_meta_filters = [];

foreach ($filters as $k => $val) {

    if (strpos($k, 'meta_') !== 0) {
        continue;
    }

    if ($meta_keys_seen >= $MAX_META_KEYS) {
        break;
    }

    if (count($all_ids) >= $MAX_IDS) {
        break;
    }

    /*
     * ---------------------------------------------------------
     * Resolve DB meta key
     * ---------------------------------------------------------
     */

    $db_key = $meta_db_keys[$k] ?? substr($k, 5);
    $db_key = (string) $db_key;

    $url_filter_key = $k;

    $any_row_map = [];

if (isset($_POST['any_row']) && is_array($_POST['any_row'])) {
    // AJAX
    $any_row_map = $_POST['any_row'];
} else {
    // SSR
    $any_row_map = $GLOBALS['cubeabf_any_row_map'] ?? [];
}

$any_row = !empty($any_row_map[$url_filter_key]);
    
 

    if (
        isset(
            $GLOBALS['cubeabf_url_key_maps'][$url_filter_key]
        )
    ) {
        $db_key =
            $GLOBALS['cubeabf_url_key_maps'][$url_filter_key];

        if (str_starts_with($db_key, 'meta_')) {
            $db_key = substr($db_key, 5);
        }
    }

    $vals = is_array($val) ? $val : [$val];

    $vals = array_values(
        array_filter(
            array_map('sanitize_text_field', $vals)
        )
    );

    if (!$vals) {
        continue;
    }

    /*
     * ---------------------------------------------------------
     * Per-filter settings
     * ---------------------------------------------------------
     */

    $any_row = !empty(
        $any_row_map[$url_filter_key]
    );

    $meta_group = !empty(
        $meta_group_map[$url_filter_key]
    );

    /*
     * ---------------------------------------------------------
     * Repeater roots
     * ---------------------------------------------------------
     */

    $repeater_roots = $_POST['repeater_roots'] ?? null;

    if (
        $repeater_roots === null ||
        $repeater_roots === ''
    ) {
        $repeater_roots =
            $GLOBALS['cubeabf_repeater_roots'] ?? [];
    }

    $repeater_roots = (array) $repeater_roots;

    $is_repeater_root = in_array(
        $db_key,
        $repeater_roots,
        true
    );

    $is_repeater = !empty(
        $repeater_fields[$db_key]
    );

      

       $is_group = !empty(
        $meta_group_map[$url_filter_key]
    );

   

if ($is_group) {

    $repeater_meta_filters[] = [
        'key'     => strtolower($db_key),
        'vals'    => $vals,
        'any_row' => false,
        'group'   => true,
    ];

    continue;
}

    /*
     * ---------------------------------------------------------
     * 1. Repeater root
     * ---------------------------------------------------------
     */

    if ($is_repeater_root) {

        $a = $base_args;

        $extra_meta = [
            'relation' => 'AND',

            [
                'key'     => $db_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
        ];

        $a['meta_query'] =
            cubeabf_merge_meta_query_and(
                $base_args['meta_query'] ?? [],
                $extra_meta
            );

        $run($a);

    /*
     * ---------------------------------------------------------
     * 2. Repeater subfield
     * ---------------------------------------------------------
     */

    } elseif (preg_match('/\d+/', $db_key)) {

        /*
         * DO NOT run() here.
         *
         * Collect it so posts_where can apply:
         *
         * exact
         * group
         * any_row
         */

        

        $repeater_meta_filters[] = [
            'key'      => strtolower($db_key),
            'vals'     => $vals,
            'any_row'  => $any_row,
            'group'    => $meta_group,
        ];

    /*
     * ---------------------------------------------------------
     * 3. Other repeater type
     * ---------------------------------------------------------
     */

    } elseif ($is_repeater) {

        $pos = strrpos($db_key, '_');

        if ($pos !== false) {

            $rep = strtolower(
                substr($db_key, 0, $pos)
            );

            $sub = strtolower(
                substr($db_key, $pos + 1)
            );

            $a = $base_args;

            $a['cubeabf_rep_meta_filters'] = [
                [
                    'rep'  => $rep,
                    'sub'  => $sub,
                    'vals' => $vals,
                ],
            ];

            $run($a);
        }

    /*
     * ---------------------------------------------------------
     * 4. Normal meta field
     * ---------------------------------------------------------
     */

    } else {

        $a = $base_args;

        $extra_meta = [
            'relation' => 'AND',

            [
                'key'     => $db_key,
                'value'   => count($vals) === 1
                    ? $vals[0]
                    : $vals,
                'compare' => count($vals) === 1
                    ? '='
                    : 'IN',
            ],
        ];

        $a['meta_query'] =
            cubeabf_merge_meta_query_and(
                $base_args['meta_query'] ?? [],
                $extra_meta
            );

        $run($a);
    }

    $meta_keys_seen++;
}

/*
 * -------------------------------------------------------------
 * Run collected repeater subfield filters
 * -------------------------------------------------------------
 */

// ---------------------------------------------------------
// Run collected repeater filters
// ---------------------------------------------------------

if (!empty($repeater_meta_filters)) {

    foreach ($repeater_meta_filters as $rep_filter) {

        if (count($all_ids) >= $MAX_IDS) {
            break;
        }

        $a = $base_args;

        // IMPORTANT: this makes posts_where run
        $a['cubeabf_query'] = true;

        $a['cubeabf_rep_meta_filters'] = [
            $rep_filter
        ];

       

        $run($a);
    }
}

    // -------------------------
    // C) Here was price PRICE (one query)
    // -------------------------
    

    // -------------------------
    // D) RATING (one query)
    // Supports bucket OR min
    // IMPORTANT: do NOT overwrite base meta_query — merge with it
    // -------------------------
    if (!empty($filters['rating']) && count($all_ids) < $MAX_IDS) {

        $vals = [];
        foreach ((array)$filters['rating'] as $v) {
            if (preg_match('/(\d+)/', (string)$v, $m)) {
                $n = (int)$m[1];
                if ($n >= 1 && $n <= 5) $vals[] = $n;
            }
        }
        $vals = array_values(array_unique($vals));

        if ($vals) {

            $a = $base_args;

            // apply rating scope ONLY here (your existing intention)
            if ($scope) {
                $a = cubeabf_apply_post_scope_to_args($a, $scope);
                $a = cubeabf_apply_scope_tax_to_args($a, $scope, (string)($a['post_type'] ?? 'post'));
            }

            // start from base meta_query (if any) and ensure AND relation
            $a['meta_query'] = $base_args['meta_query'] ?? ['relation' => 'AND'];
            if (!is_array($a['meta_query'])) {
                $a['meta_query'] = ['relation' => 'AND'];
            } elseif (empty($a['meta_query']['relation'])) {
                $a['meta_query'] = array_merge(['relation' => 'AND'], $a['meta_query']);
            } else {
                $a['meta_query']['relation'] = 'AND';
            }

            if ($rating_mode === 'min') {

                // min mode: 3+ / 4+ behavior
                // if multiple selected:
                // - OR => lowest threshold (min)
                // - AND => highest threshold (max)
                $threshold = ($rating_rel === 'AND') ? max($vals) : min($vals);

                $a['meta_query'][] = [
                    'key'     => '_wc_average_rating',
                    'value'   => (float)$threshold,
                    'type'    => 'NUMERIC',
                    'compare' => '>=',
                ];

            } else {

                // bucket mode: selected star buckets
                $group = ['relation' => $rating_rel]; // usually OR

                foreach ($vals as $r) {
                    $min   = (float)$r;
                    $upper = ($r === 5) ? 5.01 : (float)($r + 1);

                    $group[] = [
                        'relation' => 'AND',
                        ['key' => '_wc_average_rating', 'value' => $min,   'type' => 'NUMERIC', 'compare' => '>='],
                        ['key' => '_wc_average_rating', 'value' => $upper, 'type' => 'NUMERIC', 'compare' => '<' ],
                    ];
                }

                $a['meta_query'][] = $group;
            }

            $run($a);
        }
    }

    // Final unique IDs
    $all_ids = array_values(array_unique(array_filter(array_map('absint', $all_ids))));
    if (count($all_ids) > $MAX_IDS) {
        $all_ids = array_slice($all_ids, 0, $MAX_IDS);
    }

    return $all_ids;
}

 


add_filter('posts_where', function($where, $q){
    global $wpdb;

    // ✅ only affect our intended queries
    if ( ! $q->get('cubeabf_scope_config_active') ) {
        return $where;
    }

    $mode = $q->get('cubeabf_pa_all');
    if (!$mode) return $where;

    if ($mode === 'include') {
        $where .= " AND EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} tr
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = {$wpdb->posts}.ID
              AND tt.taxonomy LIKE 'pa\\_%'
        )";
    } elseif ($mode === 'exclude') {
        $where .= " AND NOT EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} tr
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = {$wpdb->posts}.ID
              AND tt.taxonomy LIKE 'pa\\_%'
        )";
    }

    return $where;
}, 10, 2);

function cubeabf_get_scope_config_tokens(): array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $csv = $GLOBALS['cubeabf_scope_config_csv'] ?? ['include' => '', 'exclude' => ''];
    $inc_csv = (string)($csv['include'] ?? '');
    $exc_csv = (string)($csv['exclude'] ?? '');

    $cached = [
        'include' => ($inc_csv !== '') ? cubeabf_parse_include_exclude_tokens($inc_csv) : null,
        'exclude' => ($exc_csv !== '') ? cubeabf_parse_include_exclude_tokens($exc_csv) : null,
    ];
    return $cached;
}

 

function cubeabf_term_matches_scope(WP_Term $t, string $taxonomy, array $scope): bool {
    $slug = strtolower($t->slug);
    $name = strtolower($t->name);
    $id   = (int)$t->term_id;

    // direct
    if (!empty($scope['ids'])   && in_array($id,   (array)$scope['ids'], true))   return true;
    if (!empty($scope['slugs']) && in_array($slug, (array)$scope['slugs'], true)) return true;
    if (!empty($scope['names']) && in_array($name, (array)$scope['names'], true)) return true;

    // taxonomy-scoped IDs
    if (!empty($scope['tax_ids'])) {
        foreach ($scope['tax_ids'] as $taxKey => $ids) {
            $resolved = cubeabf_resolve_pseudo_tax_key($taxKey, $taxonomy) ?: $taxKey;
            if ($resolved !== $taxonomy) continue;
            if ($ids && in_array($id, (array)$ids, true)) return true;
        }
    }

    // taxonomy-scoped slugs
    if (!empty($scope['tax_slugs'])) {
        foreach ($scope['tax_slugs'] as $taxKey => $slugs) {
            $resolved = cubeabf_resolve_pseudo_tax_key($taxKey, $taxonomy) ?: $taxKey;
            if ($resolved !== $taxonomy) continue;
            if ($slugs && in_array($slug, (array)$slugs, true)) return true;
        }
    }

    return false;
}


add_action('wp', function () {

    // Already set (maybe config shortcode ran early)
    if (!empty($GLOBALS['cubeabf_scope_config_csv'])) {
        return;
    }

    $post = null;

    // Most common: normal page/post
    if (is_singular()) {
        $post = get_queried_object();
    }
    // Static front page
    elseif (is_front_page()) {
        $front_id = (int) get_option('page_on_front');
        if ($front_id) $post = get_post($front_id);
    }
    // Posts page
    elseif (is_home()) {
        $posts_id = (int) get_option('page_for_posts');
        if ($posts_id) $post = get_post($posts_id);
    }

    if (!$post || empty($post->post_content)) {
        // No content to scan (archives like product_cat etc)
        // You can optionally set defaults here from options:
        // $GLOBALS['cubeabf_scope_config_csv'] = ['include' => get_option(...), 'exclude' => get_option(...)];
        return;
    }

    // Scan content for [cubeabf_config ...]
    if (!has_shortcode($post->post_content, 'cubeabf_config')) {
        return;
    }

    $regex = get_shortcode_regex(['cubeabf_config']);
    if (!preg_match_all("/$regex/s", $post->post_content, $m, PREG_SET_ORDER)) {
        return;
    }

    // Take FIRST config occurrence
    foreach ($m as $match) {
        if (empty($match[2]) || $match[2] !== 'cubeabf_config') continue;

        $atts = shortcode_parse_atts($match[3] ?? '') ?: [];

        $include = isset($atts['scope_include']) ? (string)$atts['scope_include'] : (string)($atts['include'] ?? '');
        $exclude = isset($atts['scope_exclude']) ? (string)$atts['scope_exclude'] : (string)($atts['exclude'] ?? '');

        $GLOBALS['cubeabf_scope_config_csv'] = [
            'include' => trim($include),
            'exclude' => trim($exclude),
        ];

        // Optional: also store other config flags here if you use them globally
        // $GLOBALS['cubeabf_cfg_tax_relation'] = strtoupper($atts['tax_relation'] ?? 'AND');

        break;
    }

}, 1);

// prints once per request
function cubeabf_print_config_once($atts = []) {
  static $done = false;
  if ($done) return '';
  $done = true;

  $defaults = [
    'tax_relation' => 'AND',
    'ssr_tax_operators' => 'IN',
    'filters_mode' => 'AND',     // if you ever do OR across tax+meta via union IDs
    'rating_mode'  => 'bucket',
    'rating_meta_relation' => 'AND',
    'include'       => '',
    'exclude'       => '',
    'scope_include' => '',
    'scope_exclude' => '',
    'real_meta_values' => '', // meta_one_metakey,item_1::1071,item_2::1072,item_3::1073|meta_two_metakey,boo_1::car1,boo_2::car2,boo_3::car3|meta_three_matakey,fly_1::bloom
    'real_meta_keys' => '',
    'range_keys' => '', // range_keys="_price|_weight|_height|_length"
    'repeater_roots' => '', // repeater_roots="meta_superman,meta_gallery,meta_rep_or_use"
    'meta_groups' => '', // meta_groups="meta_rep_or_use_0_lenta,meta_some_other_field"
    'any_row_true_map' => '',
    'search_fields' => '',
    ];


   if ( isset( $atts['data'] ) ) {

    // Decode base64 first
    $decoded = base64_decode( $atts['data'], true );

    if ( $decoded ) {
        // Then decode JSON
        $json_data = json_decode( $decoded, true );

        if ( json_last_error() === JSON_ERROR_NONE && is_array( $json_data ) ) {
            $atts = shortcode_atts( $defaults, $json_data, 'cubeabf-config' );
        } else {
            $atts = $defaults; // fallback if JSON malformed
        }
    } else {
        $atts = $defaults; // fallback if Base64 invalid
    }

} else {
    // Legacy style
    $atts = shortcode_atts( $defaults, $atts, 'cubeabf-config' );
}

// ✅ prefer scope_* but allow include/exclude as fallback
    $include_csv = trim((string)($atts['scope_include'] !== '' ? $atts['scope_include'] : $atts['include']));
    $exclude_csv = trim((string)($atts['scope_exclude'] !== '' ? $atts['scope_exclude'] : $atts['exclude']));

     $GLOBALS['cubeabf_filters_mode'] = $atts['filters_mode'];
     $GLOBALS['cubeabf_tax_relation'] = $atts['tax_relation'];
     $GLOBALS['cubeabf_ssr_tax_operators'] = $atts['ssr_tax_operators'];
     $GLOBALS['cubeabf_rating_mode'] = $atts['rating_mode'];
     $GLOBALS['cubeabf_rating_meta_relation'] = $atts['rating_meta_relation'];
     $GLOBALS['cubeabf_repeater_roots'] = array_filter(
    array_map('trim', explode(',', (string) $atts['repeater_roots']))
);

 

$meta_groups = sanitize_text_field($atts['meta_groups'] ?? '');

$GLOBALS['cubeabf_meta_groups'] = [];

if ($meta_groups !== '') {
    foreach (array_map('trim', explode(',', $meta_groups)) as $key) {
        if ($key !== '') {
            $GLOBALS['cubeabf_meta_groups'][$key] = true;
        }
    }
}


    $real_meta_values = trim( $atts['real_meta_values'] );

if ( $real_meta_values !== '' ) {

    foreach ( explode( '|', $real_meta_values ) as $group ) {

        $group = trim( $group );

        if ( $group === '' ) {
            continue;
        }

        $parts = array_map( 'trim', explode( ',', $group ) );

        if ( count( $parts ) < 2 ) {
            continue;
        }

         
$meta_key = array_shift($parts);

// Strip filter prefix for storage
if (str_starts_with($meta_key, 'meta_')) {
    $meta_key = substr($meta_key, 5);
}

foreach ($parts as $pair) {

    if (strpos($pair, '::') === false) {
        continue;
    }

    list($short, $real) = array_map('trim', explode('::', $pair, 2));

    if ($short === '' || $real === '') {
        continue;
    }

    $GLOBALS['cubeabf_url_maps'][$meta_key][$short] = $real;
}
    }
}

 $real_meta_keys = trim($atts['real_meta_keys']);

if ($real_meta_keys !== '') {

    foreach (explode('|', $real_meta_keys) as $pair) {

        $pair = trim($pair);

        if ($pair === '' || strpos($pair, '::') === false) {
            continue;
        }

        list($short, $real) = array_map('trim', explode('::', $pair, 2));

        if ($short === '' || $real === '') {
            continue;
        }

        $GLOBALS['cubeabf_url_key_maps'][ strtolower($short) ] = $real;
    }
}

$any_row_true_map = [];

if ( ! empty( $atts['any_row_true_map'] ) ) {

    $keys = array_map(
        'trim',
        explode( ',', $atts['any_row_true_map'] )
    );

    foreach ( $keys as $key ) {
        if ( $key !== '' ) {
            $any_row_true_map[ $key ] = true;
        }
    }
}

$GLOBALS['cubeabf_any_row_map'] = $any_row_true_map;

$search_fields = $atts['search_fields'] ?? '';

$GLOBALS['cubeabf_search_fields'] = array_values(
    array_filter(
        array_map(
            'trim',
            explode(',', $search_fields)
        )
    )
);

 if (!empty($GLOBALS['cubeabf_url_key_maps'])) :  ?>
<script>
window.cubeabfRealMetaKeys = <?php echo wp_json_encode($GLOBALS['cubeabf_url_key_maps']); ?>;
</script>
 <?php endif;  

$keys = array_filter(array_map('trim', explode('|', $atts['range_keys'])));

foreach ($keys as $meta_key) {

    $name = ltrim($meta_key, '_');

    $GLOBALS['cubeabf_range_meta_keys'][$name . '_range'] = $meta_key;
}

    // ✅ store globally for ALL other shortcodes on first load
    $GLOBALS['cubeabf_scope_config_csv'] = [
        'include' => $include_csv,
        'exclude' => $exclude_csv,
    ];

    // (optional) also store parsed tokens so you don’t parse repeatedly
    $GLOBALS['cubeabf_scope_config_tokens'] = [
        'include' => $include_csv !== '' ? cubeabf_parse_include_exclude_tokens($include_csv) : null,
        'exclude' => $exclude_csv !== '' ? cubeabf_parse_include_exclude_tokens($exclude_csv) : null,
    ];

    $repeater_roots = array_filter(array_map('trim', explode(',', $atts['repeater_roots'])));

  return '<div class="cubeabf-config" style="display:none"
    data-tax-relation="' . esc_attr(strtoupper($atts['tax_relation'])) . '"
    data-filters-mode="' . esc_attr($atts['filters_mode']) . '"
    data-rating-mode="' . esc_attr($atts['rating_mode']) . '"
    data-rating-meta-relation="' . esc_attr(strtoupper($atts['rating_meta_relation'])) . '"
    data-scope-include="' . esc_attr($atts['scope_include']) . '"
    data-scope-exclude="' . esc_attr($atts['scope_exclude']) . '"
    data-rep-roots="' . esc_attr(implode(',', $repeater_roots)) . '"
  ></div>';

  wp_localize_script('filters-mode', 'cubeabf_ajax_data', [
  // ...
  'filters_mode' => $atts['filters_mode'] ?? 'and',
]);
 

}
add_shortcode('cubeabf_config', 'cubeabf_print_config_once');


function cubeabf_get_terms_for_tax( $taxonomy, $parent = 0 ) {
    $args = array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,  // Recommended for filters (change to true if you prefer)
    );

    // Only apply 'parent' if it's a positive integer (valid term_id)
    if ( is_numeric( $parent ) && intval( $parent ) > 0 ) {
        $args['parent'] = intval( $parent );
    }
    // If $parent is a string (slug or name), we should NOT use it here with 'parent'
    // because 'parent' arg only accepts term_id. We already resolved it earlier.

    $terms = get_terms( $args );

    if ( is_wp_error( $terms ) ) {
        error_log( 'cubeabf_get_terms_for_tax error: ' . $terms->get_error_message() );
        return array();
    }

    return $terms;
}

// Inc/exc

/**
 * Parse include/exclude list that may contain:
 * - slug, name, id-123
 * - cat-27, tag-46, brand-57
 * - pa_color-blue, pa_size-12
 * - star-1 ... star-5   (returned separately)
 */
 function cubeabf_parse_include_exclude_tokens( string $list ): array {

    $out = [
        'raw'         => [],
        'slugs'       => [],
        'names'       => [],
        'ids'         => [],

        // taxonomy-scoped tokens
        'tax_ids'     => [], // [taxonomy => [ids]]
        'tax_slugs'   => [], // [taxonomy => [slugs]]

        // taxonomy exists/not-exists scope
        'tax_all'     => [], // [taxonomy => true]  (EXISTS / NOT EXISTS use-case)
        'pa_all'      => false, // any pa_* exists (special)

        // rating UI only
        'stars'       => [], // [1..5]
    ];

    $list = trim((string)$list);
    if ($list === '') return $out;

    $items = array_map('trim', explode(',', strtolower($list)));
    $items = array_values(array_filter($items, fn($x) => $x !== ''));

    $out['raw'] = $items;

    foreach ($items as $token) {

        // -------------------------
        // 0) pa_all (any pa_* term exists)
        // -------------------------
        if ($token === 'pa_all') {
            $out['pa_all'] = true;
            continue;
        }

        // -------------------------
        // 1) *_all tokens (product_tag_all, pa_color_all, etc)
        // -------------------------
        if (preg_match('/^([a-z0-9_-]+)_all$/', $token, $m)) {

            $raw_key = cubeabf_sanitize_tax_keep_dashes($m[1]);

            // allow pa_color_all (and even dashed attribute tax names)
            if (strpos($raw_key, 'pa_') === 0) {
                // accept real taxonomy names (may include dashes)
                $tax = taxonomy_exists($raw_key) ? $raw_key : '';
                if ($tax) {
                    $out['tax_all'][$tax] = true;
                }
                continue;
            }

            // map friendly keys to real taxonomies
            $map = [
                'post_cat'      => 'category',
                'post_tag'      => 'post_tag',
                'product_cat'   => 'product_cat',
                'product_tag'   => 'product_tag',
                'product_brand' => 'product_brand',
            ];

            if (!empty($map[$raw_key])) {
                $out['tax_all'][$map[$raw_key]] = true;
                continue;
            }

            // or allow real taxonomy name directly
            if (taxonomy_exists($raw_key)) {
                $out['tax_all'][$raw_key] = true;
                continue;
            }

            // unknown _all token => ignore (do NOT fall back to slug)
            continue;
        }

        // -------------------------
        // 2) id-123
        // -------------------------
        if (preg_match('/^id-(\d+)$/', $token, $m)) {
            $out['ids'][] = (int)$m[1];
            continue;
        }

        // -------------------------
        // 3) star-1 ... star-5
        // -------------------------
        if (preg_match('/^star-(\d)$/', $token, $m)) {
            $s = (int)$m[1];
            if ($s >= 1 && $s <= 5) $out['stars'][] = $s;
            continue;
        }

        // -------------------------
        // 4) key=value tokens (recommended for dashed pa_ tax)
        //    Examples:
        //      pa_super-nov-pal=abrikos
        //      product_cat=26
        //      product_tag=some-slug
        // -------------------------
        if (strpos($token, '=') !== false) {
            [$left, $right] = array_map('trim', explode('=', $token, 2));

            $raw_key = cubeabf_sanitize_tax_keep_dashes($left);
            $value   = trim($right);

            if ($raw_key === '' || $value === '') {
                continue;
            }

            // pa_* taxonomy (can contain dashes)
            if (strpos($raw_key, 'pa_') === 0) {
                $tax = taxonomy_exists($raw_key) ? $raw_key : '';
                if ($tax) {
                    if (ctype_digit($value)) $out['tax_ids'][$tax][] = (int)$value;
                    else                     $out['tax_slugs'][$tax][] = sanitize_title($value);
                }
                continue;
            }

            // non-pa mapped keys
            $map = [
                'post_cat'      => 'category',
                'post_tag'      => 'post_tag',
                'product_cat'   => 'product_cat',
                'product_tag'   => 'product_tag',
                'product_brand' => 'product_brand',
            ];

            $tax = $map[$raw_key] ?? (taxonomy_exists($raw_key) ? $raw_key : '');
            if (!$tax) {
                // ignore unknown key=value token (do NOT fall back to slug)
                continue;
            }

            if (ctype_digit($value)) $out['tax_ids'][$tax][] = (int)$value;
            else                     $out['tax_slugs'][$tax][] = sanitize_title($value);

            continue;
        }

        // -------------------------
        // 5) prefix-value (cat-27, pa_color-blue, etc) legacy
        // -------------------------
        if (preg_match('/^([a-z0-9_]+)-(.*)$/', $token, $m)) {
            $prefix = sanitize_key($m[1]);
            $value  = trim($m[2]);

            if ($value === '') continue;

            if ($prefix === 'cat') {
                if (ctype_digit($value)) $out['tax_ids']['__cat__'][] = (int)$value;
                else                     $out['tax_slugs']['__cat__'][] = sanitize_title($value);
                continue;
            }

            if ($prefix === 'tag') {
                if (ctype_digit($value)) $out['tax_ids']['__tag__'][] = (int)$value;
                else                     $out['tax_slugs']['__tag__'][] = sanitize_title($value);
                continue;
            }

            if ($prefix === 'brand') {
                if (ctype_digit($value)) $out['tax_ids']['product_brand'][] = (int)$value;
                else                     $out['tax_slugs']['product_brand'][] = sanitize_title($value);
                continue;
            }

            // pa_color-blue (no dashes in taxonomy part)
            if (strpos($prefix, 'pa_') === 0) {
                if (ctype_digit($value)) $out['tax_ids'][$prefix][] = (int)$value;
                else                     $out['tax_slugs'][$prefix][] = sanitize_title($value);
                continue;
            }
        }

        // -------------------------
        // 6) fallback: plain slug/name
        // -------------------------
        $out['slugs'][] = sanitize_title($token);
        $out['names'][] = $token;
    }

    // de-dupe
    $out['ids']   = array_values(array_unique(array_filter(array_map('absint', $out['ids']))));
    $out['stars'] = array_values(array_unique(array_filter(array_map('absint', $out['stars']))));

    foreach (['tax_ids','tax_slugs'] as $k) {
        foreach ($out[$k] as $tax => $vals) {
            if ($k === 'tax_ids') {
                $vals = array_values(array_unique(array_filter(array_map('absint', (array)$vals))));
            } else {
                $vals = array_values(array_unique(array_filter(array_map('sanitize_title', (array)$vals))));
            }
            $out[$k][$tax] = $vals;
        }
    }

    $out['slugs'] = array_values(array_unique(array_filter($out['slugs'])));
    $out['names'] = array_values(array_unique(array_filter($out['names'])));

    return $out;
}

/**
 * Resolve special pseudo-tax keys __cat__ / __tag__ to actual taxonomy being filtered.
 */
function cubeabf_resolve_pseudo_tax_key(string $pseudo, string $taxonomy): ?string {
    if ($pseudo === '__cat__') {
        return in_array($taxonomy, ['category','product_cat'], true) ? $taxonomy : null;
    }
    if ($pseudo === '__tag__') {
        return in_array($taxonomy, ['post_tag','product_tag'], true) ? $taxonomy : null;
    }
    return null;
}

// Args thing

 function cubeabf_request_var(string $key, $default = '') {
    // Prefer POST, then GET
    if (isset($_POST[$key])) return wp_unslash($_POST[$key]);
    if (isset($_GET[$key]))  return wp_unslash($_GET[$key]);
    return $default;
}

function cubeabf_build_base_args_for_context(array $opts = []): array {

    $post_type = sanitize_key($opts['post_type'] ?? 'product');
    $orderby = sanitize_key($opts['orderby'] ?? 'post_date');
    $order = sanitize_key($opts['order'] ?? 'DESC');

    // per_page: allow -1 for ID-collection queries
    $per_page = array_key_exists('per_page', $opts)
        ? (int)$opts['per_page']
        : (int)get_option('posts_per_page');

    $paged = array_key_exists('paged', $opts)
        ? max(1, (int)$opts['paged'])
        : max(1, (int)get_query_var('paged'));

    // Optional: allow caller to force search string
    $search_query = '';
    if (array_key_exists('search', $opts)) {
        $search_query = (string)$opts['search'];
    } else {
        // WP native search (first load)
        $search_query = (string)get_search_query();
        // AJAX fallback (if you send it)
        if ($search_query === '') {
            $search_query = (string) cubeabf_request_var('s', '');
        }
    }
    $search_query = sanitize_text_field($search_query);

    // ------------------------------------------------
    // 0) Base args
    // ------------------------------------------------
    $args = [
        'post_type'           => $post_type,
        'posts_per_page'      => $per_page,
        'ignore_sticky_posts' => true,
        'paged'               => $paged,
        'post_status'         => 'publish',
        'orderby'             => $orderby,
        'order'               => $order,
    ];

 
    // ------------------------------------------------
    // 1) Resolve context from WP (first-load) OR request (AJAX)
    // ------------------------------------------------

    // A) Prefer WP query context when available (first load)
    // Woo product_cat archive
    if (function_exists('is_tax') && is_tax('product_cat')) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $args['tax_query'] = [[
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => [ (int)$term->term_id ],
                'include_children' => true,
            ]];
            return $args;
        }
    }

    // WP category archive
    if (function_exists('is_category') && is_category()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $args['tax_query'] = [[
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => [ (int)$term->term_id ],
                'include_children' => true,
            ]];
            return $args;
        }
    }

    // Woo product_tag archive
    if (function_exists('is_tax') && is_tax('product_tag')) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $args['tax_query'] = [[
                'taxonomy' => 'product_tag',
                'field'    => 'term_id',
                'terms'    => [ (int)$term->term_id ],
            ]];
            return $args;
        }
    }

    // WP post_tag archive
    if (function_exists('is_tag') && is_tag()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            // WP tag query uses 'tag' => slug (works for posts)
            $args['tag'] = sanitize_title($term->slug);
            return $args;
        }
    }

    // Brand archive (your taxonomy)
    if (function_exists('is_tax') && is_tax('product_brand')) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $args['tax_query'] = [[
                'taxonomy' => 'product_brand',
                'field'    => 'term_id',
                'terms'    => [ (int)$term->term_id ],
            ]];
            return $args;
        }
    }

    // Search page (first load)
    if (function_exists('is_search') && is_search() && $search_query !== '') {
        $args['s'] = $search_query;
        return $args;
    }

    // B) If not in a WP archive context (AJAX / custom pages), use request keys
    $category_slug = sanitize_text_field( cubeabf_request_var('category_slug', '') );
    $woo_tag_slug  = sanitize_text_field( cubeabf_request_var('woo_tag', '') );
    $post_cat_slug = sanitize_text_field( cubeabf_request_var('post_cat', '') );
    $post_tag_slug = sanitize_text_field( cubeabf_request_var('cubeb_post_tag', cubeabf_request_var('post_tag', '')) );
    $brand_slug    = sanitize_text_field( cubeabf_request_var('brand_slug', '') );

    // Woo product category by slug
    if ($category_slug !== '') {
        $current_cat = get_term_by('slug', $category_slug, 'product_cat');
        if ($current_cat && !is_wp_error($current_cat)) {
            $args['tax_query'] = [[
                'taxonomy'         => 'product_cat',
                'field'            => 'slug',
                'terms'            => [ $current_cat->slug ],
                'include_children' => true,
            ]];
            return $args;
        }
    }

    // WP category by slug
    if ($post_cat_slug !== '') {
        $term = get_term_by('slug', $post_cat_slug, 'category');
        if ($term && !is_wp_error($term)) {
            $args['tax_query'] = [[
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => [ (int)$term->term_id ],
                'include_children' => true,
            ]];
            return $args;
        }
    }

    // Woo product tag by slug
    if ($woo_tag_slug !== '') {
        $current_tag = get_term_by('slug', $woo_tag_slug, 'product_tag');
        if ($current_tag && !is_wp_error($current_tag)) {
            $args['tax_query'] = [[
                'taxonomy' => 'product_tag',
                'field'    => 'term_id',
                'terms'    => [ (int)$current_tag->term_id ],
            ]];
            return $args;
        }
    }

    // WP post tag by slug
    if ($post_tag_slug !== '') {
        $term = get_term_by('slug', $post_tag_slug, 'post_tag');
        if ($term && !is_wp_error($term)) {
            $args['tag'] = sanitize_title($term->slug);
            return $args;
        }
    }

    // Brand by slug
    if ($brand_slug !== '') {
        $args['tax_query'] = [[
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => [ sanitize_title($brand_slug) ],
        ]];
        return $args;
    }

    // Search (AJAX)
    if ($search_query !== '') {
        $args['s'] = $search_query;
        return $args;
    }

    // No special context
    return $args;
}

 function cubeabf_scope_has_term_selectors_for_taxonomy(?array $scope, string $taxonomy): bool {
    if (empty($scope) || !is_array($scope)) return false;

    // direct per-term selectors
    if (!empty($scope['ids']) || !empty($scope['slugs']) || !empty($scope['names'])) {
        return true;
    }

    // taxonomy-scoped selectors
    if (!empty($scope['tax_ids'])) {
        foreach ((array)$scope['tax_ids'] as $taxKey => $ids) {
            $resolved = function_exists('cubeabf_resolve_pseudo_tax_key')
                ? (cubeabf_resolve_pseudo_tax_key((string)$taxKey, $taxonomy) ?: $taxKey)
                : $taxKey;

            if ($resolved === $taxonomy && !empty($ids)) return true;
        }
    }

    if (!empty($scope['tax_slugs'])) {
        foreach ((array)$scope['tax_slugs'] as $taxKey => $slugs) {
            $resolved = function_exists('cubeabf_resolve_pseudo_tax_key')
                ? (cubeabf_resolve_pseudo_tax_key((string)$taxKey, $taxonomy) ?: $taxKey)
                : $taxKey;

            if ($resolved === $taxonomy && !empty($slugs)) return true;
        }
    }

    // IMPORTANT: tax_all / pa_all are NOT term selectors
    return false;
}

 

function cubeabf_get_term_media_data_for_chips( $term, array $atts ): array {
    if ( !($term instanceof WP_Term) ) return ['url'=>'', 'mode'=>'img', 'alt'=>''];

   $chip_img      = isset($atts['chip_img']) ? trim((string)$atts['chip_img']) : '';
$chip_img_map  = isset($atts['chip_img_map']) ? trim((string)$atts['chip_img_map']) : '';
$chip_img_meta = isset($atts['chip_img_meta']) ? trim((string)$atts['chip_img_meta']) : '';

if ($chip_img === 'false') {
    return ['url'=>'', 'mode'=>'img', 'alt'=>(string)$term->name];
}

// If chip_img omitted BUT map/meta provided => enable chips automatically
if ($chip_img === '' && ($chip_img_map !== '' || $chip_img_meta !== '')) {
    $chip_img = 'thumbnail'; // or 'on' if you prefer; thumbnail makes sense for meta usage
}

    $size     = !empty($atts['chip_img_size']) ? sanitize_key($atts['chip_img_size']) : 'thumbnail';
    $meta_key = !empty($atts['chip_img_meta']) ? (string)$atts['chip_img_meta'] : 'thumbnail_id';

    $img_mode = isset($atts['chip_img_mode']) ? sanitize_key($atts['chip_img_mode']) : 'img';
    $map_mode = isset($atts['chip_img_map_mode']) ? sanitize_key($atts['chip_img_map_mode']) : '';
    $mode     = ($map_mode === 'img' || $map_mode === 'bg') ? $map_mode : $img_mode;

    $url = '';

    // 1) chip_img_map by slug/name
    $img_map = isset($atts['chip_img_map']) ? trim((string)$atts['chip_img_map']) : '';
    if ($img_map !== '') {
        $match_mode = isset($atts['chip_img_map_match']) ? sanitize_key($atts['chip_img_map_match']) : 'slug';
        $key = ($match_mode === 'name') ? mb_strtolower((string)$term->name) : mb_strtolower((string)$term->slug);

        foreach (array_filter(array_map('trim', explode('|', $img_map))) as $entry) {
            $pair = array_map('trim', explode('::', $entry, 2));
            if (count($pair) !== 2) continue;
            if (mb_strtolower($pair[0]) !== $key) continue;

            $u = $pair[1];
            if ($u !== '' && strpos($u, '://') === false && $u[0] === '/') $u = home_url($u);
            $url = $u;
            break;
        }
    }

    // 2) fallback chip_img="thumbnail" uses chip_img_meta
    if (!$url && strtolower($chip_img) === 'thumbnail') {
        $meta_val = get_term_meta($term->term_id, $meta_key, true);

        $attachment_id = 0;
        if (is_numeric($meta_val)) {
            $attachment_id = (int)$meta_val;
        } elseif (is_string($meta_val) && $meta_val !== '' && filter_var($meta_val, FILTER_VALIDATE_URL)) {
            $url = $meta_val;
        } elseif (is_array($meta_val)) {
            if (!empty($meta_val['ID']))  $attachment_id = (int)$meta_val['ID'];
            elseif (!empty($meta_val['url'])) $url = $meta_val['url'];
        }

        if ($attachment_id) {
            $url = wp_get_attachment_image_url($attachment_id, $size);
        }
    }

    // 3) fallback chip_img="url,...."
    if (!$url && stripos($chip_img, 'url,') === 0) {
        $raw = trim(substr($chip_img, 4));
        if ($raw !== '' && strpos($raw, '://') === false && $raw[0] === '/') $raw = home_url($raw);
        $url = $raw;
    }

    $url = $url ? esc_url_raw($url) : '';
    return [
        'url'  => $url,
        'mode' => ($mode === 'bg') ? 'bg' : 'img',
        'alt'  => (string)$term->name,
    ];
}

/**
 * Count posts per term_id inside a scoped universe of post IDs.
 * - For pa_* (attributes): rollup is normally false (no parents).
 * - For hierarchical taxonomies (cats/brands): set $rollup=true if you want parent gets children.
 */
function cubeabf_counts_by_term_id_in_scoped_ids(string $taxonomy, array $scoped_ids, bool $rollup = false): array {
    global $wpdb;

    $taxonomy = sanitize_key($taxonomy);
    $scoped_ids = array_values(array_unique(array_filter(array_map('absint', $scoped_ids))));
    if (!$taxonomy || empty($scoped_ids)) return [];

    // Attributes are usually NOT hierarchical; force rollup off for pa_*
    if (strpos($taxonomy, 'pa_') === 0) {
        $rollup = false;
    }

    $counts_by_term_id = [];

    // Safety for big IN() lists: chunk IDs
    $chunk_size = 800; // tune if needed
    $term_posts = [];  // used only when $rollup=true

    foreach (array_chunk($scoped_ids, $chunk_size) as $chunk) {

        $ids_placeholders = implode(',', array_fill(0, count($chunk), '%d'));

        // Pull (post_id, term_id) pairs for THIS taxonomy in this chunk
        $sql = "
            SELECT tr.object_id, tt.term_id
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tt.taxonomy = %s
              AND tr.object_id IN ($ids_placeholders)
        ";

        $params = array_merge([$taxonomy], $chunk);
        $rows   = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);

        foreach ($rows as $r) {
            $post_id = (int)($r['object_id'] ?? 0);
            $term_id = (int)($r['term_id'] ?? 0);
            if ($post_id <= 0 || $term_id <= 0) continue;

            if ($rollup) {
                // set-based rollup (no double count)
                $term_posts[$term_id][$post_id] = true;

                $ancestors = get_ancestors($term_id, $taxonomy, 'taxonomy');
                if (!empty($ancestors)) {
                    foreach ($ancestors as $aid) {
                        $aid = (int)$aid;
                        if ($aid > 0) $term_posts[$aid][$post_id] = true;
                    }
                }
            } else {
                // fast direct count (still safe from duplicates by using DISTINCT in SQL)
                // but we don't have DISTINCT here, so we count via per-term sets lightly
                $term_posts[$term_id][$post_id] = true;
            }
        }
    }

    // Convert sets to counts
    foreach ($term_posts as $tid => $set) {
        $counts_by_term_id[(int)$tid] = is_array($set) ? count($set) : 0;
    }

    return $counts_by_term_id;
}

// Shortcodes for common types (wrap cubeabf_taxonomy_shortcode)
add_shortcode( 'cubeabf_filter_helper' , function( $atts ) { 
    $atts = shortcode_atts( array(
        'filterurl'    => 'true',
    ), $atts );

   $filterurl = sanitize_key( $atts['filterurl'] );
    echo '<div class="cubeabf_filter_helper" data-filterurl="' . esc_attr($atts['filterurl']) . '"></div>';
});
 
 

function cubeabf_parse_value_img_map(string $raw): array {
    $map = [];
    $raw = trim((string)$raw);
    if ($raw === '') return $map;

    foreach (explode('|', $raw) as $pair) {
        $pair = trim($pair);
        if ($pair === '' || strpos($pair, '::') === false) continue;

        [$k, $u] = array_map('trim', explode('::', $pair, 2));
        if ($k === '' || $u === '') continue;

        // normalize key
        $key = mb_strtolower(trim($k));

        // normalize relative URL
        if (strpos($u, '://') === false && isset($u[0]) && $u[0] === '/') {
            $u = home_url($u);
        }

        $map[$key] = esc_url_raw($u);
    }

    return $map;
}

function cubeabf_build_meta_value_img_markup( $img_attr, $meta_value, $atts ) {
    $img_attr = is_string($img_attr) ? trim($img_attr) : '';
    $img_map  = isset($atts['img_map']) ? trim((string)$atts['img_map']) : '';

    if ( $img_attr === '' && $img_map === '' ) return '';
    if ( $img_attr === 'false' ) return '';

    $meta_value = trim((string)$meta_value);
    if ( $meta_value === '' ) return '';

    // Render modes
    $img_mode     = isset($atts['img_mode']) ? sanitize_key($atts['img_mode']) : 'img';       // img|bg
    $map_mode     = isset($atts['img_map_mode']) ? sanitize_key($atts['img_map_mode']) : '';  // img|bg|'' (optional)
    $render_mode  = ($map_mode === 'img' || $map_mode === 'bg') ? $map_mode : $img_mode;

    $bg  = ( $render_mode === 'bg' );
    $url = '';

    // 1) Try img_map match by meta value (case-insensitive)
    if ( $img_map !== '' ) {
        $current_key = mb_strtolower($meta_value);
        $entries = array_filter(array_map('trim', explode('|', $img_map)));

        foreach ( $entries as $entry ) {
            $pair = array_map('trim', explode('::', $entry, 2)); // key,url
            if ( count($pair) !== 2 ) continue;

            $k = mb_strtolower($pair[0]);
            $u = $pair[1];

            if ( $k !== '' && $u !== '' && $k === $current_key ) {
                if ( strpos($u, '://') === false && $u[0] === '/' ) {
                    $u = home_url($u);
                }
                $url = $u;
                break;
            }
        }
    }

    // 2) Fallback: img="url,..." applies to all values (optional)
    if ( ! $url && $img_attr !== '' ) {
        $parts = array_map('trim', explode(',', $img_attr));
        $mode  = strtolower($parts[0] ?? '');

        if ( $mode === 'url' ) {
            $raw = $parts[1] ?? '';
            if ( $raw !== '' ) {
                if ( strpos($raw, '://') === false && $raw[0] === '/' ) {
                    $raw = home_url($raw);
                }
                $url = $raw;
            }
            // explicit third param overrides bg
            if ( !empty($parts[2]) && strtolower($parts[2]) === 'bg' ) {
                $bg = true;
            }
        }
    }

    $url = $url ? esc_url($url) : '';
    if ( !$url ) return '';

    if ( $bg ) {
        return '<span class="cubeabf-label-media"><span class="cubeabf-label-bg" style="background-image:url(' . $url . ');"></span></span>';
    }
    $fallback_alt = $meta_value;
return cubeabf_render_label_media( $url, $bg, 0, $fallback_alt );

}

function cubeabf_chip_fallback_url(string $raw): string {
    $raw = trim((string)$raw);
    if ($raw === '' || $raw === 'false') return '';

    // allow: "url, /path.jpg" OR direct "https://..."
    $parts = array_map('trim', explode(',', $raw));
    if (strtolower($parts[0] ?? '') === 'url') {
        $u = $parts[1] ?? '';
    } else {
        $u = $raw;
    }

    if ($u !== '' && strpos($u, '://') === false && $u[0] === '/') {
        $u = home_url($u);
    }
    return $u ? esc_url_raw($u) : '';
}


function cubeabf_render_label_tag( $label, $tag_mode = 'false', $tag_map = '', $slug = '' ) {

    if ( empty( $tag_mode ) || $tag_mode === 'false' || empty( $tag_map ) ) {
        return '';
    }

    $value = trim($slug);

if (preg_match('/^#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $value)) {
    // Hex color
    $output = '#' . ltrim($value, '#');
} else {
    // Plain color name
    $output = $value;
}

    $tag_parts = array_map( 'trim', explode( '|', $tag_mode ) );

    $tag          = tag_escape( $tag_parts[0] );
    $use_bg_color = ! empty( $tag_parts[1] ) && $tag_parts[1] === 'true';

    $style = ( $use_bg_color && ! empty( $output ) )
        ? sprintf( ' style="background-color:%s;"', esc_attr( $output ) )
        : '';

    // Global class list (no mappings)
    if ( strpos( $tag_map, '|' ) === false && strpos( $tag_map, '::' ) === false ) {
        return sprintf(
            '<%1$s class="%2$s"%3$s></%1$s> ',
            $tag,
            esc_attr( trim( $tag_map ) ),
            $style
        );
    }

    $pairs = explode( '|', $tag_map );

    foreach ( $pairs as $pair ) {

        $parts = array_map( 'trim', explode( '::', $pair, 2 ) );

        if ( count( $parts ) < 2 ) {
            continue;
        }

        $map_value = $parts[0];
        $classes   = $parts[1];

        $label_match = strcasecmp( $map_value, $label ) === 0;
        $slug_match  = ! empty( $output) && strcasecmp( $map_value, $output ) === 0;

        if ( $label_match || $slug_match ) {

            return sprintf(
                '<%1$s class="%2$s"%3$s></%1$s> ',
                $tag,
                esc_attr( $classes ),
                $style
            );
        }
    }

    return '';
}


function cubeabf_url_slug($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (function_exists('transliterator_transliterate')) {
        $value = transliterator_transliterate(
            'Any-Latin; Latin-ASCII',
            $value
        );
    } else {
        $value = remove_accents($value);
    }

    return sanitize_title($value);
}

 


function cubeabf_get_config_scoped_ids_for_post_type(string $post_type): array {
    static $cache = [];

    if (isset($cache[$post_type])) return $cache[$post_type];

    $scope_cfg = cubeabf_get_scope_from_globals_co($post_type); // or globals on first load
    $args = [
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'cache_results'          => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];

    $args = cubeabf_apply_post_scope_to_args_config($args, $scope_cfg);
    $args = cubeabf_apply_scope_tax_to_args_config($args, $scope_cfg);

    $ids = (new WP_Query($args))->posts ?: [];
    $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));

    $cache[$post_type] = $ids;
    return $ids;
}

 function cubeabf_parse_chip_img_map(string $raw): array {
    $map = [];
    $raw = trim($raw);
    if ($raw === '') return $map;

    foreach (explode('|', $raw) as $pair) {
        $pair = trim($pair);
        if ($pair === '' || strpos($pair, '::') === false) continue;

        [$k, $u] = array_map('trim', explode('::', $pair, 2));
        $k = (int)$k;

        if ($k < 1 || $k > 5 || $u === '') continue;

        // normalize relative URLs
        if (strpos($u, '://') === false && $u[0] === '/') {
            $u = home_url($u);
        }

        $map[$k] = esc_url_raw($u);
    }

    return $map;
}

function cubeabf_render_rating_tag( $rating, $tag_mode = 'false', $tag_map = '' ) {

    if ( empty( $tag_map ) || empty( $rating ) ) {
        return '';
    }

    $allowed_tags = array(
        'span', 'i', 'div', 'strong', 'em', 'b'
    );

    $tag_mode = strtolower( trim( $tag_mode ) );

    if ( ! in_array( $tag_mode, $allowed_tags, true ) ) {
        $tag_mode = 'span';
    }

    $pairs = explode( '|', $tag_map );

    foreach ( $pairs as $pair ) {

        $parts = array_map( 'trim', explode( '::', $pair, 2 ) );

        if ( count( $parts ) < 2 ) {
            continue;
        }

        $map_rating = $parts[0];
        $class_list = $parts[1];

        if ( (string) $map_rating === (string) $rating ) {

            return sprintf(
                '<%1$s class="%2$s"></%1$s> ',
                esc_html( $tag_mode ),
                esc_attr( $class_list )
            );
        }
    }

    return '';
}

 


 add_filter('posts_where', function($where, $q) {
    global $wpdb;
 

    if (!$q->get('cubeabf_query')) {
    return $where;
}
 
    $rep_filters = $q->get('cubeabf_rep_meta_filters');

     

    if (empty($rep_filters) || !is_array($rep_filters)) {
        return $where;
    }

    

   foreach ($rep_filters as $f) {

    $key   = strtolower((string) ($f['key'] ?? ''));
    $vals  = (array) ($f['vals'] ?? []);
    $group = !empty($f['group']);
$any_row = !empty($f['any_row']);
    $vals = array_values(
        array_filter(
            array_map('sanitize_text_field', $vals)
        )
    );

    if (!$key) {
        continue;
    }

    /*
     * GROUP FILTER
     *
     * rep_or_use_0_lenta
     * ->
     * rep_or_use_0_lenta_%
     *
     * We only need to know that the post
     * has at least one child meta field.
     *
     * DO NOT check meta_value = 1.
     */
    if ($group) {

        $like = $wpdb->esc_like($key . '_') . '%';

        $where .= $wpdb->prepare(
            "
            AND EXISTS (
                SELECT 1
                FROM {$wpdb->postmeta} pm2
                WHERE pm2.post_id = {$wpdb->posts}.ID
                  AND pm2.meta_key LIKE %s
                  AND pm2.meta_value != ''
            )
            ",
            $like
        );

        continue;
    }

    /*
     * Normal repeater filter
     */
    if (!$vals) {
        continue;
    }

 

  if ($any_row) {

    // ANY ROW
    $like = preg_replace(
        '/_(\d+)(?=_|$)/',
        '\_%',
        $key
    );

    $meta_key_comparison = "pm2.meta_key LIKE %s";
    $meta_key_value = $like;

} else {

    // EXACT ROW
    $meta_key_comparison = "pm2.meta_key = %s";
    $meta_key_value = $key;
}

$place = implode(',', array_fill(0, count($vals), '%s'));

$sql = "
    AND EXISTS (
        SELECT 1
        FROM {$wpdb->postmeta} pm2
        WHERE pm2.post_id = {$wpdb->posts}.ID
          AND {$meta_key_comparison}
          AND pm2.meta_value IN ($place)
    )
";

$where .= $wpdb->prepare(
    $sql,
    array_merge([$meta_key_value], $vals)
);
}


    return $where;

}, 10, 2);

 function cubeabf_tax_query_clause_smart(string $taxonomy, array $terms_in, string $op): array {
    $op = strtoupper($op) === 'AND' ? 'AND' : 'IN';

    // 1) Normalize incoming values to strings
    $raw = [];
    foreach ((array)$terms_in as $v) {
        if (!is_scalar($v)) continue;
        $s = trim((string)$v);
        if ($s !== '') $raw[] = $s;
    }
    if (!$raw) return [];

    // 2) Resolve EVERYTHING to term_ids in this taxonomy (robust for pa_* and numeric slugs)
    $term_ids = [];
    foreach ($raw as $s) {

        $term = null;

        // If it's digits, try term_id first
        if (ctype_digit($s)) {
            $t = get_term((int)$s, $taxonomy);
            if ($t && !is_wp_error($t)) {
                $term = $t;
            }
        }

        // Fallback: treat it as slug (works for "40" slugs too)
        if (!$term) {
            $slug = sanitize_title($s);
            if ($slug !== '') {
                $t = get_term_by('slug', $slug, $taxonomy);
                if ($t && !is_wp_error($t)) {
                    $term = $t;
                }
            }
        }

        if ($term) {
            $term_ids[] = (int)$term->term_id;
        }
    }

    $term_ids = array_values(array_unique(array_filter($term_ids)));
    if (!$term_ids) return [];

    $is_hier = is_taxonomy_hierarchical($taxonomy);

    // -------------------------
    // Non-hierarchical (pa_* usually): simple clause
    // -------------------------
    if (!$is_hier) {
        return [
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_ids,
            'operator' => $op, // IN or AND
        ];
    }

    // -------------------------
    // Hierarchical taxonomy
    // IN = allow include_children
    // AND = expand each selected term into its subtree and AND them
    // -------------------------
    if ($op === 'IN') {
        return [
            'taxonomy'         => $taxonomy,
            'field'            => 'term_id',
            'terms'            => $term_ids,
            'operator'         => 'IN',
            'include_children' => true,
        ];
    }

    // AND + hierarchical:
    // (tree(termA) IN) AND (tree(termB) IN) ...
    $group = ['relation' => 'AND'];

    foreach ($term_ids as $tid) {
        $ids = [ (int)$tid ];

        $children = get_term_children((int)$tid, $taxonomy);
        if (!is_wp_error($children) && !empty($children)) {
            $ids = array_merge($ids, array_map('intval', $children));
        }

        $group[] = [
            'taxonomy'         => $taxonomy,
            'field'            => 'term_id',
            'terms'            => array_values(array_unique($ids)),
            'operator'         => 'IN',
            'include_children' => false,
        ];
    }

    return (count($group) > 1) ? $group : [];
}

// Pass CF to live search

 add_filter( 'posts_search', 'cubeabf_extend_search_to_meta', 10, 2 );

function cubeabf_extend_search_to_meta( $search, $query ) {

    global $wpdb;

    if ( ! $query->get( 'cubeabf_query' ) ) {
        return $search;
    }

    $search_term = $query->get( 's' );

    if ( empty( $search_term ) ) {
        return $search;
    }

    $custom_fields = (array) $query->get( 'custom_fields' );

    if ( empty( $custom_fields ) ) {
        return $search;
    }

    // support both 31,12 and 31.12
    $search_terms = array_unique( [
        $search_term,
        str_replace( ',', '.', $search_term ),
        str_replace( '.', ',', $search_term ),
        str_replace( [',', '.'], '', $search_term ),
    ] );

    $meta_conditions = [];
    $tax_conditions  = [];

    foreach ( $custom_fields as $field ) {

        // ---------------------------------------
        // Attribute taxonomy (pa_color, pa_size...)
        // ---------------------------------------
        if ( strpos( $field, 'pa_' ) === 0 ) {

            foreach ( $search_terms as $term ) {

                $tax_conditions[] = $wpdb->prepare(
                    '(tt.taxonomy = %s AND t.name LIKE %s)',
                    $field,
                    '%' . $wpdb->esc_like( $term ) . '%'
                );

            }

            continue;
        }

        // ---------------------------------------
        // Normal custom field
        // ---------------------------------------
        foreach ( $search_terms as $term ) {

          if (
    strpos( $term, ',' ) === false &&
    strpos( $term, '.' ) === false &&
    ctype_digit( $term )
) {

    $from = floor( (int) $term / 50 ) * 50;
    $to   = $from + 49;

    $meta_conditions[] = $wpdb->prepare(
        "(pm.meta_key = %s AND (
            REPLACE(REPLACE(pm.meta_value, ',', ''), '.', '') LIKE %s
            OR (
                CAST(REPLACE(REPLACE(pm.meta_value, ',', ''), '.', '') AS UNSIGNED)
                BETWEEN %d AND %d
            )
        ))",
        $field,
        '%' . $wpdb->esc_like( $term ) . '%',
        $from,
        $to
    );

} else {

    $meta_conditions[] = $wpdb->prepare(
        '(pm.meta_key = %s AND pm.meta_value LIKE %s)',
        $field,
        '%' . $wpdb->esc_like( $term ) . '%'
    );

}

        }
    }

    $or_parts = [];

    // Meta search
    if ( ! empty( $meta_conditions ) ) {

        $or_parts[] = "
        EXISTS (
            SELECT 1
            FROM {$wpdb->postmeta} pm
            WHERE pm.post_id = {$wpdb->posts}.ID
            AND (
                " . implode( ' OR ', $meta_conditions ) . "
            )
        )";

    }

    // Attribute search
    if ( ! empty( $tax_conditions ) ) {

        $or_parts[] = "
        EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t
                ON tt.term_id = t.term_id
            WHERE tr.object_id = {$wpdb->posts}.ID
            AND (
                " . implode( ' OR ', $tax_conditions ) . "
            )
        )";

    }

    if ( empty( $or_parts ) ) {
        return $search;
    }

    $original_search = preg_replace( '/^\s*AND\s+/i', '', $search );

    $search = " AND (
        ({$original_search})
        OR
        (" . implode( ' OR ', $or_parts ) . ")
    )";

    return $search;
}

add_action('wp_ajax_cubeabf_load_posts', 'cubeabf_load_posts');
add_action('wp_ajax_nopriv_cubeabf_load_posts', 'cubeabf_load_posts');

function cubeabf_load_posts() {
if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'cubeabf_nonce')) {
        wp_send_json_error('Nonce verification failed', 400);
        wp_die();
    }

    nocache_headers();
 
    
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    $post_type = isset($_POST['ptype']) ? sanitize_text_field($_POST['ptype']) : 'post';
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 6;
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

 $postcatslug = sanitize_text_field( wp_unslash($_POST['post_cat']) ?? wp_unslash($_GET['category']) ?? '' );

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

 $lmore = isset($_POST['lmore']) ? sanitize_text_field($_POST['lmore']) : 'right';

  $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
   $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'post_date';
 

 $parmore = isset($_POST['parmore']) ? sanitize_text_field($_POST['parmore']) : 'true';
$ploader = isset($_POST['ploader']) ? sanitize_text_field($_POST['ploader']) : 'Loading...';
$lmoretext = isset($_POST['lmoretext']) ? sanitize_text_field($_POST['lmoretext']) : 'Load More';
$ploaderimg = isset($_POST['ploaderimg']) ? sanitize_text_field($_POST['ploaderimg']) : 'false';
$pagelefticon = isset($_POST['pagelefticon']) ? sanitize_text_field($_POST['pagelefticon']) : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg');
$pagerighticon = isset($_POST['pagerighticon']) ? sanitize_text_field($_POST['pagerighticon']) : (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg');
$pagelefttext = isset($_POST['pagelefttext']) ? sanitize_text_field($_POST['pagelefttext']) : 'false';
$pagerighttext = isset($_POST['pagerighttext']) ? sanitize_text_field($_POST['pagerighttext']) : 'false';
$rightleftempty = isset($_POST['rightleftempty']) ? sanitize_text_field($_POST['rightleftempty']) : 'true';
$rightlefthide = isset($_POST['rightlefthide']) ? sanitize_text_field($_POST['rightlefthide']) : 'false';
$firstlast = isset($_POST['firstlast']) ? sanitize_text_field($_POST['firstlast']) : 'false';
$firsttext = isset($_POST['firsttext']) ? sanitize_text_field($_POST['firsttext']) : 'false';
$lasttext = isset($_POST['lasttext']) ? sanitize_text_field($_POST['lasttext']) : 'false';
$first_icon = isset($_POST['firstticon']) ? sanitize_text_field($_POST['firsticon']) : (CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-start.svg');
$last_icon = isset($_POST['lasticon']) ? sanitize_text_field($_POST['lasticon']) : (CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-end.svg');
$enable_pagination        = isset($_POST['enable_pagination']) ? sanitize_text_field($_POST['enable_pagination']) : 'true';
$enable_load_more         = isset($_POST['enable_load_more']) ? sanitize_text_field($_POST['enable_load_more']) : 'true';
$enable_pagination_mobile = isset($_POST['enable_pagination_mobile']) ? sanitize_text_field($_POST['enable_pagination_mobile']) : 'true';
$enable_load_more_mobile  = isset($_POST['enable_load_more_mobile']) ? sanitize_text_field($_POST['enable_load_more_mobile']) : 'true';
$enable_pagination_pc     = isset($_POST['enable_pagination_pc']) ? sanitize_text_field($_POST['enable_pagination_pc']) : 'true';
$enable_load_more_pc      = isset($_POST['enable_load_more_pc']) ? sanitize_text_field($_POST['enable_load_more_pc']) : 'true';
$arrowsonly      = isset($_POST['arrowsonly']) ? sanitize_text_field($_POST['arrowsonly']) : 'false';

$maxwidth = isset($_POST['maxwidth']) ? sanitize_text_field($_POST['maxwidth']) : 767;
$ajaxoff = isset($_POST['ajaxoff']) ? sanitize_text_field($_POST['ajaxoff']) : 'false';
 
 

// defaults
$mid_desktop = isset($_POST['mid_desktop']) ? sanitize_text_field($_POST['mid_desktop']) : '2';
$mid_mobile  = isset($_POST['mid_mobile']) ? sanitize_text_field($_POST['mid_mobile']) : '1';
$mobile_bp   = isset($_POST['mobile_bp']) ? sanitize_text_field($_POST['mobile_bp']) : '767';
 

$infinite = isset($_POST['infinite']) ? sanitize_text_field($_POST['infinite']) : 'true';
$enable_infinite_mobile  = isset($_POST['enable_infinite_mobile']) ? sanitize_text_field($_POST['enable_infinite_mobile']) : 'true';
$enable_infinite_pc     = isset($_POST['enable_infinite_pc']) ? sanitize_text_field($_POST['enable_infinite_pc']) : 'true';
$search_query = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : get_query_var('s');

$tax_relation = strtoupper($_POST['tax_relation'] ?? 'AND');
$tax_relation = ($tax_relation === 'OR') ? 'OR' : 'AND';

$tax_ops = $_POST['tax_operators'] ?? [];
if (!is_array($tax_ops)) $tax_ops = [];
 
// -------------------------------------------
// 0) Base query args (same for all contexts)
// -------------------------------------------
$args = [
    'post_type'           => $post_type,
    'posts_per_page'      => $per_page,
    'ignore_sticky_posts' => true,
    'paged'               => max(1, (int) $paged),
    'post_status'         => 'publish',
    'order' => $order,
    'orderby'=> $orderby,
];
 

$sc = cubeabf_get_scope_from_request_config($post_type);

$argsy = cubeabf_apply_post_scope_to_args_config($args, $sc);
$argsy = cubeabf_apply_scope_tax_to_args_config($argsy, $sc);

$maxquery = new WP_Query($argsy);

$total_posts = (int) $maxquery->found_posts;
$max_pages   = (int) ceil($total_posts / $per_page);

// -------------------------------------------
// 3) Filters input normalization (AJAX)
// -------------------------------------------
$filters = $_POST['filters'] ?? [];
$filters = is_string($filters) ? json_decode(wp_unslash($filters), true) : $filters;
$filters = is_array($filters) ? $filters : [];

 $search = ! empty( $filters['keyword'] )
    ? sanitize_text_field( $filters['keyword'] )
    : '';

    $args['s'] = $search;

    $custom_f = [];

if ( ! empty( $_POST['custom_fields'] ) ) {
    $custom_f = array_filter(
        array_map(
            'trim',
            explode( ',', sanitize_text_field( wp_unslash( $_POST['custom_fields'] ) ) )
        )
    );
}

$args['custom_fields'] = $custom_f;

 $sorting_raw = (string) ($filters['sorting'] ?? '');

 $sorting = cubeabf_parse_sorting_value($sorting_raw);

if (strpos($sorting_raw, 'meta:_price:') === 0) {

    $parts = explode(':', $sorting_raw);

    $dir = strtoupper(trim($parts[3] ?? 'ASC'));

$dir = ($dir === 'DESC') ? 'DESC' : 'ASC';

    $args['meta_key'] = '_price';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = $dir;

} else {

    if (!empty($sorting['orderby'])) {
        $args['orderby'] = $sorting['orderby'];
    }

    if (!empty($sorting['meta_query'])) {

        if (!isset($args['meta_query'])) {
            $args['meta_query'] = [];
        }

        $args['meta_query'] = array_merge(
            $args['meta_query'],
            $sorting['meta_query']
        );
    }
}


if (strpos($sorting_raw, 'meta:_wc_average_rating:') === 0) {

    $parts = explode(':', $sorting_raw);

    $dir = strtoupper(trim($parts[3] ?? 'DESC'));

    $dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

    $args['meta_key'] = '_wc_average_rating';

    $args['orderby'] = 'meta_value_num';

    $args['order'] = $dir;

    // IMPORTANT
    $args['meta_query'][] = [
        'key'     => '_wc_average_rating',
        'value'   => 0,
        'compare' => '>',
        'type'    => 'NUMERIC',
    ];
}

// -------------------------------------------
// 1) Context constraints (category/tag/brand/search)
//    IMPORTANT: we only ADD to args, never overwrite later
// -------------------------------------------
if(!$filters) {
// Woo product category archive
if ( $current_cat ) {
    $args['tax_query'] = [
        [
            'taxonomy'         => 'product_cat',
            'field'            => 'slug',
            'terms'            => [ $current_cat->slug ],
            'include_children' => true,
        ],
    ];
}
// WP category archive by slug
elseif ( ! empty($postcatslug) ) {
    $term = get_term_by('slug', $postcatslug, 'category');
    if ( $term && ! is_wp_error($term) ) {
        $args['tax_query'] = [
            [
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => [ (int) $term->term_id ],
                'include_children' => true,
            ],
        ];
    }
}
// Woo product tag archive
elseif ( $current_tag ) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_tag',
            'field'    => 'term_id',
            'terms'    => [ (int) $current_tag->term_id ],
        ],
    ];
}
// WP post tag archive
elseif ( ! empty($tag_slug) ) {
    $args['tag'] = sanitize_title($tag_slug);
}
// Brand archive (your taxonomy)
elseif ( ! empty($current_brand) ) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => [ sanitize_title($current_brand) ],
        ],
    ];
}
// Search
elseif ( ! empty($search_query) ) {
    $args['s'] = sanitize_text_field($search_query);
}
}
// -------------------------------------------
// 2) Ports (your custom "more" param) => meta_query clause
// -------------------------------------------

$final_meta = [ 'relation' => 'AND' ];

$m_param = get_option( 'cubeabf_more_param', 'after' );

if ( $parmore !== 'false' ) {
    $ports = get_query_var($m_param);
    if ( $ports !== '' && $ports !== null ) {
        $final_meta[] = [
            'key'     => 'port_number',
            'value'   => sanitize_text_field($ports),
            'compare' => '=',
        ];
    }
}


$scope_io = $_POST['scope_io'] ?? null;
$include_csv = '';
$exclude_csv = '';

if (is_string($scope_io)) {
    $scope_io = json_decode(wp_unslash($scope_io), true);
}

if (is_array($scope_io)) {
    $include_csv = (string)($scope_io['include'] ?? '');
    $exclude_csv = (string)($scope_io['exclude'] ?? '');
}



 // Union
$filters_mode = sanitize_key($_POST['filters_mode'] ?? 'and');



$meta_db_keys = [];
if (!empty($filters['__meta_db_keys']) && is_array($filters['__meta_db_keys'])) {
  $meta_db_keys = $filters['__meta_db_keys'];
}
// keep filters clean for normal keys, but pass mapping to union
unset($filters['__meta_db_keys']);
  
$base_args = $args; // keep CLEAN associative array (no [] appends)

$has_any_filter = false;
foreach ($filters as $k => $v) {
    if ($v !== '' && $v !== [] && $v !== null) { $has_any_filter = true; break; }
}

 

if ($filters_mode === 'union' && $has_any_filter) {

$final_meta = [];

$repeater_meta_filters = []; // collect repeater subfield filters here
    // rating scope ONLY when rating is active

    
    
    $scope = null;
    if (!empty($filters['rating'])) {
        $scope = cubeabf_get_scope_from_request($post_type);
    }

    // PASS these into union fn
    $rating_mode = sanitize_key($_POST['rating_mode'] ?? 'bucket');       // bucket|min
    $rating_mode = ($rating_mode === 'min') ? 'min' : 'bucket';

    $rating_rel  = strtoupper($_POST['rating_meta_relation'] ?? 'OR');    // OR|AND (mostly relevant for bucket multi-select)
    $rating_rel  = ($rating_rel === 'AND') ? 'AND' : 'OR';

    // IMPORTANT: do NOT add your normal filter AND logic below.
    $union_ids = cubeabf_union_filter_ids($base_args, $filters, $scope, $rating_mode, $rating_rel, $meta_db_keys);

    if (!$union_ids) {
        // return empty response...
    }
 
    

    $final = $base_args;

    // union result = only selector
    $final['post__in'] = $union_ids;

    // safety: union should not be re-ANDed by leftovers
    unset($final['tax_query'], $final['meta_query']);

    $final['posts_per_page'] = (int) $per_page;
    $final['paged']          = max(1, (int) $paged);
 
    

   foreach ($filters as $key => $range) {

    if (!str_ends_with($key, '_range')) {
        continue;
    }

    if (!is_array($range)) {
        continue;
    }

    // Привязываем правильный мета-ключ для рейтинга WooCommerce, если ключ rating_range
    if ($key === 'rating_range') {
        $meta_key = '_wc_average_rating';
    } elseif ($key === 'price_range') {
        $meta_key = '_price';
    } else {
        $meta_key = sanitize_key($range['meta_key'] ?? $key);
    }

   

$d_type = ($meta_key === '_wc_average_rating') ? 'NUMERIC' : 'DECIMAL(10,2)';
    // Проверяем наличие значений в массиве через isset, без жесткого приведения к 0 по умолчанию
    $min_val = isset($range['min']) && $range['min'] !== '' ? (float) $range['min'] : null;
    $max_val = isset($range['max']) && $range['max'] !== '' ? (float) $range['max'] : null;

    // Если оба значения не переданы, пропускаем фильтр
    if ($min_val === null && $max_val === null) {
        continue;
    }

    // Если передан диапазон (и min, и max)
    if ($min_val !== null && $max_val !== null) {
        
        // КРИТИЧНО ДЛЯ РЕЙТИНГА: если min = 0, делаем строгое сравнение, чтобы исключить товары без отзывов
        if ($meta_key === '_wc_average_rating' && $min_val === 0.0) {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $d_type,
            ];
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => $max_val,
                'compare' => '<=',
                'type'    => $d_type,
            ];
        } else {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => [$min_val, $max_val],
                'compare' => 'BETWEEN',
                'type'    => $d_type,
            ];
        }

    } elseif ($min_val !== null) {
        // Передан только min
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $min_val,
            'compare' => '>=',
            'type'    => $d_type,
        ];
    } else {
        // Передан только max
        // Если это рейтинг и указан только max, все равно отсекаем товары без оценок (> 0)
        if ($meta_key === '_wc_average_rating') {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $d_type,
            ];
        }
        
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $max_val,
            'compare' => '<=',
            'type'    => $d_type,
        ];
    }
}


if (!empty($final_meta)) {

    $final['meta_query'] = array_merge(
        ['relation' => 'AND'],
        $final_meta
    );
}
      
  $scopeconfig = cubeabf_get_scope_from_request_config($post_type);

$final = cubeabf_apply_post_scope_to_args_config($final, $scopeconfig);
$final = cubeabf_apply_scope_tax_to_args_config($final, $scopeconfig);
   
 if ( !empty($repeater_meta_filters) ) {
    $final['cubeabf_rep_meta_filters'] = $repeater_meta_filters;
}

$final['cubeabf_query'] = true;
    $query = new WP_Query($final);



    

}  else {
 
 
// -------------------------------------------
// 4) Meta filters: keys like meta_yourkey
// -------------------------------------------
 
$meta_group_map = [];

if (
    isset($filters['__meta_group_map']) &&
    is_array($filters['__meta_group_map'])
) {
    $meta_group_map = $filters['__meta_group_map'];

    unset($filters['__meta_group_map']);
}
 
 
 
  
$repeater_meta_filters = []; // collect repeater subfield filters here

foreach ( $filters as $raw_key => $value ) {

     $raw_key = sanitize_key($raw_key);

    if ( strpos($raw_key, 'meta_') !== 0 ) continue;

    // default
    $meta_key = substr($raw_key, 5);

    // ✅ override from JS mapping (use original key string if you stored it that way)
    if (isset($meta_db_keys[$raw_key]) && $meta_db_keys[$raw_key] !== '') {
        $meta_key = (string) $meta_db_keys[$raw_key];
    }

     
    $vals = is_array($value) ? $value : [ $value ];
    $vals = array_values(array_filter(array_map('sanitize_text_field', $vals)));
    if ( ! $vals ) continue;

     $is_group = !empty(
        $meta_group_map[$raw_key]
    );

     if ($is_group) {

        $repeater_meta_filters[] = [
            'key'     => $meta_key,
            'vals'    => $vals,
            'any_row' => false,
            'group'   => true,
        ];

        continue;
    }

  if (preg_match('/_\d+_/', $meta_key)) {

 $group_key = sanitize_key($raw_key);

 $any_row_map = isset($_POST['any_row']) && is_array($_POST['any_row'])
    ? $_POST['any_row']
    : [];

$any_row = filter_var(
    $any_row_map[$meta_key] ?? false,
    FILTER_VALIDATE_BOOLEAN
);


$repeater_meta_filters[] = [
    'key'     => strtolower($meta_key),
    'vals'    => $vals,
    'any_row' => $any_row,
    'group'   => !empty($meta_group_map[$group_key]),
];
    
 
    continue;
}

    $repeater_roots = $_POST['repeater_roots'] ?? [];
$is_repeater_root = in_array($meta_key, $repeater_roots, true);

 

    if ($is_repeater_root) {

    // Any repeater with at least one row
    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => 0,
        'type'    => 'NUMERIC',
        'compare' => '>',
    ];

} elseif (count($vals) === 1) {

    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => $vals[0],
        'compare' => '=',
    ];

} else {

    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => $vals,
        'compare' => 'IN',
    ];

}
}

if ( count($final_meta) > 1 ) {
    $args['meta_query'] = $final_meta;
}


// --------------------
// Price
// --------------------
 foreach ($filters as $key => $range) {

    if (!str_ends_with($key, '_range')) {
        continue;
    }

    if (!is_array($range)) {
        continue;
    }

    // Привязываем правильный мета-ключ для рейтинга WooCommerce, если ключ rating_range
    if ($key === 'rating_range') {
        $meta_key = '_wc_average_rating';
    } elseif ($key === 'price_range') {
        $meta_key = '_price';
    } else {
        $meta_key = sanitize_key($range['meta_key'] ?? $key);
    }

    // Проверяем наличие значений в массиве через isset, без жесткого приведения к 0 по умолчанию
    $min_val = isset($range['min']) && $range['min'] !== '' ? (float) $range['min'] : null;
    $max_val = isset($range['max']) && $range['max'] !== '' ? (float) $range['max'] : null;

 
$data_type = ($meta_key === '_wc_average_rating') ? 'NUMERIC' : 'DECIMAL(10,2)';
    // =========================================================================
    // ДОБАВИТЬ ЭТУ ПРОВЕРКУ: 
    // Если min и max пришли как 0 (и это не фильтр рейтинга), полностью игнорируем фильтр
    // =========================================================================
    if ($meta_key !== '_wc_average_rating' && $min_val === 0.0 && $max_val === 0.0) {
        continue;
    }
 

    // Если оба значения не переданы, пропускаем фильтр
    if ($min_val === null && $max_val === null) {
        continue;
    }

    // Если передан диапазон (и min, и max)
    if ($min_val !== null && $max_val !== null) {
        
        // КРИТИЧНО ДЛЯ РЕЙТИНГА: если min = 0, делаем строгое сравнение, чтобы исключить товары без отзывов
        if ($meta_key === '_wc_average_rating' && $min_val === 0.0) {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $data_type,
            ];
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => $max_val,
                'compare' => '<=',
                'type'    => $data_type,
            ];
        } else {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => [$min_val, $max_val],
                'compare' => 'BETWEEN',
                'type'    => $data_type,
            ];
        }

    } elseif ($min_val !== null) {
        // Передан только min
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $min_val,
            'compare' => '>=',
            'type'    => $data_type,
        ];
    } else {
        // Передан только max
        // Если это рейтинг и указан только max, все равно отсекаем товары без оценок (> 0)
        if ($meta_key === '_wc_average_rating') {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $data_type,
            ];
        }
        
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $max_val,
            'compare' => '<=',
            'type'    => $data_type,
        ];
    }
}

$scope = null;

 if ( ! empty($filters['rating']) ) {
  $scope = cubeabf_get_scope_from_request($post_type);
  
 }

$args_ids = $args;
$args_ids['fields'] = 'ids';
$args_ids['posts_per_page'] = -1;
$args_ids['no_found_rows'] = true;
$args_ids['cache_results'] = false;
$args_ids['update_post_meta_cache'] = false;
$args_ids['update_post_term_cache'] = false;

 
$matching_ids = (new WP_Query($args_ids))->posts ?: [];
$matching_ids = array_values(array_unique(array_map('absint', $matching_ids)));


 
if($scope) {
  $matching_ids = cubeabf_apply_scope_to_id_list($matching_ids, $scope);
 }


// --------------------
// Rating (add into $final_meta, NOT $args['meta_query'])
// --------------------

if (isset($matching_ids) && !empty($matching_ids)) {
if (!empty($filters['rating'])) {

  $vals = [];
  foreach ((array)$filters['rating'] as $v) {
    if (preg_match('/(\d+)/', (string)$v, $m)) {
      $n = (int)$m[1];
      if ($n >= 1 && $n <= 5) $vals[] = $n;
    }
  }
  $vals = array_values(array_unique($vals));

  if ($vals) {
    $mode = sanitize_key($_POST['rating_mode'] ?? 'bucket'); // bucket|min
    $mode = ($mode === 'min') ? 'min' : 'bucket';

    $rel  = strtoupper($_POST['rating_meta_relation'] ?? 'OR'); // OR|AND
    $rel  = ($rel === 'AND') ? 'AND' : 'OR';

    if ($mode === 'min') {
      $threshold = ($rel === 'AND') ? max($vals) : min($vals);

      $final_meta[] = [
        'key'     => '_wc_average_rating',
        'value'   => (float)$threshold,
        'type'    => 'NUMERIC',
        'compare' => '>=',
      ];
    } else {
      $group = ['relation' => $rel];

     foreach ($vals as $r) {

    $min   = (float) $r;
    $upper = ($r === 5) ? 5.01 : (float) ($r + 1);

    $group[] = [
        'relation' => 'AND',
        [
            'key'     => '_wc_average_rating',
            'value'   => $min,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ],
        [
            'key'     => '_wc_average_rating',
            'value'   => $upper,
            'compare' => '<',
            'type'    => 'NUMERIC',
        ],
    ];
}

      $final_meta[] = $group;
    }
  }
} }

// Apply once at end
if (count($final_meta) > 1) {
  $args['meta_query'] = $final_meta;
} else {
  unset($args['meta_query']);
}

  

// -------------------------------------------
// 7) Taxonomy filters (product_cat/product_tag/product_brand/pa_*)
//    Merge with existing context tax_query (do NOT overwrite)
// -------------------------------------------
$tax_clauses = [];

foreach ( $filters as $taxonomy => $values ) {

    if ( empty($values) ) continue;

    // skip non-tax keys
 if (str_ends_with($taxonomy, '_range') || $taxonomy === 'rating') continue;
    if ( strpos((string)$taxonomy, 'meta_') === 0 ) continue;

    $taxonomy = cubeabf_sanitize_tax_keep_dashes($taxonomy);

    // normalize terms
    $terms = is_array($values) ? $values : [ $values ];
    $terms = array_values(array_filter(array_map('sanitize_title', $terms)));
    if ( ! $terms ) continue;

    $is_allowed =
        ($taxonomy === 'product_cat') ||
        ($taxonomy === 'product_tag') ||
        ($taxonomy === 'product_brand') ||
        ($taxonomy === 'category') ||
        ($taxonomy === 'post_tag') ||
        (function_exists('str_starts_with') ? str_starts_with($taxonomy, 'pa_') : (strpos($taxonomy, 'pa_') === 0));

    if ( ! $is_allowed ) continue;

    // Allow pa_ even if taxonomy_exists is flaky during some AJAX init orders
    if ( ! taxonomy_exists($taxonomy) && strpos($taxonomy, 'pa_') !== 0 ) continue;

    $op = strtoupper($tax_ops[$taxonomy] ?? 'IN');
$op = ($op === 'AND') ? 'AND' : 'IN';
  
 $clause = cubeabf_tax_query_clause_smart($taxonomy, $terms, $op);
if (!$clause) continue;

$tax_clauses[] = $clause;

     
}

if ( $tax_clauses ) {
    $existing_tax = isset($args['tax_query']) ? (array) $args['tax_query'] : [];
    if ( isset($existing_tax['relation']) ) unset($existing_tax['relation']);

    // merge base/context + filters
    $merged_tax = array_merge($existing_tax, $tax_clauses);

    $args['tax_query'] = array_merge([ 'relation' => $tax_relation ], $merged_tax);
}


// -------------------------------------------
// 8) Run query
// -------------------------------------------


 

$scopeconfig = cubeabf_get_scope_from_request_config($post_type);

$args = cubeabf_apply_post_scope_to_args_config($args, $scopeconfig);
$args = cubeabf_apply_scope_tax_to_args_config($args, $scopeconfig);

 

if ( !empty($repeater_meta_filters) ) {
    $args['cubeabf_rep_meta_filters'] = $repeater_meta_filters;
}
 
 

$args['cubeabf_query'] = true;
 

$query = new WP_Query($args);

 
}



   ob_start();

if ( $query->have_posts() ) :
	while ( $query->have_posts() ) :
		$query->the_post();

		$current_type = get_post_type();
		get_template_part( 'cubeabf-parts/content', $current_type );

	endwhile;
 
endif;

wp_reset_postdata();

$html = ob_get_clean();
 
    ob_start();
 
 
   $pagination_post_type = $post_type;
    $pagination_max_pages = $max_pages;
    $pagination_lmore = $lmore;
    $pagination_ploader = $ploader;
    $pagination_lmoretext = $lmoretext;
    $pagination_ploaderimg = $ploaderimg;
    $pagination_pagelefticon = $pagelefticon;
    $pagination_pagerighticon = $pagerighticon;
    $pagination_pagelefttext = $pagelefttext;
    $pagination_pagerighttext = $pagerighttext;
    $pagination_rightleftempty = $rightleftempty;
    $pagination_rightlefthide = $rightlefthide;
    $pagination_firstlast = $firstlast;

    $pagination_firsttext = $firsttext;
    $pagination_lasttext = $lasttext;
     $pagination_first_icon = $first_icon;
    $pagination_last_icon = $last_icon;

    $pagination_enable_pagination = $enable_pagination;
$pagination_enable_load_more = $enable_load_more;
$pagination_enable_pagination_mobile = $enable_pagination_mobile;
$pagination_enable_load_more_mobile = $enable_load_more_mobile;
$pagination_enable_pagination_pc = $enable_pagination_pc;
$pagination_enable_load_more_pc = $enable_load_more_pc;
$pagination_maxwidth = $maxwidth;
$pagination_ajaxoff = $ajaxoff;
$pagination_arrowsonly = $arrowsonly;
$pagination_infinite = $infinite;
$pagination_enable_infinite_mobile = $enable_infinite_mobile;
$pagination_enable_infinite_pc = $enable_infinite_pc;
$pagination_isfilter = $filters;
$pagination_mid_desktop = $mid_desktop;
$pagination_mid_mobile = $mid_mobile;
$pagination_mobile_bp = $mobile_bp;
 
 
    include CUBEPAGI_PLUGIN_DIR . 'template/pagination-template.php';   ?>
   
   <?php $pagination = ob_get_clean();

   
wp_reset_postdata(); 
    wp_send_json([
        'html' => $html,
        'pagination' => $pagination,
        'max_pages' => $query->max_num_pages,
        'posts_per_page' => $per_page,
        'category_slug' => sanitize_title($slug),
        'post_cat' => $postcatslug,
 
    ]);
    wp_die();
}


 
 
function cubeabf_ensure_scope_config_globals(): void {
    if (!empty($GLOBALS['cubeabf_scope_config_csv'])) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        $GLOBALS['cubeabf_scope_config_csv'] = ['include' => '', 'exclude' => ''];
        return;
    }

    $post = get_post($post_id);
    if (!$post || empty($post->post_content)) {
        $GLOBALS['cubeabf_scope_config_csv'] = ['include' => '', 'exclude' => ''];
        return;
    }

    if (!has_shortcode($post->post_content, 'cubeabf_config')) {
        $GLOBALS['cubeabf_scope_config_csv'] = ['include' => '', 'exclude' => ''];
        return;
    }

    $regex = get_shortcode_regex(['cubeabf_config']);
    if (!preg_match("/$regex/s", $post->post_content, $m)) {
        $GLOBALS['cubeabf_scope_config_csv'] = ['include' => '', 'exclude' => ''];
        return;
    }

    // $m[3] contains shortcode attributes
    $atts = shortcode_parse_atts($m[3]);

    $include_csv = trim((string)($atts['scope_include'] ?? $atts['include'] ?? ''));
    $exclude_csv = trim((string)($atts['scope_exclude'] ?? $atts['exclude'] ?? ''));

    $GLOBALS['cubeabf_scope_config_csv'] = [
        'include' => $include_csv,
        'exclude' => $exclude_csv,
    ];
}


function cubeabf_get_scope_from_globals_co(string $post_type): array {
    $csv = $GLOBALS['cubeabf_scope_config_csv'] ?? ['include' => '', 'exclude' => ''];

    $include_csv = trim((string)($csv['include'] ?? ''));
    $exclude_csv = trim((string)($csv['exclude'] ?? ''));

    return [
        'include' => cubeabf_parse_scope_tokens_config($include_csv),
        'exclude' => cubeabf_parse_scope_tokens_config($exclude_csv),
    ];
}

// Sorting

 function cubeabf_parse_sorting_value( $raw ) {

    // normalize
    if ( is_array($raw) ) {
        $raw = reset($raw); // first selected value
    }

    $raw = (string) $raw;

    if ( $raw === '' ) {
        return array(
            'orderby'    => array(),
            'meta_query' => array(),
        );
    }

    $parts = array_filter(array_map('trim', explode('|', $raw)));

    $orderby = array();
    $meta_query = array();

    foreach ( $parts as $part ) {

        if ( strpos($part, 'meta:') === 0 ) {

            $meta = explode(':', $part);

            $meta_key   = $meta[1] ?? '';
            $meta_type  = $meta[2] ?? 'CHAR';
            $meta_order = strtoupper($meta[3] ?? 'ASC');

            $clause = sanitize_key($meta_key . '_clause');

            $meta_query[$clause] = array(
                'key'  => $meta_key,
                'type' => $meta_type,
            );

            $orderby[$clause] = $meta_order;

        } else {

         list($field, $dir) = array_pad(
    explode(':', $part),
    2,
    'ASC'
);

$field = trim($field);
$dir   = strtoupper(trim($dir));

// special case: random sorting
if ($field === 'rand') {

    $orderby = 'rand';

} else {

    $orderby[$field] = $dir;
}
        }
    }

    return array(
        'orderby'    => $orderby,
        'meta_query' => $meta_query,
    );
}

function cubeabf_parse_sorting_options( $raw ) {

    $items = preg_split('/\r\n|\r|\n|(?<!\\\\),/', $raw);

    $out = array();

    foreach ( $items as $item ) {

        $item = trim($item);

        if ( ! $item ) continue;

        $parts = array_map( 'trim', explode('::', $item, 2) );

        if ( count($parts) < 2 ) continue;

        $label = array_shift($parts);

        $out[] = array(
            'label' => $label,
            'value' => $parts[0],
             'extra' => $parts[1] ?? null,
            'raw'   => $item,
        );
    }

    return $out;
}

function cubeabf_build_sorting_img_markup( $map, $label, $atts = [] ) {

    if ( empty( $map ) || empty( $label ) ) {
        return '';
    }

    $mode = $atts['img_map_mode'] ?? 'img';

    $pairs = array_map( 'trim', explode( '|', $map ) );

    foreach ( $pairs as $pair ) {

        $parts = array_map( 'trim', explode( '::', $pair, 2 ) );

        if ( count( $parts ) < 2 ) {
            continue;
        }

        $map_label = $parts[0];
        $img       = $parts[1];

        if ( $map_label !== $label ) {
            continue;
        }

        if ( $mode === 'bg' ) {

            return '<span class="cubeabf-label-media"><span class="cubeabf-label-bg" style="background-image:url('
                . esc_url( $img ) .
            ')"></span></span>';
        }

        return '<span class="cubeabf-label-media"><img class="cubeabf-label-img" src="'
            . esc_url( $img ) .
            '" alt="' . esc_attr( $label ) . '"></span>';
    }

    return '';
}
 




 // For SSR load posts from url with filters
 function cubeabf_get_filters_from_url() {

    $filters = [];

    $allowed_prefixes = [
        'product_cat',
        'product_tag',
        'product_brand',
        'category',
        'post_tag',
        'rating',
        'sorting',
        'keyword',
    ];

    /*
    |--------------------------------------------------------------------------
    | Clean path
    |--------------------------------------------------------------------------
    */

    $path = trim(
        parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ),
        '/'
    );

    // remove /page/x
    $path = preg_replace( '#/page/\d+/?$#', '', $path );

    // remove load more
    $more_param = get_option( 'cubeabf_more_param', 'after' );

    $path = preg_replace(
        '#/' . preg_quote( $more_param, '#' ) . '/\d+/?$#',
        '',
        $path
    );

    $segments = array_filter( explode( '/', $path ) );

    /*
    |--------------------------------------------------------------------------
    | Parse URL segments
    |--------------------------------------------------------------------------
    */

 
    foreach ( $segments as $segment ) {

        /*
        |--------------------------------------------------------------------------
        | Number ranges
        |--------------------------------------------------------------------------
        */
 
        if (
            strpos( $segment, '=' ) !== false &&
            strpos( $segment, '_range' ) !== false
        ) {

            list( $key, $value ) = explode( '=', $segment, 2 );

            list( $min, $max ) = array_pad(
                explode( '-', $value ),
                2,
                null
            );

            $filters[ $key ] = [
                'min'      => is_numeric( $min ) ? (float) $min : null,
                'max'      => is_numeric( $max ) ? (float) $max : null,
                'isDirty'  => true,
            ];

            continue;
        }
         
        /*
        |--------------------------------------------------------------------------
        | Find filter key
        |--------------------------------------------------------------------------
        */

        $matched_key = null;

        foreach ( $allowed_prefixes as $prefix ) {

            if ( strpos( $segment, $prefix . '-' ) === 0 ) {

                $matched_key = $prefix;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | pa_
        |--------------------------------------------------------------------------
        */

      

if ( ! $matched_key && strpos( $segment, 'pa_' ) === 0 ) {

    // 1. Сначала проверяем через $_GET, как и было
    foreach ( $_GET as $k => $v ) {
        if (
            strpos( $k, 'pa_' ) === 0 &&
            strpos( $segment, $k . '-' ) === 0
        ) {
            $matched_key = $k;
            break;
        }
    }

    // 2. Если в $_GET пусто, проверяем зарегистрированные таксономии WordPress
    if ( ! $matched_key && function_exists('taxonomy_exists') ) {
        // Перебираем сегмент по дефисам с конца, чтобы найти существующую таксономию
        $parts = explode('-', $segment);
        while ( count($parts) > 1 ) {
            $potential_taxonomy = implode('-', $parts);
            if ( taxonomy_exists($potential_taxonomy) ) {
                $matched_key = $potential_taxonomy;
                break;
            }
            array_pop($parts); // Удаляем последний элемент и проверяем строку короче
        }
    }

    // 3. Резервный вариант, если таксономия еще не зарегистрирована в системе
    if ( ! $matched_key ) {
        if ( preg_match( '/^(pa_[^-]+)/', $segment, $m ) ) {
            $matched_key = $m[1];
        }
    }
}


/*
|--------------------------------------------------------------------------
| meta_ URL Parser (Фикс для составных городов через дефис)
|--------------------------------------------------------------------------
*/

if ( ! $matched_key && strpos( $segment, 'meta_' ) === 0 ) {
    
    // 1. Ищем ключ по первому дефису после meta_..., чтобы не срезать дефисы в самом городе
    if ( preg_match( '/^(meta_[a-zA-Z0-9_]+)-/', $segment, $m ) ) {
        $matched_key = $m[1];
    }
    
    // 2. Если регулярка не сработала, проверяем ключи из $_GET
    if ( ! $matched_key ) {
        foreach ( $_GET as $k => $v ) {
            if ( strpos( $k, 'meta_' ) === 0 && strpos( $segment, $k . '-' ) === 0 ) {
                $matched_key = $k;
                break;
            }
        }
    }
}

if ( ! $matched_key ) {
    continue;
}

// Извлекаем значение города целиком (например, "niznij-novgorod")
$value = substr($segment, strlen( $matched_key ) + 1);
if ( $value === '' ) {
    continue;
}

// Возвращаем исходное имя переменной $values, которое требует плагин
$values = array_map(
    'urldecode',
    explode( '/', $value )
);

/*
|--------------------------------------------------------------------------
| Database Alias Mapping
|--------------------------------------------------------------------------
*/

if ( ! empty( $values ) ) {

    global $wpdb;

    /*
     * $matched_key is the URL/filter key:
     *
     * meta_dobavit_gorod_0_gorod
     *
     * Remove "meta_" before querying wp_postmeta.
     */
    $db_meta_key = $matched_key;

    if ( strpos( $db_meta_key, 'meta_' ) === 0 ) {
        $db_meta_key = substr( $db_meta_key, 5 );
    }

    /*
     * Repeater:
     *
     * dobavit_gorod_0_gorod
     *
     * becomes:
     *
     * dobavit_gorod_%_gorod
     */
    $like_key = $db_meta_key;

    if ( preg_match( '/_(\d+)(?=_|$)/', $db_meta_key ) ) {

        $like_key = preg_replace(
            '/_(\d+)(?=_|$)/',
            '_%',
            $db_meta_key
        );
    }

  

    $db_values = $wpdb->get_col(
        $wpdb->prepare(
            "
            SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key LIKE %s
              AND meta_value != ''
            ",
            $like_key
        )
    );

    

    /*
     * Build:
     *
     * niznij-novgorod
     * =>
     * Нижний Новгород
     */
    $alias_map = [];

    foreach ( $db_values as $db_value ) {

        $slug = cubeabf_url_slug( $db_value );

        

        if ( $slug !== '' ) {
            $alias_map[ $slug ] = $db_value;
        }
    }

   foreach ( $values as &$value ) {

    foreach ( $db_values as $db_value ) {

        if ( cubeabf_url_slug( $db_value ) === $value ) {
            $value = $db_value;
            break;
        }
    }
}

unset( $value );
 
}



        /*
        |--------------------------------------------------------------------------
        | Подмена значений шорткода (item_3 -> 1078) для SSR
        |--------------------------------------------------------------------------
        */
$lookup_key = $matched_key;

if (isset($GLOBALS['cubeabf_url_key_maps'][$lookup_key])) {
    $lookup_key = $GLOBALS['cubeabf_url_key_maps'][$lookup_key];
}

if (str_starts_with($lookup_key, 'meta_')) {
    $lookup_key = substr($lookup_key, 5);
}

if (isset($GLOBALS['cubeabf_url_maps'][$lookup_key])) {

    foreach ($values as &$v) {
        $v = $GLOBALS['cubeabf_url_maps'][$lookup_key][$v] ?? $v;
    }

    unset($v);
}

        if ( $matched_key === 'sorting' ) {

            $filters['sorting'] = $values[0];

        } else {

            if ( ! isset( $filters[ $matched_key ] ) ) {

                $filters[ $matched_key ] = [];
            }

            $filters[ $matched_key ] = array_merge(
                $filters[ $matched_key ],
                $values
            );
        }
    }
 
    /*
    |--------------------------------------------------------------------------
    | Query string
    |--------------------------------------------------------------------------
    */
 
    foreach ( $_GET as $key => $value ) {

        if (
            strpos( $key, 'pa_' ) === 0 ||
            strpos( $key, 'meta_' ) === 0 ||
            in_array( $key, $allowed_prefixes, true )
        ) {

            $values = (array) $value;

            $values = array_map(
                'urldecode',
                $values
            );

            if ( $key === 'sorting' ) {

                $filters['sorting'] = reset( $values );

            } elseif ( $key === 'keyword' ) {

                $filters['keyword'] = reset( $values );

            } else {

                if ( ! isset( $filters[ $key ] ) ) {

                    $filters[ $key ] = [];
                }

                $filters[ $key ] = array_merge(
                    $filters[ $key ],
                    $values
                );
            }
        }
    }
 
    /*
    |--------------------------------------------------------------------------
    | Legacy number range
    |--------------------------------------------------------------------------
    */

    if (
        isset( $_GET['min_number'] ) ||
        isset( $_GET['max_number'] )
    ) {

        $filters['number'] = [

            'min' => $_GET['min_number'] ?? '',
            'max' => $_GET['max_number'] ?? '',
        ];
    }

    return $filters;
}

// Pagination shortcode

function cubeabf_shortcode_handle($atts) {
    
    ob_start();
   
     // Default values
    $defaults = array(
        'ptype'     => 'post',
        'pppage'    => '6',
        'order'     => 'DESC',
        'orderby'=> 'post_date',
        'qselect'   => 'post-list',
        'wrapclass' => 'post-list-wrap',
        'htmltag'   => 'div',
        'htmlwraptag'   => 'div',
        'lmore'     => 'right',
        'after'      => true,
        'ploader'   => 'Loading...',
        'ploadermore'   => 'Loading...',
        'lmoretext' => 'Load More',
        'ploaderimg' => 'false',
        'ploadermoreimg' => 'false',
        'pagelefticon' => (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg'),
        'pagerighticon' => (CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg'),
        'pagelefttext' => 'false',
        'pagerighttext' => 'false',
        'rightleftempty' => 'true', // If farther is 0 pages then don't show arrow
        'rightlefthide' => 'false',
        'firstlast' => 'false',
        'firsttext' => 'false',
        'lasttext' => 'false',
        'firsticon' => (CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-start.svg'),
        'lasticon' => (CUBEPAGI_PLUGIN_URL . 'assets/images/arrow-double-end.svg'),
        'activecolor' => '#0073aa',
        'hovercolor' => '#005177',
        'enable_pagination' => 'true',
        'enable_load_more' => 'true',
        'enable_pagination_mobile' => 'true',
        'enable_load_more_mobile' => 'true',
        'enable_pagination_pc' => 'true',
        'enable_load_more_pc' => 'true',
        'maxwidth' => '767',
        'ajaxoff' => 'false',
        'updateurl' => 'true',
        'arrowsonly' => 'false',
        'cssoff' => 'false',
        'infinite' => 'true',
         'enable_infinite_mobile' => 'true',
        'enable_infinite_pc' => 'true',
        'mid_size' => '2,1,767', // Desktop, Mobile, Max screen width for mobile
        'skeleton_loader' => 'false',
'scope_exclude' => '',
'scope_include' => '',
'load_btn'  => 'false',
'no_found_infinite' => 'Больше нет результатов.',
'no_found_posts' => 'Ничего не нашлось. Попробуйте другой запрос.',
'update_f_url'  => 'true',
 
);
 
     

    if ( isset( $atts['data'] ) ) {

    // Decode base64 first
    $decoded = base64_decode( $atts['data'], true );

    if ( $decoded ) {
        // Then decode JSON
        $json_data = json_decode( $decoded, true );

        if ( json_last_error() === JSON_ERROR_NONE && is_array( $json_data ) ) {
            $atts = shortcode_atts( $defaults, $json_data, 'cubeabf_code' );
        } else {
            $atts = $defaults; // fallback if JSON malformed
        }
    } else {
        $atts = $defaults; // fallback if Base64 invalid
    }

} else {
    // Legacy style
    $atts = shortcode_atts( $defaults, $atts, 'cubeabf_code' );
}
 
    set_query_var( 'lmore', $atts['lmore'] );
    set_query_var( 'lmoretext', $atts['lmoretext'] );
    set_query_var( 'order', $atts['order'] );
    set_query_var( 'orderby', $atts['orderby'] );
     set_query_var( 'ptype', $atts['ptype'] );
    set_query_var( 'pppage', $atts['pppage'] );
    set_query_var( 'ploaderimg', $atts['ploaderimg'] );
    set_query_var( 'pagelefticon', $atts['pagelefticon'] );
    set_query_var( 'pagerighticon', $atts['pagerighticon'] );
    set_query_var( 'pagelefttext', $atts['pagelefttext'] ); 
    set_query_var( 'pagerighttext', $atts['pagerighttext'] ); 
    set_query_var( 'rightleftempty', $atts['rightleftempty'] ); 
    set_query_var( 'rightlefthide', $atts['rightlefthide'] ); 
    set_query_var( 'firstlast', $atts['firstlast'] );
    set_query_var( 'firsttext', $atts['firsttext'] );
    set_query_var( 'lasttext', $atts['lasttext'] );
    set_query_var( 'firsticon', $atts['firsticon'] );
    set_query_var( 'lasticon', $atts['lasticon'] );
    set_query_var( 'activecolor', $atts['activecolor'] );
    set_query_var( 'hovercolor', $atts['hovercolor'] );
    set_query_var( 'enable_pagination', $atts['enable_pagination'] );
set_query_var( 'enable_load_more', $atts['enable_load_more'] );
set_query_var( 'enable_pagination_mobile', $atts['enable_pagination_mobile'] );
set_query_var( 'enable_load_more_mobile', $atts['enable_load_more_mobile'] );
set_query_var( 'enable_pagination_pc', $atts['enable_pagination_pc'] );
set_query_var( 'enable_load_more_pc', $atts['enable_load_more_pc'] );
 set_query_var( 'maxwidth', $atts['maxwidth'] ); 
  set_query_var( 'ajaxoff', $atts['ajaxoff'] );
   set_query_var( 'arrowsonly', $atts['arrowsonly'] );
     set_query_var( 'cssoff', $atts['cssoff'] );
     set_query_var( 'infinite', $atts['infinite'] ); 
     set_query_var( 'enable_infinite_mobile', $atts['enable_infinite_mobile'] );
set_query_var( 'enable_infinite_pc', $atts['enable_infinite_pc'] );
set_query_var( 'mid_size', $atts['mid_size'] );
set_query_var( 'skeleton_loader', $atts['skeleton_loader'] );
set_query_var( 'load_btn', $atts['load_btn'] );
      
  $htmltag = tag_escape( $atts['htmltag'] );
  $htmlwraptag = tag_escape( $atts['htmlwraptag'] );
 

$paged = max(
    1,
    get_query_var('paged', 1), // standard for posts archive
    get_query_var('page', 1)   // for static pages
);  // Ensure paged is captured

 $filters = cubeabf_get_filters_from_url();

$sorting = isset($_GET['sorting'])
    ? sanitize_text_field(wp_unslash($_GET['sorting']))
    : '';

if (!empty($sorting)) {
    $filters['sorting'] = $sorting;
}

  // Prepare shortcode-specific data
    $shortcode_data = array(
        'ptype'   => $atts['ptype'],
        'pppage'  => $atts['pppage'],
        'order'   => $atts['order'],
        'orderby'  => $atts['orderby'],
        'qselect' => $atts['qselect'],
        'wrapclass' => $atts['wrapclass'],
        'lmore' => $atts['lmore'],
        'after' => $atts['after'],
        'ploader' => $atts['ploader'],
        'ploadermore' => $atts['ploadermore'],
        'lmoretext' => $atts['lmoretext'],
        'ploaderimg' => $atts['ploaderimg'],
        'ploadermoreimg' => $atts['ploadermoreimg'],
        'pagelefticon' => $atts['pagelefticon'],
        'pagerighticon' => $atts['pagerighticon'],
        'pagelefttext' => $atts['pagelefttext'],
        'pagerighttext' => $atts['pagerighttext'],
        'rightleftempty' => $atts['rightleftempty'],
        'rightlefthide' => $atts['rightlefthide'],
        'firstlast' => $atts['firstlast'],
        'firsttext' => $atts['firsttext'],
        'lasttext' => $atts['lasttext'],
         'firsticon' => $atts['firsticon'],
        'lasticon' => $atts['lasticon'],
        'activecolor' => $atts['activecolor'],
        'hovercolor' => $atts['hovercolor'],
        'enable_pagination' => $atts['enable_pagination'],
'enable_load_more' => $atts['enable_load_more'],
'enable_pagination_mobile' => $atts['enable_pagination_mobile'],
'enable_load_more_mobile' => $atts['enable_load_more_mobile'],
'enable_pagination_pc' => $atts['enable_pagination_pc'],
'enable_load_more_pc' => $atts['enable_load_more_pc'],
'maxwidth' => $atts['maxwidth'],
'ajaxoff' => $atts['ajaxoff'],
'updateurl' => $atts['updateurl'], 
'arrowsonly' => $atts['arrowsonly'],
'cssoff' => $atts['cssoff'],
'infinite' => $atts['infinite'], 
'enable_infinite_mobile' => $atts['enable_infinite_mobile'], 
'enable_infinite_pc' => $atts['enable_infinite_pc'], 
'mid_size' => $atts['mid_size'],
'skeleton_loader' => $atts['skeleton_loader'],
'load_btn' => $atts['load_btn'],
'no_found_infinite' => $atts['no_found_infinite'],
'no_found_posts' => $atts['no_found_posts'],
'update_f_url' => $atts['update_f_url'],
 
        
    );
     if ( $atts['cssoff'] == 'false' ) {
    
    
    $cubeabf_css = "
     .load-cubeabf, .product-pagi__page.active {
        background: {$atts['activecolor']};
        border-color: {$atts['activecolor']};
    }
    .load-cubeabf:hover, .product-pagi__page.active:hover {
        background: {$atts['hovercolor']};
        border-color: {$atts['hovercolor']};
    }";

    // Enqueue your main stylesheet first
    wp_enqueue_style(
        'cubeabf-pagination-style',
        CUBEPAGI_PLUGIN_URL . '/assets/pagination.css',
        [],
        time() // cache-busting
    );

    // Then attach the inline CSS to the same handle
    wp_add_inline_style('cubeabf-pagination-style', $cubeabf_css);
}

 
   // Inject shortcode data into JS
    wp_add_inline_script(
        'cubeabf-pagination',
        'Object.assign(cubeabf_ajax_data, ' . wp_json_encode($shortcode_data) . ');',
        'before'
    );
 $qselect = ltrim($atts['qselect'], '.#');  
$wrapclass = ltrim($atts['wrapclass'], '.#'); 

$skeleton_loader = $atts['skeleton_loader'];
  
 $ploaderimg = $atts['ploaderimg']; 
 $ploadermoreimg = $atts['ploadermoreimg']; 
 $infinite = $atts['infinite'];

 switch ($atts['ploader']) {
  case 'dots':
    $ploadertext = '
      <div class="dot-loader">
        <span></span><span></span><span></span>
      </div>';
    break;

  case 'spinner':
    $ploadertext = '<div class="spinner" aria-label="Loading"></div>';
    break;

  default:
    $ploadertext = esc_html($atts['ploader']);
}

 switch ($atts['ploadermore']) {
  case 'dots':
    $ploadermoretext = '
      <div class="dot-loader">
        <span></span><span></span><span></span>
      </div>';
    break;

  case 'spinner':
    $ploadermoretext = '<div class="spinner" aria-label="Loading"></div>';
    break;

  default:
    $ploadermoretext = esc_html($atts['ploadermore']);
}
  
   if ( ( is_home() || is_front_page() ) && ! empty( $atts['ptype'] ) && ! empty( $atts['pppage'] ) ) {
    set_transient( 'home_shortcode_atts', [
        'ptype' => sanitize_text_field( $atts['ptype'] ),
        'pppage' => intval( $atts['pppage'] ),
    ], HOUR_IN_SECONDS );
     
}
$post_type = $atts['ptype'];
 
 $no_posts_message = $atts['no_found_posts'];
    
  
 $paged = max(
    1,
    get_query_var('paged', 1), // standard for posts archive
    get_query_var('page', 1)   // for static pages
); 

$args = [
    'post_type'           => $atts['ptype'],
    'posts_per_page'      => $atts['pppage'],
    'ignore_sticky_posts' => true,
    'paged'               => max(1, (int) $paged),
    'post_status'         => 'publish',
    'order' => $atts['order'],
    'orderby'=> $atts['orderby'],
];
 
 

// 2. Accumulated Pages (Defaults to current page on initial SSR load)
$accumulated_pages = isset( $_GET['accumulated_pages'] ) && is_array( $_GET['accumulated_pages'] ) 
    ? array_map( 'intval', $_GET['accumulated_pages'] ) 
    : [ $paged ];

// 3. WooCommerce Category
$slug        = isset( $_GET['category_slug'] ) ? sanitize_text_field( wp_unslash( $_GET['category_slug'] ) ) : '';
$current_cat = $slug ? get_term_by( 'slug', $slug, 'product_cat' ) : false;

// 4. WooCommerce Tag
$slugTag     = isset( $_GET['woo_tag'] ) ? sanitize_text_field( wp_unslash( $_GET['woo_tag'] ) ) : '';
$current_tag = $slugTag ? get_term_by( 'slug', $slugTag, 'product_tag' ) : false;

// 5. Standard Post Category
$postcatslug = isset( $_GET['post_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['post_cat'] ) ) : '';

// 6. Standard Post Tag
$tagTag   = isset( $_GET['post_tag'] ) ? sanitize_text_field( wp_unslash( $_GET['post_tag'] ) ) : '';
$tag_slug = '';

if ( $tagTag ) {
    $term = get_term_by( 'slug', $tagTag, 'post_tag' );
    if ( $term && ! is_wp_error( $term ) ) {
        $tag_slug = $term->slug;
    }
}

// 7. Brands
$current_brand = isset( $_GET['brand_slug'] ) ? sanitize_text_field( wp_unslash( $_GET['brand_slug'] ) ) : '';

// 8. Append Handling (Usually empty on first SSR load, but kept for compatibility)
$append = isset( $_GET['append'] ) ? sanitize_text_field( wp_unslash( $_GET['append'] ) ) : '';
if ( empty( $append ) ) {
    $accumulated_pages = [ $paged ];
}

$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : get_query_var('s');

/// The load

if ( ! empty($search_query) ) {
    $args['s'] = sanitize_text_field($search_query);
}

$tax_relation =  $GLOBALS['cubeabf_tax_relation'] ?? 'AND';
$tax_relation = ($tax_relation === 'OR') ? 'OR' : 'AND';

$tax_ops = $GLOBALS['cubeabf_ssr_tax_operators'] ?? [];
if (!is_array($tax_ops)) $tax_ops = [];
 
 
 
// -------------------------------------------
// 3) Filters input normalization (AJAX)
// -------------------------------------------
 

$search = ! empty( $filters['keyword'] )
    ? sanitize_text_field( $filters['keyword'] )
    : '';

$args['s'] = $search;

$custom_f = $GLOBALS['cubeabf_search_fields'] ?? [];

$args['custom_fields'] = $custom_f;

if ( ! empty( $_GET['keyword'] ) ) {
    $custom_f = array_filter(
        array_map(
            'trim',
            explode( ',', sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) )
        )
    );
}

 

 $sorting_raw = (string) ($filters['sorting'] ?? '');

 $sorting = cubeabf_parse_sorting_value($sorting_raw);

if (strpos($sorting_raw, 'meta:_price:') === 0) {

    $parts = explode(':', $sorting_raw);

    $dir = strtoupper(trim($parts[3] ?? 'ASC'));

$dir = ($dir === 'DESC') ? 'DESC' : 'ASC';

    $args['meta_key'] = '_price';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = $dir;

} else {

    if (!empty($sorting['orderby'])) {
        $args['orderby'] = $sorting['orderby'];
    }

    if (!empty($sorting['meta_query'])) {

        if (!isset($args['meta_query'])) {
            $args['meta_query'] = [];
        }

        $args['meta_query'] = array_merge(
            $args['meta_query'],
            $sorting['meta_query']
        );
    }
}


if (strpos($sorting_raw, 'meta:_wc_average_rating:') === 0) {

    $parts = explode(':', $sorting_raw);

    $dir = strtoupper(trim($parts[3] ?? 'DESC'));

    $dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

    $args['meta_key'] = '_wc_average_rating';

    $args['orderby'] = 'meta_value_num';

    $args['order'] = $dir;

    // IMPORTANT
    $args['meta_query'][] = [
        'key'     => '_wc_average_rating',
        'value'   => 0,
        'compare' => '>',
        'type'    => 'NUMERIC',
    ];
}

// -------------------------------------------
// 1) Context constraints (category/tag/brand/search)
//    IMPORTANT: we only ADD to args, never overwrite later
// -------------------------------------------


// Инициализируем tax_query, если его еще нет
if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
    $args['tax_query'] = array();
}

// Задаем отношение между фильтрами (товары должны соответствовать ВСЕМ выбранным условиям)
if ( count( $args['tax_query'] ) > 1 ) {
    $args['tax_query']['relation'] = 'AND';
}

// Получаем текущий объект архива (терм таксономии)
$current_term = get_queried_object();

if ( $current_term && isset( $current_term->taxonomy ) ) {

    // 1. Страница категории WooCommerce
    if ( is_product_category() ) {
        $args['tax_query'][] = [
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => [ $current_term->term_id ],
            'include_children' => true,
        ];
    }
    
    // 2. Страница стандартной категории записей WP
    elseif ( is_category() ) {
        $args['tax_query'][] = [
            'taxonomy'         => 'category',
            'field'            => 'term_id',
            'terms'            => [ $current_term->term_id ],
            'include_children' => true,
        ];
    }
    
    // 3. Страница тега WooCommerce
    elseif ( is_product_tag() ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field'    => 'term_id',
            'terms'    => [ $current_term->term_id ],
        ];
    }
    
    // 4. Страница стандартного тега записей WP
    elseif ( is_tag() ) {
        $args['tag'] = $current_term->slug;
    }
    
    // 5. Страница брендов (или любой другой кастомной таксономии)
    // Замените 'product_brand' на точный slug вашей таксономии брендов
    elseif ( is_tax( 'product_brand' ) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_brand',
            'field'    => 'term_id',
            'terms'    => [ $current_term->term_id ],
        ];
    }
}


 if ( ! empty($search_query) ) {
    $args['s'] = sanitize_text_field($search_query);
}

// -------------------------------------------
// 2) Ports (your custom "more" param) => meta_query clause
// -------------------------------------------

$final_meta = [ 'relation' => 'AND' ];

$m_param = get_option( 'cubeabf_more_url_param', 'after' );

if ( $atts['after'] !== 'false' ) {
    $ports = get_query_var($m_param);
    if ( $ports !== '' && $ports !== null ) {
        $final_meta[] = [
            'key'     => 'port_number',
            'value'   => sanitize_text_field($ports),
            'compare' => '=',
        ];
    }
}

// 1. Безопасно получаем данные из GLOBALS (защита от Notice, если первого шорткода нет на странице)
$scope_io = $GLOBALS['cubeabf_scope_config_csv'] ?? null;
 

$include_csv = '';
$exclude_csv = '';

// 2. Если данные пришли в виде JSON-строки (например, из базы или AJAX)
if ( is_string( $scope_io ) ) {
    $scope_io = json_decode( wp_unslash( $scope_io ), true );
}

// 3. Если данные уже являются массивом (стандартный случай для вашего SSR)
if ( is_array( $scope_io ) ) {
    $include_csv = isset( $scope_io['include'] ) ? (string) $scope_io['include'] : '';
    $exclude_csv = isset( $scope_io['exclude'] ) ? (string) $scope_io['exclude'] : '';
}

 
 

 // Union
$filters_mode = sanitize_key($GLOBALS['cubeabf_filters_mode'] ?? 'and');



$meta_db_keys = [];
if (!empty($filters['__meta_db_keys']) && is_array($filters['__meta_db_keys'])) {
  $meta_db_keys = $filters['__meta_db_keys'];
}
// keep filters clean for normal keys, but pass mapping to union
unset($filters['__meta_db_keys']);
  
$base_args = $args; // keep CLEAN associative array (no [] appends)

$has_any_filter = false;
foreach ($filters as $k => $v) {
    if ($v !== '' && $v !== [] && $v !== null) { $has_any_filter = true; break; }
}

 

if ($filters_mode === 'union' && $has_any_filter) {

$final_meta = [];

$repeater_meta_filters = []; // collect repeater subfield filters here
    // rating scope ONLY when rating is active
    $scope = null;
    if (!empty($filters['rating'])) {
        $scope = cubeabf_get_scope_from_request($post_type);
    }

    // PASS these into union fn
    $rating_mode = sanitize_key($GLOBALS['cubeabf_rating_mode'] ?? 'bucket');       // bucket|min
    $rating_mode = ($rating_mode === 'min') ? 'min' : 'bucket';

    $rating_rel  = strtoupper($GLOBALS['cubeabf_rating_meta_relation'] ?? 'OR');    // OR|AND (mostly relevant for bucket multi-select)
    $rating_rel  = ($rating_rel === 'AND') ? 'AND' : 'OR';

    // IMPORTANT: do NOT add your normal filter AND logic below.
    $union_ids = cubeabf_union_filter_ids($base_args, $filters, $scope, $rating_mode, $rating_rel, $meta_db_keys);

    if (!$union_ids) {
        // return empty response...
    }
 

    $final = $base_args;

    // union result = only selector
    $final['post__in'] = $union_ids;

    // safety: union should not be re-ANDed by leftovers
    unset($final['tax_query'], $final['meta_query']);

    $final['posts_per_page'] = (int) $atts['pppage'];
    $final['paged']          = max(1, (int) $paged);
 

   foreach ($filters as $key => $range) {

    if (!str_ends_with($key, '_range')) {
        continue;
    }

    if (!is_array($range)) {
        continue;
    }

    // Привязываем правильный мета-ключ для рейтинга WooCommerce, если ключ rating_range
      if ($key === 'rating_range') {

    $meta_key = '_wc_average_rating';

} elseif ($key === 'price_range') {

    $meta_key = '_price';

} elseif (isset($GLOBALS['cubeabf_range_meta_keys'][$key])) {

    $meta_key = $GLOBALS['cubeabf_range_meta_keys'][$key] ?? $key;

} else {

    $meta_key = sanitize_key($range['meta_key'] ?? $key);

}

 

$d_type = ($meta_key === '_wc_average_rating') ? 'NUMERIC' : 'DECIMAL(10,2)';
    // Проверяем наличие значений в массиве через isset, без жесткого приведения к 0 по умолчанию
    $min_val = isset($range['min']) && $range['min'] !== '' ? (float) $range['min'] : null;
    $max_val = isset($range['max']) && $range['max'] !== '' ? (float) $range['max'] : null;

    // Если оба значения не переданы, пропускаем фильтр
    if ($min_val === null && $max_val === null) {
        continue;
    }

    // Если передан диапазон (и min, и max)
    if ($min_val !== null && $max_val !== null) {
        
        // КРИТИЧНО ДЛЯ РЕЙТИНГА: если min = 0, делаем строгое сравнение, чтобы исключить товары без отзывов
        if ($meta_key === '_wc_average_rating' && $min_val === 0.0) {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $d_type,
            ];
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => $max_val,
                'compare' => '<=',
                'type'    => $d_type,
            ];
        } else {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => [$min_val, $max_val],
                'compare' => 'BETWEEN',
                'type'    => $d_type,
            ];
        }

    } elseif ($min_val !== null) {
        // Передан только min
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $min_val,
            'compare' => '>=',
            'type'    => $d_type,
        ];
    } else {
        // Передан только max
        // Если это рейтинг и указан только max, все равно отсекаем товары без оценок (> 0)
        if ($meta_key === '_wc_average_rating') {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $d_type,
            ];
        }
        
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $max_val,
            'compare' => '<=',
            'type'    => $d_type,
        ];
    }
}


if (!empty($final_meta)) {

    $final['meta_query'] = array_merge(
        ['relation' => 'AND'],
        $final_meta
    );
}

  cubeabf_ensure_scope_config_globals();
// Apply global config scope (include/exclude)
$scopeconfig = cubeabf_get_scope_from_globals_co($post_type);
$final = cubeabf_apply_post_scope_to_args_config($final, $scopeconfig);
$final = cubeabf_apply_scope_tax_to_args_config($final, $scopeconfig);
 
   
 if ( !empty($repeater_meta_filters) ) {
    $final['cubeabf_rep_meta_filters'] = $repeater_meta_filters;
}

$final['cubeabf_query'] = true;
     

  if($skeleton_loader === "false" || empty($filters)) { 
$blog_posts = new WP_Query($final);
  } else {
$blog_posts = new WP_Query( array( 
    'post_type'           => $atts['ptype'], 
    'post__in'            => array(0), // Указываем несуществующий ID внутри массива
    'posts_per_page'      => 1,        // Ограничиваем до 1, чтобы SQL-запрос отработал мгновенно
    'ignore_sticky_posts' => true      // Игнорируем закрепленные посты
) );
  }

   $blog_posts_count = new WP_Query($final);
 

}  else {
 
 
// -------------------------------------------
// 4) Meta filters: keys like meta_yourkey
// -------------------------------------------


  
$repeater_meta_filters = []; // collect repeater subfield filters here

foreach ( $filters as $raw_key => $value ) {
    

     $raw_key = sanitize_key($raw_key);

    if ( strpos($raw_key, 'meta_') !== 0 ) continue;

 $meta_key = substr($raw_key, 5);
$lookup_meta_key = $meta_key;

  // URL alias -> real meta key
if (isset($GLOBALS['cubeabf_url_key_maps']['meta_' . $meta_key])) {

    $meta_key = $GLOBALS['cubeabf_url_key_maps']['meta_' . $meta_key];

    // DB keys never have the filter prefix
    if (str_starts_with($meta_key, 'meta_')) {
        $meta_key = substr($meta_key, 5);
    }
}

    // ✅ override from JS mapping (use original key string if you stored it that way)
    if (isset($meta_db_keys[$raw_key]) && $meta_db_keys[$raw_key] !== '') {
        $meta_key = (string) $meta_db_keys[$raw_key];
    }

     
    $vals = is_array($value) ? $value : [ $value ];
    $vals = array_values(array_filter(array_map('sanitize_text_field', $vals)));
    if ( ! $vals ) continue;

    global $wpdb;

$like_key = $meta_key;

if (preg_match('/_\d+(?=_|$)/', $meta_key)) {
    $like_key = preg_replace('/_(\d+)(?=_|$)/', '_%', $meta_key);
}

$db_values = $wpdb->get_col(
    $wpdb->prepare(
        "
        SELECT DISTINCT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key LIKE %s
          AND meta_value != ''
        ",
        $like_key
    )
);

$alias_map = [];

foreach ($db_values as $db_value) {

    $slug = cubeabf_url_slug($db_value);

    if ($slug !== '') {
        $alias_map[$slug] = $db_value;
    }
}

foreach ($vals as &$val) {

    $alias = trim((string) $val);

    if (isset($alias_map[$alias])) {
        $val = $alias_map[$alias];
    }
}
unset($val);

        $meta_group_map = $GLOBALS['cubeabf_meta_groups'] ?? [];

       $is_group = !empty(
        $meta_group_map[$raw_key]
    );

   

if ($is_group) {

    $repeater_meta_filters[] = [
        'key'     => strtolower($meta_key),
        'vals'    => $vals,
        'any_row' => false,
        'group'   => true,
    ];

    continue;
}

$any_row_map = isset($_POST['any_row']) && is_array($_POST['any_row'])
    ? $_POST['any_row']
    : ($GLOBALS['cubeabf_any_row_map'] ?? []);

$any_row = filter_var(
    $any_row_map[$raw_key] ?? $any_row_map[$meta_key] ?? false,
    FILTER_VALIDATE_BOOLEAN
);

 

   if (preg_match('/_\d+_/', $meta_key)) {

   

    $repeater_meta_filters[] = [
        'key'  => strtolower($meta_key),
        'vals' => $vals,
        'any_row' => $any_row,
        'group'    => !empty($meta_group_map[$raw_key]),
    ];

    continue;
}

       $repeater_roots = $GLOBALS['cubeabf_repeater_roots'] ?? [];
$is_repeater_root = in_array($meta_key, $repeater_roots, true);

 

    if ($is_repeater_root) {

    // Any repeater with at least one row
    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => 0,
        'type'    => 'NUMERIC',
        'compare' => '>',
    ];

} elseif (count($vals) === 1) {

    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => $vals[0],
        'compare' => '=',
    ];

} else {

    $final_meta[] = [
        'key'     => $meta_key,
        'value'   => $vals,
        'compare' => 'IN',
    ];

}
}



if ( count($final_meta) > 1 ) {
    $args['meta_query'] = $final_meta;
}


// --------------------
// Price
// --------------------
 foreach ($filters as $key => $range) {

    if (!str_ends_with($key, '_range')) {
        continue;
    }

    if (!is_array($range)) {
        continue;
    }

    // Привязываем правильный мета-ключ для рейтинга WooCommerce, если ключ rating_range
   if ($key === 'rating_range') {

    $meta_key = '_wc_average_rating';

} elseif ($key === 'price_range') {

    $meta_key = '_price';

} elseif (isset($GLOBALS['cubeabf_range_meta_keys'][$key])) {

    $meta_key = $GLOBALS['cubeabf_range_meta_keys'][$key] ?? $key;

} else {

    $meta_key = sanitize_key($range['meta_key'] ?? $key);

}

    // Проверяем наличие значений в массиве через isset, без жесткого приведения к 0 по умолчанию
    $min_val = isset($range['min']) && $range['min'] !== '' ? (float) $range['min'] : null;
    $max_val = isset($range['max']) && $range['max'] !== '' ? (float) $range['max'] : null;

 
$data_type = ($meta_key === '_wc_average_rating') ? 'NUMERIC' : 'DECIMAL(10,2)';
    // =========================================================================
    // ДОБАВИТЬ ЭТУ ПРОВЕРКУ: 
    // Если min и max пришли как 0 (и это не фильтр рейтинга), полностью игнорируем фильтр
    // =========================================================================
    if ($meta_key !== '_wc_average_rating' && $min_val === 0.0 && $max_val === 0.0) {
        continue;
    }
 

    // Если оба значения не переданы, пропускаем фильтр
    if ($min_val === null && $max_val === null) {
        continue;
    }

    // Если передан диапазон (и min, и max)
    if ($min_val !== null && $max_val !== null) {
        
        // КРИТИЧНО ДЛЯ РЕЙТИНГА: если min = 0, делаем строгое сравнение, чтобы исключить товары без отзывов
        if ($meta_key === '_wc_average_rating' && $min_val === 0.0) {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $data_type,
            ];
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => $max_val,
                'compare' => '<=',
                'type'    => $data_type,
            ];
        } else {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => [$min_val, $max_val],
                'compare' => 'BETWEEN',
                'type'    => $data_type,
            ];
        }

    } elseif ($min_val !== null) {
        // Передан только min
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $min_val,
            'compare' => '>=',
            'type'    => $data_type,
        ];
    } else {
        // Передан только max
        // Если это рейтинг и указан только max, все равно отсекаем товары без оценок (> 0)
        if ($meta_key === '_wc_average_rating') {
            $final_meta[] = [
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => $data_type,
            ];
        }
        
        $final_meta[] = [
            'key'     => $meta_key,
            'value'   => $max_val,
            'compare' => '<=',
            'type'    => $data_type,
        ];
    }
}


// --------------------
// Rating (add into $final_meta, NOT $args['meta_query'])
// --------------------
 

 $scope = null;
 if ( ! empty($filters['rating']) ) {
  $scope = cubeabf_get_scope_from_request($post_type);
  
 }
 
$args_ids = $args;
$args_ids['fields'] = 'ids';
$args_ids['posts_per_page'] = -1;
$args_ids['no_found_rows'] = true;
$args_ids['cache_results'] = false;
$args_ids['update_post_meta_cache'] = false;
$args_ids['update_post_term_cache'] = false;

 
$matching_ids = (new WP_Query($args_ids))->posts ?: [];
$matching_ids = array_values(array_unique(array_map('absint', $matching_ids)));


 
if($scope) {
  $matching_ids = cubeabf_apply_scope_to_id_list($matching_ids, $scope);
 }


// --------------------
// Rating (add into $final_meta, NOT $args['meta_query'])
// --------------------

if (isset($matching_ids) && !empty($matching_ids)) {
if (!empty($filters['rating'])) {

  $vals = [];
  foreach ((array)$filters['rating'] as $v) {
    if (preg_match('/(\d+)/', (string)$v, $m)) {
      $n = (int)$m[1];
      if ($n >= 1 && $n <= 5) $vals[] = $n;
    }
  }
  $vals = array_values(array_unique($vals));

  if ($vals) {
    $mode = sanitize_key($GLOBALS['cubeabf_rating_mode'] ?? 'bucket'); // bucket|min
    $mode = ($mode === 'min') ? 'min' : 'bucket';

    $rel  = strtoupper($GLOBALS['cubeabf_rating_meta_relation'] ?? 'OR'); // OR|AND
    $rel  = ($rel === 'AND') ? 'AND' : 'OR';

    if ($mode === 'min') {
      $threshold = ($rel === 'AND') ? max($vals) : min($vals);

      $final_meta[] = [
        'key'     => '_wc_average_rating',
        'value'   => (float)$threshold,
        'type'    => 'NUMERIC',
        'compare' => '>=',
      ];
    } else {
      $group = ['relation' => $rel];

      foreach ($vals as $r) {
        $min   = (float)$r;
        $upper = ($r === 5) ? 5.01 : (float)($r + 1);

       $group[] = [
        'relation' => 'AND',
        [
            'key'     => '_wc_average_rating',
            'value'   => $min,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ],
        [
            'key'     => '_wc_average_rating',
            'value'   => $upper,
            'compare' => '<',
            'type'    => 'NUMERIC',
        ],
    ];
      }

      $final_meta[] = $group;
    }
  }
}
}
// Apply once at end
if (count($final_meta) > 1) {
  $args['meta_query'] = $final_meta;
} else {
  unset($args['meta_query']);
}

  

// -------------------------------------------
// 7) Taxonomy filters (product_cat/product_tag/product_brand/pa_*)
//    Merge with existing context tax_query (do NOT overwrite)
// -------------------------------------------
$tax_clauses = [];

foreach ( $filters as $taxonomy => $values ) {

    if ( empty($values) ) continue;

    // skip non-tax keys
 if (str_ends_with($taxonomy, '_range') || $taxonomy === 'rating') continue;
    if ( strpos((string)$taxonomy, 'meta_') === 0 ) continue;

    $taxonomy = cubeabf_sanitize_tax_keep_dashes($taxonomy);

    // normalize terms
    $terms = is_array($values) ? $values : [ $values ];
    $terms = array_values(array_filter(array_map('sanitize_title', $terms)));
    if ( ! $terms ) continue;

    $is_allowed =
        ($taxonomy === 'product_cat') ||
        ($taxonomy === 'product_tag') ||
        ($taxonomy === 'product_brand') ||
         ($taxonomy === 'category') ||
        ($taxonomy === 'post_tag') ||
        
        (function_exists('str_starts_with') ? str_starts_with($taxonomy, 'pa_') : (strpos($taxonomy, 'pa_') === 0));

    if ( ! $is_allowed ) continue;

    // Allow pa_ even if taxonomy_exists is flaky during some AJAX init orders
    if ( ! taxonomy_exists($taxonomy) && strpos($taxonomy, 'pa_') !== 0 ) continue;

    $op = strtoupper($tax_ops[$taxonomy] ?? 'IN');
$op = ($op === 'AND') ? 'AND' : 'IN';
  
 $clause = cubeabf_tax_query_clause_smart($taxonomy, $terms, $op);
if (!$clause) continue;

$tax_clauses[] = $clause;

     
}

if ( $tax_clauses ) {
    $existing_tax = isset($args['tax_query']) ? (array) $args['tax_query'] : [];
    if ( isset($existing_tax['relation']) ) unset($existing_tax['relation']);

    // merge base/context + filters
    $merged_tax = array_merge($existing_tax, $tax_clauses);

    $args['tax_query'] = array_merge([ 'relation' => $tax_relation ], $merged_tax);
}


// -------------------------------------------
// 8) Run query
// -------------------------------------------
 
 
  cubeabf_ensure_scope_config_globals();
// Apply global config scope (include/exclude)
$scope_cfg = cubeabf_get_scope_from_globals_co($post_type);
$args = cubeabf_apply_post_scope_to_args_config($args, $scope_cfg);
$args = cubeabf_apply_scope_tax_to_args_config($args, $scope_cfg);

 

if ( !empty($repeater_meta_filters) ) {
    $args['cubeabf_rep_meta_filters'] = $repeater_meta_filters;
}
 
$args['cubeabf_query'] = true;

 

  if($skeleton_loader === "false" || empty($filters)) { 
$blog_posts = new WP_Query($args);
  } else {
$blog_posts = new WP_Query( array( 
    'post_type'           => $atts['ptype'], 
    'post__in'            => array(0), // Указываем несуществующий ID внутри массива
    'posts_per_page'      => 1,        // Ограничиваем до 1, чтобы SQL-запрос отработал мгновенно
    'ignore_sticky_posts' => true      // Игнорируем закрепленные посты
) );
  }
$blog_posts_count = new WP_Query($args);
  
 
}

 $all_post_ids = cubeabf_get_config_scoped_ids_for_post_type($post_type);

  $total_count = ( is_array( $all_post_ids ) ) ? count( $all_post_ids ) : 0;
  
  $max_pages = $total_count / $atts['pppage'];
// Генерируем JS-скрипт, который выполнится сразу при загрузке страницы
echo "<script>
 
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Обновляем количество постов
    var countElements = document.querySelectorAll('.cubeabf-all-total-posts');
    countElements.forEach(function(el) {
      el.textContent = '" . intval($total_count) . "';
    });

     var countFirst = document.querySelectorAll('.cubeabf-update-posts-count');
    countFirst.forEach(function(el) {
      el.textContent = '" . intval($total_count) . "';
    });

    // 2. Обновляем общее количество страниц
    var countMaxPages = document.querySelectorAll('.cubeabf-all-total-pages');
    countMaxPages.forEach(function(el) {
      el.textContent = '" . ceil($max_pages) . "';
    });
  });
  
</script>";

$skeleton_loader = $atts['skeleton_loader'];

  if (!$blog_posts->have_posts() && empty($ports) && $skeleton_loader === "false") {
    // no_posts_message
    if($no_posts_message !== "false") {
   echo '<h2 class="no-posts-message shortcode-message">' . esc_html($no_posts_message) . '</h2>';
 }
}  
 $cubeabf_pagi_loader_css = get_option('cubeabf_pagi_loader_css', 'page-loading');
 $cubeabf_pagi_moreloader_css = get_option('cubeabf_pagi_moreloader_css', 'page-moreloading');
    ?>
    
    <<?php echo $htmlwraptag; ?> class="<?php echo esc_attr($wrapclass); ?>">
        <div class="overlay-loader"></div>
<<?php echo $htmltag; ?> class="<?php echo esc_attr($qselect); ?>">
    <?php if ($blog_posts->have_posts()): while ($blog_posts->have_posts()): $blog_posts->the_post(); ?>
   <?php  $current_type = get_post_type(); get_template_part( 'cubeabf-parts/content', $current_type ); ?>
  <?php endwhile; wp_reset_postdata(); endif;  ?>
</<?php echo $htmltag; ?>>
<div class="cubeabf-meassages"></div>
<div class="<?php echo $cubeabf_pagi_loader_css; ?>">
     <?php
if ( $ploaderimg && $ploaderimg !== 'false' ) {
    echo '<img src="' . esc_url( $ploaderimg ) . '" alt="" />';
} else {
    echo $ploadertext;
}
?>

</div>
</<?php echo $htmlwraptag; ?>>
<div class="<?php echo $cubeabf_pagi_moreloader_css; ?>">
     <?php
if ( $ploadermoreimg && $ploadermoreimg !== 'false') {
    echo '<img src="' . esc_url( $ploadermoreimg ) . '" alt="" />';
} else {
    echo $ploadermoretext;
}
?>

</div>
<?php
// render pagination controls
if ( $filters !== '' || $blog_posts->max_num_pages > 1) {
    $GLOBALS['cubeabf_loop'] = $blog_posts;
    
  
    $template = locate_template( 'pagination-template.php' );
    if ( ! $template ) {
        $template = CUBEPAGI_PLUGIN_DIR . '/template/pagination-template.php';
    }

    include $template;
}
   return ob_get_clean();
}
add_shortcode('cubeabf_code', 'cubeabf_shortcode_handle');



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
    $more_param = esc_attr(get_option('cubeabf_more_url_param', 'after'));
    if ( strpos( $requested_url, $more_param ) !== false ) {
        return false; // skip canonical redirect for /more/ links
    }
    return $redirect_url;
}, 10, 2 );

 

// Hide /more/ from search index

add_action( 'wp_head', function() {
    global $wp;

    $load_more_slug = esc_attr(get_option('cubeabf_more_url_param', 'after'));

    // Check if the URL contains /$load_more_slug/
    if ( strpos( $wp->request, "/{$load_more_slug}/" ) !== false || preg_match("#/{$load_more_slug}/([0-9]+)/?#", $wp->request) ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
});

?>
