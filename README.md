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

In your /themes/ folder create another folder, if you haven’t already, “template-parts”. Create and place into this folder a file: “content-{post_type}.php”. If you want to paginate, for example, woocommerce products, then after “-” type the name of the Post Type and it would look like so: “content-product.php”. If it would be just posts then it would look like so: “content-post.php” and so on. Inside the file you need to place your loop item, for example a product card. Then on the page or/and category/subcategory or a tag template, of the same post type, where you want to have your paginated content, place the shortcode `[cubeab_code]`. Then, in the plugin’s settings you also want to select the post type and configure the rest of the settings according to your needs.

The Pagination and Load More work reflects in the URL: if only the pagination is engaged then it shows only page number, like /page/4/ but if they work together with Load More button then the URL would be like /page/4/more/3/ , it means that the button Load More was clicked on the page 3 and loaded additionally one more page. The /more/ text you can easily change. IMPORTANT – after changing /more/ slug, save permalinks: In the left-hand menu, go to Settings > Permalinks. The plugin has many free options in the settings. If you don’t need the pages’ path segments in URL you can easily disable it in the settings. You can also show only pagination or only LoadMore button or show each one based on device’s screen width.

## Template names you can use

For post type: files that end on `*-page.php`, `*-template.php`, `*-home.php`, `*-post-type.php`,`*-posts.php`.

For categories: `category-{$category->slug}.php`, `category-{$category->term_id}.php`, `category.php`, `archive.php`.

For woocommerce categories: `taxonomy-product_cat-{$product_cat}.php`, `taxonomy-product_cat.php`, `archive-product.php`, `category.php`, `archive.php`.

for post tags: `tag-{$post_tag}.php`, `tag.php`, `archive.php`, `index.php`.

For woocommerce tags: `product-tag-{$tag->slug}.php`, `product-tag-{$tag->term_id}.php`, `taxonomy-product_tag`, `product-tag.php`, `archive-product.php`, `archive.php`.

Demo site: [https://silverpheasant.ru/](https://silverpheasant.ru/)

