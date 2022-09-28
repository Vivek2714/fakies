<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class gformAffiliate{

  protected static $_instance = null;

  public $classChange      = "";

  public function __construct(){

    if (!class_exists('customChanges')) {
      return  "Custom Changes Class does not exist";
    }
    $this->classChange  = customChanges();

    add_shortcode('gravity-affiliate', [$this, 'affiliateHtml'] );
    add_action('wp_enqueue_scripts', array($this, 'addScript') );
  }


  public function affiliateHtml(){
    ob_start();
    include $this->classChange->plugin_path . "afiliate-html.php";
    return ob_get_clean();
  }

  public function addScript(){
    wp_enqueue_style(  'affiliate-css' , $this->classChange->plugin_url . "css/afiliate.css?".time(), array(), filemtime( $this->classChange->plugin_url. '"css/afiliate.css'));
    wp_enqueue_style( 'style-name', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" );
  }

  # We can't use this in common class
  public static function instance () {
    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()

}

function gformAffiliate(){
  return gformAffiliate::instance();
}

//gformAffiliate();

