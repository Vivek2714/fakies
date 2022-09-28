<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class gformFrontEndCoupon{

  protected static $_instance = null;

  public $classChange      = "";

  public function __construct(){

    if (!class_exists('customChanges')) {
      return  "Custom Changes Class does not exist";
    }
    $this->classChange  = customChanges();
  

    add_shortcode('front-end-coupon-code', [$this, 'couponCodeViaFrontEnd'] );
    

    # add_action( 'wp_enqueue_scripts'     , [$this, 'enqueue_tooltip_scripts'] );


  }


  /**
   * Enqueue the styles and scripts required for the tooltips.
   */
  function enqueue_tooltip_scripts() {
    wp_enqueue_style( 'gform_tooltip' );
    wp_enqueue_style( 'gform_font_awesome' );
    wp_enqueue_script( 'gform_tooltip_init' );
    wp_enqueue_script( 'gform_gravityforms' );
  }


  public function couponCodeViaFrontEnd(){
    ob_start();

    echo '<pre>';
    print_r( $_POST );
    echo '</pre>';

    die();

    

    $_POST['_gravityformscoupons_save_settings_nonce'] = wp_create_nonce('_gravityformscoupons_save_settings_nonce');

    $_POST['_wp_http_referer'] = '/front-end-coupone-manager/?debuggin=true';
    $_POST['_gaddon_setting_gravityForm'] = '3';
    $_POST['_gaddon_setting_couponName'] = '';
    $_POST['_gaddon_setting_couponCode'] = '';
    $_POST['_gaddon_setting_couponAmountType'] = 'flat';
    $_POST['_gaddon_setting_couponAmount'] = '';
    $_POST['_gaddon_setting_startDate'] = '';
    $_POST['_gaddon_setting_endDate'] = '';
    $_POST['_gaddon_setting_usageLimit'] = '';
    $_POST['_gaddon_setting_isStackable'] = '0';
    $_POST['_gaddon_setting_usageCount'] = '';
    $_POST['gform-settings-save'] = 'Update Settings';
    $_POST['gf_feed_id'] = '';


    if( function_exists('gf_coupons') ){

      echo '<pre>';
      print_r( $_POST );
      echo '</pre>';

      
      # This is used by the plugin layout
      require_once( GFCommon::get_base_path() . '/tooltips.php' );

      $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
      
      # 'src'     => GFAddOn::get_gfaddon_base_url() . "/css/gaddon_settings{$min}.css",

      # Adding required CSS & JS 
      ?>
      <link rel="stylesheet" href="<?php echo GFCommon::get_base_url() ?>/css/admin<?php echo $min; ?>.css?ver=<?php echo GFForms::$version ?>" />
      <link rel="stylesheet" href="<?php echo GFAddOn::get_gfaddon_base_url() . "/css/gaddon_settings{$min}.css"; ?>?ver=<?php echo GFCommon::$version ?>" />      
      <script>
      <?php GFCommon::gf_vars(); ?>
      </script>
      <?php
      gf_coupons()->coupon_edit_page('0', '5');
      $this->enqueue_tooltip_scripts();
    }
    return ob_get_clean();
  }

  # We can't use this in common class
  public static function instance () {
    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()

}

function gformFrontEndCoupon(){
  return gformFrontEndCoupon::instance();
}

//gformFrontEndCoupon();

