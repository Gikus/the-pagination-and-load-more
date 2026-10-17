<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// File: admin-settings.php


add_action('admin_menu', function() {
    add_options_page(
        esc_html__('Pagination Settings', 'cubeb-pagination-and-load-more'),
        esc_html__('Pagination Settings', 'cubeb-pagination-and-load-more'),
        'manage_options',
        'pagination-settings',
        'cubeabf_render_settings_page'
    );
});

 

function cubeabf_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Pagination Settings', 'cubeb-pagination-and-load-more'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cubeabf_settings_group');
            do_settings_sections('cubeabf_settings_group');
            ?>
            <table class="form-table">
                 
              
                 <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Number of posts per page for product brands if you have subbrands', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_subbrands_ppp" value="<?php echo esc_attr(get_option('cubeabf_subbrands_ppp', '6')); ?>" style="width: 300px;">
                     <p class="description"><?php echo esc_html__('(IMPORTANT!) to avoid 404 on the url reload', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Url path segment for Load More', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_more_url_param" value="<?php echo esc_attr(get_option('cubeabf_more_url_param', 'after')); ?>" style="width: 300px;">
                        <p class="description"><?php echo esc_html__('By default it is "after"', 'cubeb-pagination-and-load-more'); ?></p>
                    </td>
                </tr>
                 
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Main CSS class for pagination wrapper', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_wrapper_css" value="<?php echo esc_attr(get_option('cubeabf_wrapper_css', 'ajax-pagination')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Additional CSS class in pagination wrapper', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_add_wrapper_css" value="<?php echo esc_attr(get_option('cubeabf_add_wrapper_css', 'product-pagi')); ?>" style="width: 300px;">
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for pagination numbers wrapper', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_wrapper_numbers_css" value="<?php echo esc_attr(get_option('cubeabf_wrapper_numbers_css', 'product-pagi__wrapper')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS main class for pagination button', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_btn_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_btn_css', 'pagination-btn')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for previous pagination button', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_prev_btn_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_prev_btn_css', 'product-pagi__prev')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for next pagination button', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_next_btn_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_next_btn_css', 'product-pagi__next')); ?>" style="width: 300px;">
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for page number buttons', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_page_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_page_css', 'product-pagi__page')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for dots between numbers', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_page_dots_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_page_dots_css', 'page-dots')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for load more btn div', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_load_more_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_load_more_css', 'load-cubeabf')); ?>" style="width: 300px;">
                    </td>
                </tr>
                 <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for loader div', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_loader_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_loader_css', 'page-loading')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('CSS class for More Loader div', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_pagi_moreloader_css" value="<?php echo esc_attr(get_option('cubeabf_pagi_moreloader_css', 'page-moreloading')); ?>" style="width: 300px;">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Текст страницы в заголовке вкладки браузера, — «Страница 3»', 'cubeb-pagination-and-load-more'); ?></th>
                    <td>
                        <input type="text" name="cubeabf_title_page_name" value="<?php echo esc_attr(get_option('cubeabf_title_page_name', 'Страница')); ?>" style="width: 300px;">
                    </td>
                </tr>
                 
    </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function() {
    

  register_setting(
    'cubeabf_settings_group',      
    'cubeabf_more_url_param',       
    [
        'default'           => 'after', // Default value
        'sanitize_callback' => function ($value) {
            $value = trim($value);
            return $value === '' ? 'after' : $value;
        }
    ]
);
    register_setting('cubeabf_settings_group', 'cubeabf_prev_arrow_icon', [
        'default' => CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg',
        'sanitize_callback' => function ( $value ) {
            $value = esc_url_raw( trim( $value ) );
            return $value === '' ? CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-l-active.svg' : $value;
        }
    ]);
    register_setting('cubeabf_settings_group', 'cubeabf_next_arrow_icon', [
        'default' => CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg',
        'sanitize_callback' => function ( $value ) {
              $value = esc_url_raw( trim( $value ) );
            return $value === '' ? CUBEPAGI_PLUGIN_URL . 'assets/images/slick-arrow-r-active.svg' : $value;
        }
    ]);
 register_setting('cubeabf_settings_group', 'cubeabf_subbrands_ppp', ['default' => '6', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? '6' : $value;
            }
        ]);

      
         
    register_setting('cubeabf_settings_group', 'cubeabf_wrapper_css', ['default' => 'ajax-pagination', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'ajax-pagination' : $value;
            }
        ]);
        register_setting('cubeabf_settings_group', 'cubeabf_add_wrapper_css', ['default' => 'product-pagi', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'product-pagi' : $value;
            }
        ]);

         register_setting('cubeabf_settings_group', 'cubeabf_wrapper_numbers_css', ['default' => 'product-pagi__wrapper', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'product-pagi__wrapper' : $value;
            }
        ]);
           register_setting('cubeabf_settings_group', 'cubeabf_pagi_btn_css', ['default' => 'pagination-btn', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'pagination-btn' : $value;
            }
        ]);
       register_setting('cubeabf_settings_group', 'cubeabf_pagi_prev_btn_css', ['default' => 'product-pagi__prev', 
            'sanitize_callback' => function ($value) {
                 $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'product-pagi__prev' : $value;
            }
        ]);
         register_setting('cubeabf_settings_group', 'cubeabf_pagi_next_btn_css', ['default' => 'product-pagi__next', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'product-pagi__next' : $value;
            }
        ]);
         register_setting('cubeabf_settings_group', 'cubeabf_pagi_page_css', ['default' => 'product-pagi__page', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'product-pagi__page' : $value;
            }
        ]);
         register_setting('cubeabf_settings_group', 'cubeabf_pagi_page_dots_css', ['default' => 'page-dots', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'page-dots' : $value;
            }
        ]);
         register_setting('cubeabf_settings_group', 'cubeabf_pagi_load_more_css', ['default' => 'load-cubeabf', 
            'sanitize_callback' => function ($value) {
                  $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'load-cubeabf' : $value;
            }
        ]);
         register_setting('cubeabf_settings_group', 'cubeabf_pagi_loader_css', ['default' => 'page-loading', 
            'sanitize_callback' => function ($value) {
                 $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'page-loading' : $value;
            }
        ]);
        register_setting('cubeabf_settings_group', 'cubeabf_pagi_moreloader_css', ['default' => 'page-moreloading', 
            'sanitize_callback' => function ($value) {
                 $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'page-moreloading' : $value;
            }
        ]);

        register_setting(
    'cubeabf_license_group',      
    'cubeabf_license',       
    [
        'default'           => '', // Default value
        'sanitize_callback' => function ($value) {
            $value = trim($value);
            return $value === '' ? '' : $value;
        }
    ]
);

 register_setting('cubeabf_settings_group', 'cubeabf_title_page_name', ['default' => 'Страница', 
            'sanitize_callback' => function ($value) {
                 $oldval = trim($value);
                  $value = ltrim($oldval, '.#');
                return $value === '' ? 'Страница' : $value;
            }
        ]);

});