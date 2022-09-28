<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class classSocialShare{

  protected static $_instance   = null;
  public $classChange           = null; // Define in constructor
  public $formId                = "";   //  Define in constructor
  public $homePage              = "37";
  public $orderFields           = [];   //  Define in constructor

  public function __construct(){

    if (!class_exists('customChanges')) {
      return  "Custom Changes Class does not exist";
    }
    $this->classChange =  customChanges();

    $formId = $this->classChange->parentFormId;
    add_shortcode('social-share', [$this, 'socialShareHtml']);
    add_action( "gform_enqueue_scripts_{$formId}", array( $this, 'addSocialShareScript' ) , 10, 2 );
  }


  public function socialShareHtml(){
    ob_start();
    $homeUrl =  get_the_permalink($this->homePage); 
    ?>
    <div id="sociallocker">
      <div id="sociallocker-links">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $homeUrl; ?>" class="social-1 fb"><i class="fab fa-facebook-f"></i> Share on facebook</a>
      </div>
      <div id="sociallocker-content">Free Shipping - $0</div>
      <div id="sociallocker-overlay"><i class="fas fa-lock"></i>Want to unlock Free Shipping? Just share our page to Facebook.</div>
    </div>
    <?php 
    return ob_get_clean();
  }

  public function addSocialShareScript(){

      wp_enqueue_style(  'social-share-css' , $this->classChange->plugin_url . "css/social-share.css?".time(), array(), filemtime($this->classChange->plugin_path . 'css/social-share.css'));
      wp_register_script( 'social-share-script', $this->classChange->plugin_url . 'js/social-share.js?'.time(), array('jquery'), false, true  );
       wp_localize_script( 'social-share-script', 'socialShareObj', [
        'formId'        => $this->classChange->parentFormId,
        'orderFields'   => $this->classChange->orderFields,
      ]);
      wp_enqueue_script( 'social-share-script' );
    }


  # We can't use this in common class
  public static function instance () {
    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()

}

function classSocialShare(){
  return classSocialShare::instance();
}


