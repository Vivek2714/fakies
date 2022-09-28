<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class emailOrderClass{
  public  $customObj      = null;  // Define in constructor
  public  $redirectObj    = null;  // Define in constructor
  public  $formId         = "";    //  Define in constructor
  public  $thankPage      = 1957;
  public  $currencySybmol = "$";
  public  $childForm      = '';


  public function __construct(){

    if (!class_exists('customChanges')) {
      return "Custom Changes class does not exist!";
    }
    $this->customObj = customChanges();
    $this->formId = $this->customObj->parentFormId;

    if (!class_exists('redirectAfterSubmit')) {
      return "Redirect After Submit class does not exist!";
    }
    $this->redirectObj = redirectAfterSubmit();

    add_shortcode('custom-email', [$this, 'customEmailTemplate']);

    add_shortcode ( 'email-order-data', [ $this, 'emailOrderHtml' ]);

    

    $this->childForm    = $this->customObj->childFormId;
    //$parentForm   = $this->customObj->parentFormId;
    add_action( "gform_enqueue_scripts", [$this, 'enqueueScriptForSpinnerUrl'] );
    
    // if(  ( isset($_GET['devemail']) && $_GET['devemail'] == 'preview' ) ){
    //  //add_action( "gform_enqueue_scripts", [$this, 'enqueueScriptForTextOff'] );
    //   //add_action( 'gform_entry_created', [$this, 'updateEidHiddenFld'], 10, 2 );
    // }
    
      
    //add_shortcode ( 'next-prev-loader', [ $this, 'loaderOnClickNextPrev' ]);
    add_action('wp_head', [$this, 'loaderOnClickNextPrev']);


    #TODO Below code
    // add_filter( "gform_pre_render_{$this->childForm}", [$this, 'gfScrollToErrorFocus'], 10, 1 );
    //add_filter( "gform_validation_{$this->childForm}",  [$this, 'gfScrollToErrorFocus'] );

    // add_filter( "gform_confirmation_anchor_{$this->childForm}", function($anchor) {
    //  return $anchor;
    // } );

    // add_action('init', function (){

    //   $user = get_user_by('login', 'fakies');

    //   wp_set_password( "TEgh(MhyNE6*_he", $user->ID );

    //   die();
    // });

    add_filter("gform_pre_render_{$this->formId}", array( $this, "getTotalCalculation") );
    add_filter( 'gform_pre_send_email', [$this, 'beforeSendEmail'], 10, 3);

  }

  public function beforeSendEmail( $email, $message_format, $notification ) {
    if( !empty($email['subject']) && strpos( $email['subject'], "[urlcost") !== false){
      $email['subject'] = do_shortcode( $email['subject'] );
    }
    return $email;
  }

 

  public function enqueueScriptForTextOff() {
    wp_enqueue_script( 'add-text-off-js' , $this->customObj->plugin_url . "js/add-text-off.js?".time(), array('jquery'), false, true );  
    wp_localize_script( 'add-text-off-js', 'offAjaxObj',[
      'parentFormId'  =>  $this->customObj->parentFormId,
      'childFormId'   =>  $this->customObj->childFormId,
      'orderFields'   =>  $this->customObj->orderFields,
      'childFields'   =>  $this->customObj->childFields,
      'currency'      =>  $this->customObj->currencySymbol,
      'perCardDis'    =>  $this->customObj->discountOnCard,
      'orderPage'     => get_the_permalink( $this->customObj->orderNesPgId),
    ]);
  }

  public function getTotalCalculation($form ){


    /*if ( $form['id'] != $this->formId ) {
      return $form;
    }*/


    $entries =  $this->customObj->getActiveCardEntries();

    if(empty($entries)){
      return $form;
    }

    $fields  =  $this->customObj->orderFields;
    $cfields =  $this->customObj->childFields;

    $calField       = $fields['calPrice'];
    $nestedField    = $fields['nestedField'];
    $discountField  = $fields['discount'];

    // $discount = isset($_POST['input_'.$discountField]) ? $_POST['input_'.$discountField] : 0;
    // Getting discount from jQuery
    $discount = 0;
    foreach( $form['fields'] as &$field )  {
      if($field->id == $discountField ){
          $discount = $field->defaultValue;
          break;
      }
    }
    
    // In case post is configured
    // $discount = isset($_POST['input_'.$discountField]) ? $_POST['input_'.$discountField] : $discount;

    $fullPrice = $cfields['firstCardPrice'];
    $halfPrice = $cfields['secondCardPrice'];
    
    $isFirst = true;
    $total   = 0;

    foreach($entries as $entryID){
      $nEntry = GFAPI::get_entry( $entryID );
      if( is_wp_error($nEntry) ) {
        continue;
      }
      $price = $nEntry[ $halfPrice ];
      if( $isFirst ){
        $price = $nEntry[ $fullPrice ];
        $isFirst = false;
      }
      $total += $price;
    }

    // var discount   = parseFloat( jQuery(getInputId( orderFields['discount'], parentFormId )).val() ) || 0;
    // var totalPrice = ( total > discount) ? total - discount : 0;
    // if( $total > $discount){
    //   $total = $total - $discount;
    // }

    $total = ( $total > $discount) ? $total - $discount : 0;

    foreach( $form['fields'] as &$field )  {
      if($field->id == $calField){
        $field->defaultValue = $total;
        break;
      }
    }

    return $form;
  }

  public function gfScrollToErrorFocus( $form ) {
    ?>
    <script type="text/javascript">
      if( window['jQuery'] ) {
        (function( $ ) {
          $( document ).bind( 'gform_post_render', function() {
            var $firstError = $( 'li.gfield:first' );

            console.log($firstError);

            if( $firstError.length > 0 ) {
                $firstError.find( 'input, select, textarea' ).eq( 0 ).focus();
                document.body.scrollTop = $firstError.offset().top;
            }
          } );
        })( jQuery );
      }
    </script>
    <?php
    return $form;
  }

  public function loaderOnClickNextPrev(){
    //ob_start();
    ?>
      <div class="next-prev-ajax-loader" style="display:none;">
        <div class="next-prev-loader-center">
          <div class="next-prev-center-container">
            <img src="<?php echo $this->customObj->plugin_url; ?>/img/loader.svg" alt="Kiwi standing on oval"><br>
            <span>Loading...</span>
          </div>
        </div>
      </div>
    <?php //return ob_get_clean();
  }

  public function enqueueScriptForSpinnerUrl() {
    wp_enqueue_style(  'spinner-url-custom-css' , $this->customObj->plugin_url . "css/spinner-url-custom.css?".time(), array(), filemtime($this->customObj->plugin_path . 'css/spinner-url-custom.css'));
    wp_enqueue_script( 'spinner-url-custom-js' , $this->customObj->plugin_url . "js/spinner-url-custom.js?".time(), array('jquery'), false, true );  
    wp_localize_script( 'spinner-url-custom-js', 'spinnerAjaxObj',[
      'parentFormId'  =>  $this->customObj->parentFormId,
      'childFormId'   =>  $this->customObj->childFormId,
      'orderFields'   =>  $this->customObj->orderFields,
      'childFields'   =>  $this->customObj->childFields,
      'currency'      =>  $this->customObj->currencySymbol,
      'perCardDis'    =>  $this->customObj->discountOnCard,
      'orderPage'     => get_the_permalink( $this->customObj->orderNesPgId),
    ]);
  }

  // This will always return the HTML
  public function getEmailHTML( $entryId ){
    // Return is done inside this
    return include $this->customObj->plugin_path . 'email-template.php';
  }

  public function customEmailTemplate($args){
    if(empty( $args['entry_id'] )){
      return "Invalid entry";
    }
    return $this->getEmailHTML( $args['entry_id'] ); 
  }

  public function getDataFields($id = ""){
    
    $enrtyString = isset($_GET['id']) ? $_GET['id'] : "";

    $entryId     = base64_decode(urldecode($enrtyString));

    if(!empty($id)){
     $entryId = $id; 
    }

    $entry       = GFAPI::get_entry( $entryId );

    $data   = [];
    if( is_wp_error( $entry ) ) {
      return $data;
    }
    $fields = $this->customObj->orderFields;

    $couponPrice = 0;
    if( function_exists('gf_coupons') && !empty($entry[$fields['couponCode']]) ){
      $discounts = gf_coupons()->get_coupons_by_codes( $entry[$fields['couponCode']], $this->formId );
      foreach ($discounts as $key => $value) {
        $couponPrice += !empty($value['amount']) ? $value['amount'] : 0;
      }
    }

    return $data  = [
      'id'            => $entry['id'],
      'cost'          => $entry[$fields['cost']],
      'total'         => $entry[$fields['total']],
      'discount'      => $entry[$fields['discount']],
      'nested'        => $entry[$fields['nestedProduct']],
      'totalDiscount' => $entry[$fields['totalDiscount']],
      'totalCard'     => $entry[$fields['totalCard']],
      'firstName'     => $entry[$fields['firstName']],
      'lastName'      => $entry[$fields['lastName']],
      'coupon'        => $couponPrice,
    ];

  }


  public function emailOrderHtml($args){

    if( !isset( $args['param'] )){
      return "";
    }
    
    $id = isset( $args['entryid'] ) ? $args['entryid'] : ""; //get entryId from email template form backend

    $getData = $this->getDataFields($id);

    $key     = $args['param'];

    switch( $key ){
      case 'name':
        $firstName = isset($getData['firstName']) ? $getData['firstName'] : '';
        $lastName  = isset($getData['lastName']) ? $getData['lastName'] : '';
        return $firstName . ' ' . $lastName ;
      break;
      case 'ordertable':
        ob_start();
        include $this->customObj->plugin_path . 'email-order-table.php';
        return ob_get_clean();
      break;
      case 'account':
        ob_start();
        include $this->customObj->plugin_path . 'email-account-info-table.php';
        return ob_get_clean();
      break;
      case 'coupon':
        $coupon = isset($getData['coupon']) ? $getData['coupon'] : 0;
        return '-' . $this->customObj->currencySymbol . $coupon;
      break;
    }
   
    return "";
  }

  ##########################
  private static $_instance = null;
  public static function instance() {
    if ( is_null( self::$_instance ) )
      self::$_instance = new self();
    return self::$_instance;
  } // End instance()

}


function emailOrderClass() {
  return emailOrderClass::instance();
}


?>