=== The Pagination and Load More ===

Tested up to: 7.1
Stable tag: 2.0.2
Contributors: Sevar
Tags: load more, infinite scroll, ajax load more, ajax pagination, pagination
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight solution AJAX pagination, Load More button, and infinite scroll that works seamlessly together or apart for any post type.

== Usage. Where to place shortcodes? ==

In your `/themes/` folder, create another folder named `cubeabf-parts`. Next, create and place `content-{post_type}.php` files inside this folder. For example, if you want the page to be displayed for WooCommerce products, enter the post type name after the "-" symbol, so it looks like this: `content-product.php`. For regular posts, it will look like this: `content-post.php`, and so on. You can place multiple files for different post types there at once. Inside the file, you need to place a loop element, such as a product card. Then, place the config shortcode `[cubeabf_config]` and the pagination shortcode `[cubeabf_code]` on the desired page.

== Detailed Documentation ==

The free version includes pagination, a load more button, and infinite scroll. These can be used simultaneously or based on screen width, featuring numerous adjustable parameters that you specify directly in the config `[cubeabf_config]` and pagination `[cubeabf_code]` shortcodes. You can use this on the main shop page, category pages, brand pages, tag pages, and search result pages. 

Detailed documentation is available on the official website for "Advanced Filters for WP" filter. In this documentation, you will need the config and pagination sections. The remaining sections cover the features of the Advanced Filters for WP filter, which also includes pagination. If you only need the free version (pagination), you should look at the parameters in the config and pagination shortcode sections that do not relate to the filter. For example, in the config, you can use the `scope_exclude` or `scope_include` functions, among others, which directly affect how pagination works without a filter.


== Pro Version - Advanced Filtering with Pagination ==

= Links =
* [Advanced Filters for WP – Pro Version](https://afiltersforwp.ru/)
* [Try the Live Demo](https://showcase.afiltersforwp.ru/shop/)
* [Read the Documentation](https://afiltersforwp.ru/docs/)

The flexible Advanced Filters for WP can filter almost anything on a WordPress site:

* Any post types, including WooCommerce products.
* Taxonomies.
* Custom Fields and ACF.
* Attributes.
* Numeric filters.
* Ajax search.
* Ratings.
* Sorting.
* Chips.
* Pagination, load more, and infinite scroll (can be used simultaneously or based on screen width).

The filter offers a vast number of features, such as automatic visual color display in both the filter and the chips. Numeric filters display real values. You can have a filter on taxonomy pages where it will filter only by the posts of that specific taxonomy. In the client admin dashboard, you can display only the settings you need. It supports any inserts in the form of images, bg or HTML to the filter items. It also features SSR loading via a filtered link. Position everything exactly how you need it, and much, much more. Almost all interactions with filters and pagination happen directly within the shortcodes.
