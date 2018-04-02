<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class BaseWidget extends \WP_Widget { 

  public static function ajax_form() {
    $i = new static();
    $i->form( $_GET['instance'] );
    wp_die();
  }

  protected function render($path, $data) {
    extract($data);
    ob_start();
      require(FLEXMLS_PLUGIN_DIR_PATH . '/FBS/Widgets/templates/' . $path);
      $html = ob_get_contents();
    ob_end_clean();
    return $html;
  }

}
