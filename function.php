<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}
require(CCCTERMSFILTERAJAX_PLUGIN_PATH . '/assets/results.php');
require(CCCTERMSFILTERAJAX_PLUGIN_PATH . '/addons/ccc-post_thumbnail/ccc-post_thumbnail.php');


class CCC_Terms_Filter_Ajax
{

  /*** Initial execution ***/
  public function __construct()
  {
    add_action('wp_enqueue_scripts', array($this, 'jquery_check'));
    add_action('wp_enqueue_scripts', array($this, 'styles'));
    add_action('wp_enqueue_scripts', array($this, 'scripts'));
    add_action('wp_ajax_ccc_terms_filter_ajax-action', array($this, 'action'));
    add_action('wp_ajax_nopriv_ccc_terms_filter_ajax-action', array($this, 'action'));
  } //endfunction

  public function jquery_check()
  {
    wp_enqueue_script('jquery');
  } //endfunction

  public function styles()
  {
    wp_enqueue_style('ccc_terms_filter_ajax-list', CCCTERMSFILTERAJAX_PLUGIN_URL . '/assets/filter.css', array(), CCCTERMSFILTERAJAX_PLUGIN_VERSION, 'all');
  } //endfunction

  public function scripts()
  {
    $handle = 'ccc_terms_filter_ajax-list';
    $file = 'filter.js';
    wp_register_script($handle, CCCTERMSFILTERAJAX_PLUGIN_URL . '/assets/' . $file, array('jquery'), CCCTERMSFILTERAJAX_PLUGIN_VERSION, true);
    $action = 'ccc_terms_filter_ajax-action';
    wp_localize_script(
      $handle,
      'CCC_TERMS_FILTER_AJAX',
      array(
        'api'    => admin_url('admin-ajax.php'),
        'action' => $action,
        'nonce'  => wp_create_nonce($action)
      )
    );
  } //endfunction

  public function action()
  {
    if (check_ajax_referer($_POST['action'], 'nonce', false)) {
      $data = CCC_Terms_Filter_Ajax_Results::action();
    } else {
      //status_header( '403' );
      $data = 'Forbidden';
    }
    echo $data;
    die();
  } //endfunction

} //endclass
