=== The Pagination and Load More ===

Tested up to: 6.8
Stable tag: 1.2.0
Contributors: Sevar
Tags: load more button, ajax pagination
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Live Preview: https://playground.wordpress.net/?blueprint-url=https://plugins.svn.wordpress.org/cubeb-pagination-and-load-more/assets/blueprint.json

The lightweight solution The Pagination and Load More adds AJAX pagination and Load More functionality that work in sync for any post type.

== Installation ==

== How to install/activate it? ==

WordPress Admin Method:

Go to you administration area in WordPress Plugins > Add
Search for The Pagination and Load More
Click on Install and activate the plugin

FTP Method:

Upload the complete backup folder to the /wp-content/plugins/ directory
Activate the plugin through the ‘Plugins’ menu in WordPress

The AJAX pagination menu will appear in the left sidebar in the Settings

== Usage ==

How to use in theme/templates?
 
In your /themes/ folder create another folder, if you haven't already, "template-parts". Create and place into this folder a file: "content-{post_type}.php". If you want to paginate, for example,  woocommerce products, then after "-" type the name of the Post Type and it would look like so: "content-product.php". If it would be just posts then it would look like so:  "content-post.php" and so on.  Inside the file you need to place your loop item, for example a product card. Then on the page or/and category/subcategory or a tag template, of the same post type, where you want to have your paginated content, place the shortcode `[cubeab_code]`. Then, in the plugin's settings you also want to select the post type and configure the rest of the settings according to your needs. 

The Pagination and Load More work reflects in the URL: if only the pagination is engaged then it shows only page number, like `/page/4/` but if they work together with Load More button then the URL would be like `/page/4/more/3/ `, it means that the button Load More was clicked on the page 3 and loaded additionally one more page. The `/more/` text you can easily change. IMPORTANT - after changing `/more/` slug, save permalinks: In the left-hand menu, go to Settings > Permalinks. The `/more/` path segments are hidden from search index. The plugin has many free options in the settings. If you don't need the pages' path segments in URL you can easily disable it in the settings. You can also show only pagination or only LoadMore button or show each one based on device's screen width.

== Template names you can use ==

For post type: files that end on  `*-page.php`, `*-template.php`, `*-home.php`, `*-post-type.php`, `*-posts.php`.

For categories: `category-{$category->slug}.php`, `category-{$category->term_id}.php`, `category.php`, `archive.php`.

For woocommerce categories:  `taxonomy-product_cat-{$product_cat}.php`, `taxonomy-product_cat.php`, `archive-product.php`, `category.php`, `archive.php`.

for post tags:  `tag-{$post_tag}.php`, `tag.php`, `archive.php`, `index.php`.

For woocommerce tags:  `product-tag-{$tag->slug}.php`, `product-tag-{$tag->term_id}.php`, `taxonomy-product_tag`, `product-tag.php`, `archive-product.php`, `archive.php`.

For product brands in `get_query_var('product_brand')`: `taxonomy-product_brand-{$brand_slug}.php`, `taxonomy-product_brand-{$term->term_id}.php`, `taxonomy-product_brand.php`, `archive-product.php`, `archive.php`, `index.php`

For woocommerce `single-product.php` - if it doesn't find it in the theme root or  theme root/woocommerce/ folder then it looks to the woocommerce's plugin native folder. Then it looks for `single.php`, `singular.php`, `index.php`

For not found 404 page: files that end on `*-page404.php`, `*-404.php` or such full names as: `404.php`, `notfound.php`.


Demo site: [https://silverpheasant.ru/](https://silverpheasant.ru/)


== Changelog: ==

1.0.0 - initial upload
 
1.0.1 - translation update and minor fixes

1.0.2 - minor fixes

1.0.3 - translation function fix

1.1.0 - added support for subcategories and tags

1.1.1 - fixed single post type display

1.1.2 - minor fixes

1.1.3 - template usage bug fix
 
1.1.4  - canonical for /more/ fix

1.1.5 - noindex /more/ and dynamic container fix

1.1.6 - not found 404 fix and other minor fixes

1.1.7 - small fix
 
1.1.8 - small fix published posts only

1.2.0 - many fixes, added support for product brands