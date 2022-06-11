<?php
/**
 * Plugin Name: Posts Filter
 * Plugin URI: https://wordpress.org/plugins/posts-filter
 * Description: Filter posts by taxonomy terms with Ajax.
 * Version: 1.3.1
 * Requires at least: 4.8
 * Requires PHP: 5.4.0
 * Author: Takashi Matsuyama
 * Author URI: https://profiles.wordpress.org/takashimatsuyama/
 * Text Domain: posts-filter
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

$this_plugin_info = get_file_data( __FILE__, array(
  'name' => 'Plugin Name',
  'version' => 'Version',
  'text_domain' => 'Text Domain',
  'minimum_php' => 'Requires PHP',
));

define( 'CCCTERMSFILTERAJAX_PLUGIN_PATH', rtrim( plugin_dir_path( __FILE__ ), '/') );
define( 'CCCTERMSFILTERAJAX_PLUGIN_URL', rtrim( plugin_dir_url( __FILE__ ), '/') );
define( 'CCCTERMSFILTERAJAX_PLUGIN_SLUG', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
define( 'CCCTERMSFILTERAJAX_PLUGIN_NAME', $this_plugin_info['name'] );
define( 'CCCTERMSFILTERAJAX_PLUGIN_VERSION', $this_plugin_info['version'] );
define( 'CCCTERMSFILTERAJAX_TEXT_DOMAIN', $this_plugin_info['text_domain'] );

load_plugin_textdomain( CCCTERMSFILTERAJAX_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

/*** Require PHP Version Check ***/
if ( version_compare(phpversion(), $this_plugin_info['minimum_php'], '<') ) {
  $plugin_notice = sprintf( __('Oops, this plugin will soon require PHP %s or higher.', CCCTERMSFILTERAJAX_TEXT_DOMAIN), $this_plugin_info['minimum_php'] );
  register_activation_hook(__FILE__, create_function('', "deactivate_plugins('".plugin_basename( __FILE__ )."'); wp_die('{$plugin_notice}');"));
}

if( ! class_exists( 'CCC_Terms_Filter_Ajax' ) ) {
  require( CCCTERMSFILTERAJAX_PLUGIN_PATH.'/function.php' );
  /****** CCC_Terms_Filter_Ajax Initialize ******/
  function ccc_terms_filter_ajax_initialize() {
    global $ccc_terms_filter_ajax;
    /* Instantiate only once. */
    if( ! isset($ccc_terms_filter_ajax) ) {
      $ccc_terms_filter_ajax = new CCC_Terms_Filter_Ajax();
    }
    return $ccc_terms_filter_ajax;
  }
  /*** Instantiate ****/
  ccc_terms_filter_ajax_initialize();

  /*** How to use this Shortcode ***/
  /*
  * [ccc_posts_filter_list post_type="string" posts_per_page="int" class="string" style="string"]
  * [ccc_posts_filter_list term_parent_slug="string" taxonomy_name="string"]
  */
  require( CCCTERMSFILTERAJAX_PLUGIN_PATH .'/assets/shortcode-list.php' );

} else {
  $plugin_notice = __('Oops, PHP Class Name Conflict.', CCCTERMSFILTERAJAX_TEXT_DOMAIN);
  wp_die($plugin_notice);
}
