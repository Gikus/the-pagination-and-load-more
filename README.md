# The Pagination and Load More

This is the development repository for The Pagination and Load More, a lightweight WordPress plugin that adds AJAX pagination and Load More functionality that work synchronously together or separately for any post type. A copy of the plugin is downloadable from the [WordPress.org Plugin Directory.](https://wordpress.org/plugins/cubeb-pagination-and-load-more/)

## License

This plugin is released under the GNU General Public License Version 2 (GPLv2) or later.

## Getting started

Install The Pagination and Load More plugin through the Add Plugins screen (Plugins > Add Plugin). After activating the plugin, the AJAX pagination menu will appear in the left sidebar in the Settings.

## Support

Support for this plugin is primarily provided within the WordPress.org support forums, so if you have questions about using the plugin, please contact us through the Support section on the official plugin page on WordPress.org.

## Usage

How to use in theme/templates?

In your `/themes/` folder, create another folder named `cubeabf-parts`. Next, create and place `content-{post_type}.php` files inside this folder. For example, if you want the page to be displayed for WooCommerce products, enter the post type name after the "-" symbol, so it looks like this: `content-product.php`. For regular posts, it will look like this: `content-post.php`, and so on. You can place multiple files for different post types there at once. Inside the file, you need to place a loop element, such as a product card. Then, place the config shortcode `[cubeabf_config]` and the pagination shortcode `[cubeabf_code]` on the desired page.

## Detailed Documentation

The free version includes pagination, a load more button, and infinite scroll. These can be used simultaneously or based on screen width, featuring numerous adjustable parameters that you specify directly in the config `[cubeabf_config]` and pagination `[cubeabf_code]` shortcodes. You can use this on the main shop page or home page or secondary page, category pages, brand pages, tag pages, and search result pages.

Detailed documentation is available on the official website for "Advanced Filters for WP" filter. In this documentation, you will need the config and pagination sections. The remaining sections cover the features of the Advanced Filters for WP filter, which also includes pagination. If you only need the free version (pagination), you should look at the parameters in the config and pagination shortcode sections that do not relate to the filter. For example, in the config, you can use the `scope_exclude` or `scope_include` functions, among others, which directly affect how pagination works without a filter.

## Pro Version - Advanced Filtering with Pagination

= Links =

- [Advanced Filters for WP – Pro Version](https://afiltersforwp.ru/)
- [Try the Live Demo](https://showcase.afiltersforwp.ru/shop/)
- [Read the Documentation](https://afiltersforwp.ru/docs/)

The flexible Advanced Filters for WP can filter almost anything on a WordPress site:

- Any post types, including WooCommerce products.
- Taxonomies.
- Custom Fields and ACF.
- Attributes.
- Numeric filters.
- Ajax search.
- Ratings.
- Sorting.
- Chips.
- Pagination, load more, and infinite scroll (can be used simultaneously or based on screen width).
