<?php
/*
Plugin Name: The Pagination and Load More
Description: AJAX pagination and Load More button working synchronously.
Version: 1.1.7
Author: Evgeny Sudakoff
Text Domain: cubeb-pagination-and-load-more
Domain Path: /languages
License: GPLv2
*/

if (!defined('ABSPATH')) exit;
define( 'CUBEPAGI_VERSION', '1.1.7' );
define( 'CUBEPAGI_PLUGIN_FILE', __FILE__ );
define( 'CUBEPAGI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CUBEPAGI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CUBEPAGI_PLUGIN_DIR . 'includes/admin-settings.php';
require_once CUBEPAGI_PLUGIN_DIR . 'includes/frontend-hooks.php';

  

?>
