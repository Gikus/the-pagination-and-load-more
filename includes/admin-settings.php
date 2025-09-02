<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// File: admin-settings.php
// Add media uploader support
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_ajax-pagination-settings') {
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('pagimore-arrow-uploader', plugins_url('/assets/arrow-uploader.js', CUBEPAGI_PLUGIN_FILE), ['jquery'], CUBEPAGI_VERSION, true);
    }
});

add_action('admin_menu', function() {
    add_options_page(
        'AJAX Pagination Settings',
        'AJAX Pagination',
        'manage_options',
        'ajax-pagination-settings',
        'pagimore_render_settings_page'
    );
});

// Arrow icon field render function
function pagimore_arrow_icon_field($args) {
    $option = $args['option_name'];
    $icon_url = get_option($option);
    ?>
    <div>
        <img id="<?php echo esc_html($option); ?>-preview" src="<?php echo esc_url($icon_url); ?>" style="max-width:50px; <?php echo esc_url($icon_url) ? '' : 'display:none;'; ?>">
        <input type="text" id="<?php echo esc_html($option); ?>-input" name="<?php echo esc_html($option); ?>" value="<?php echo esc_url($icon_url); ?>" style="width:70%;" placeholder="<?php echo esc_attr__( 'Or some arrow pic url here', 'cubeb-pagination-and-load-more' ); ?>">
        <button type="button" class="button pagimore-arrow-upload" data-target="<?php echo esc_html($option); ?>"><?php echo esc_html__('Choose Image', 'cubeb-pagination-and-load-more'); ?></button>
        <button type="button" class="button pagimore-arrow-remove" data-target="<?php echo esc_html($option); ?>" <?php echo esc_url($icon_url) ? '' : 'style="display:none;"'; ?>><?php echo esc_html__('Remove', 'cubeb-pagination-and-load-more'); ?></button>
    </div>
    <?php
}

function pagimore_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('AJAX Pagination Settings', 'cubeb-pagination-and-load-more'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('pagimore_settings_group');
            do_settings_sections('pagimore_settings_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Query Selector (CSS)', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="pagimore_query_selector" value="<?php echo esc_attr(get_option('pagimore_query_selector', '.post-list')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('CSS selector for the loop container to paginate.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Posts Per Page', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="number" name="pagimore_post_per_page" min="1" value="<?php echo esc_attr(get_option('pagimore_post_per_page', 5)); ?>" style="width: 80px;">
                        <p class="description"><?php echo esc_html__('Number of posts to show per page.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Post Type to Paginate', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <?php
                        $selected = get_option('pagimore_post_type', 'post');
                        $post_types = get_post_types(['public' => true], 'objects');
                        echo '<select name="pagimore_post_type">';
                        foreach ($post_types as $slug => $pt) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($slug),
                                selected($selected, $slug, false),
                                esc_html($pt->labels->singular_name)
                            );
                        }
                        echo '</select>';
                        ?>
                        <p class="description"><?php echo esc_html__('Select which post type should be paginated.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Previous Arrow Icon', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <?php pagimore_arrow_icon_field(['option_name' => 'pagimore_prev_arrow_icon']); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Next Arrow Icon', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <?php pagimore_arrow_icon_field(['option_name' => 'pagimore_next_arrow_icon']); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Url parameter for Load More', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="pagimore_more_url_param" value="<?php echo esc_attr(get_option('pagimore_more_url_param', 'more')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('By default it is "more"', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Preloader text', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="pagimore_preloader_text" value="<?php echo esc_attr(get_option('pagimore_preloader_text', 'Loading...')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('Change the text that appears before results are loaded', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Load More Button text', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="pagimore_load_more_text" value="<?php echo esc_attr(get_option('pagimore_load_more_text', 'Load More')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('Change the text in the Load More button', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                 
                <tr valign="top">
    <th scope="row"><?php echo esc_html__('Active Color', 'cubeb-pagination-and-load-more'); ?></th>
    <td>
        <input type="text" name="pagimore_pagi_active_color" class="pagi-color-picker" 
               value="<?php echo esc_attr(get_option('pagimore_pagi_active_color', '#0073aa')); ?>">
        <p class="description"><?php echo esc_html__('Choose color for active pagination elements', 'cubeb-pagination-and-load-more'); ?></p>
    <tr valign="top">
    <th scope="row"><?php echo esc_html__('Hover Color', 'cubeb-pagination-and-load-more'); ?></th>
    <td>
        <input type="text" name="pagimore_pagi_hover_active_color" class="pagi-color-picker" 
               value="<?php echo esc_attr(get_option('pagimore_pagi_hover_active_color', '#005177')); ?>">
        <p class="description"><?php echo esc_html__('Choose color for hovered active pagination elements', 'cubeb-pagination-and-load-more'); ?></p>
    </td>
</tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Enable Pagination', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <label><input type="checkbox" name="pagimore_enable_pagination" <?php checked(get_option('pagimore_enable_pagination', 1)); ?> value="1"> Enable Pagination (All Devices)</label>
                        <p class="description"><?php echo esc_html__('Show pagination buttons (Previous, Next, and page numbers).', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Enable Load More Button', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <label><input type="checkbox" name="pagimore_enable_load_more" <?php checked(get_option('pagimore_enable_load_more', 1)); ?> value="1"> Enable Load More Button (All Devices)</label>
                        <p class="description"><?php echo esc_html__('Show the "Load More" button.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Mobile Settings (Screen Width < 768px)', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <label><input type="checkbox" name="pagimore_enable_pagination_mobile" <?php checked(get_option('pagimore_enable_pagination_mobile', 1)); ?> value="1"> Enable Pagination on Mobile</label><br>
                        <label><input type="checkbox" name="pagimore_enable_load_more_mobile" <?php checked(get_option('pagimore_enable_load_more_mobile', 1)); ?> value="1"> Enable Load More Button on Mobile</label>
                        <p class="description"><?php echo esc_html__('Control pagination and Load More button visibility on mobile devices.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('PC Settings (Screen Width ≥ 768px)', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <label><input type="checkbox" name="pagimore_enable_pagination_pc" <?php checked(get_option('pagimore_enable_pagination_pc', 1)); ?> value="1"> Enable Pagination on PC</label><br>
                        <label><input type="checkbox" name="pagimore_enable_load_more_pc" <?php checked(get_option('pagimore_enable_load_more_pc', 1)); ?> value="1"> Enable Load More Button on PC</label>
                        <p class="description"><?php echo esc_html__('Control pagination and Load More button visibility on PC devices.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
    <th scope="row"><?php echo esc_html__('Disable Default Styles', 'cubeb-pagination-and-load-more'); ?></th>
    <td>
        <label>
            <input type="checkbox" name="pagimore_disable_styles" <?php checked(get_option('pagimore_disable_styles', 0)); ?> value="1">
            <?php echo esc_html__("Disable plugin's default pagination styles", 'cubeb-pagination-and-load-more'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Check this if you want to use your own CSS styles', 'cubeb-pagination-and-load-more'); ?></p>
    </td>
</tr>
  <tr valign="top">
    <th scope="row"><?php echo esc_html__('Remove pages from URL', 'cubeb-pagination-and-load-more'); ?></th>
    <td>
        <label>
            <input type="checkbox" name="pagimore_remove_pages" <?php checked(get_option('pagimore_remove_pages', 0)); ?> value="1">
            <?php echo esc_html__("Disable plugin's pages update in the URL", 'cubeb-pagination-and-load-more'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Check this if you need pagination without URL change', 'cubeb-pagination-and-load-more'); ?></p>
    </td>
</tr>
<tr valign="top">
                    <th scope="row"><?php echo esc_html__('Not Found slug', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="pagimore_404_page" value="<?php echo esc_attr(get_option('pagimore_404_page', '/notfound-404/')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('Place here custom 404 Not Found template page slug with slashes.', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function() {
     
    register_setting('pagimore_settings_group', 'pagimore_query_selector', ['default' => '.post-list', 
            'sanitize_callback' => function ($value) {
                  $value = trim($value);
                return $value === '' ? '.post-list' : $value;
            }
        ]);
     // Checkbox / boolean toggles (sanitize as 0 or 1)
register_setting('pagimore_settings_group', 'pagimore_enable_pagination', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_enable_load_more', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_enable_pagination_mobile', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_enable_load_more_mobile', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_enable_pagination_pc', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_enable_load_more_pc', [
    'default'           => 1,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_disable_styles', [
    'default'           => 0,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

register_setting('pagimore_settings_group', 'pagimore_remove_pages', [
    'default'           => 0,
    'sanitize_callback' => function ($value) {
        return (int) (bool) $value;
    },
]);

// Post type (sanitize as key-friendly string)
register_setting('pagimore_settings_group', 'pagimore_post_type', [
    'default'           => 'post',
    'sanitize_callback' => function ($value) {
        return sanitize_key($value); // ensures lowercase, a-z0-9_, etc.
    },
]);

// Posts per page (sanitize as positive integer)
register_setting('pagimore_settings_group', 'pagimore_post_per_page', [
    'default'           => 5,
    'sanitize_callback' => function ($value) {
        $value = absint($value);
        return $value > 0 ? $value : 5;
    },
]);

// Colors (sanitize as HEX, fallback to default)
register_setting('pagimore_settings_group', 'pagimore_pagi_active_color', [
    'default'           => '#0073aa',
    'sanitize_callback' => function ($value) {
    return sanitize_text_field($value);
    },
]);

register_setting('pagimore_settings_group', 'pagimore_pagi_hover_active_color', [
    'default'           => '#005177',
    'sanitize_callback' => function ($value) {
return sanitize_text_field($value);
    },
]);

  register_setting(
    'pagimore_settings_group',      
    'pagimore_more_url_param',       
    [
        'default'           => 'more', // Default value
        'sanitize_callback' => function ($value) {
            $value = trim($value);
            return $value === '' ? 'more' : $value;
        }
    ]
);
    register_setting('pagimore_settings_group', 'pagimore_prev_arrow_icon', [
        'default' => CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg',
        'sanitize_callback' => function ( $value ) {
            $value = esc_url_raw( trim( $value ) );
            return $value === '' ? CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg' : $value;
        }
    ]);
    register_setting('pagimore_settings_group', 'pagimore_next_arrow_icon', [
        'default' => CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg',
        'sanitize_callback' => function ( $value ) {
              $value = esc_url_raw( trim( $value ) );
            return $value === '' ? CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg' : $value;
        }
    ]);
     
    register_setting('pagimore_settings_group', 'pagimore_preloader_text', ['default' => 'Loading...', 
            'sanitize_callback' => function ($value) {
                  $value = trim($value);
                return $value === '' ? 'Loading...' : $value;
            }
        ]);

            register_setting('pagimore_settings_group', 'pagimore_load_more_text', ['default' => 'Load More', 
            'sanitize_callback' => function ($value) {
                  $value = trim($value);
                return $value === '' ? 'Load More' : $value;
            }
        ]);
         
        register_setting('pagimore_settings_group', 'pagimore_404_page',  [
        'default'           => '/notfound-404/',
        'sanitize_callback' => function ($value) {
            $value = trim(sanitize_text_field($value));
            return $value === '' ? '/notfound-404/' : $value;
        },
    ]);
       
});