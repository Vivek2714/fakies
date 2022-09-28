<?php


class redirectAfterSubmit{

  public  $customObj      = null;  // Define in constructor
  public  $formId         = "";    //  Define in constructor
  public  $thankPage      = 1957;
  public  $currencySybmol = "$";

  public function __construct(){

    if (!class_exists('customChanges')) {
      return "Custom Changes class does not exist!";
    }
    $this->customObj = customChanges();
    $formId = $this->customObj->parentFormId;

    

    add_filter( "gform_confirmation_{$formId}", [ $this, "redirectAfterSubmission"], 10, 4 );

    add_shortcode ( 'urlcost', [ $this, 'urlparamGetCost' ]);

    add_shortcode ( 'confirmation-code', [ $this, 'confirmationHtml' ]);
    
    add_action( "wp_enqueue_scripts", array( $this, 'addConfirmationScript' ));

    //add_shortcode ( 'thank-you-message', [ $this, 'thankYouMessage' ]);
    
    add_filter("gform_pre_render_{$this->formId}", array( $this, "getTotalCalculation") );
  }


  public function emailToOrderConfirmUrl($eId = ''){
    $entryId     = !empty($eId) ? $eId : "";
    $enrtyString = urlencode(base64_encode($entryId));
    $queryParam              = $_GET;
    $queryParam['id']        = $enrtyString;
    $currentURL              = get_the_permalink( $this->customObj->checkOutPage);
    $queryParam              = http_build_query( $queryParam );
    $questionMark            = strpos($currentURL,'?') === false ? "?" : "&";
    $currentURL              =  "{$currentURL}{$questionMark}{$queryParam}";
    return $currentURL;
  }

   public function emailHomeUrl(){
    $currentURL  = get_the_permalink(  $this->customObj->homePage);
    return $currentURL;
  }

  function confirmationHtml(){
    ob_start();
    include $this->customObj->plugin_path . "confirmation-html.php";
    return ob_get_clean();
  }

  public function addConfirmationScript(){
    wp_enqueue_style(  'confirmation-css' , $this->customObj->plugin_url . "css/confirmation.css?".time(), array(), filemtime($this->customObj->plugin_path . 'css/confirmation.css'));
  }

  public function thankYouMessage(){
    ob_start();

    $enrtyString = isset($_GET['id']) ? $_GET['id'] : "";
    $entryId     = base64_decode(urldecode($enrtyString));

    $entry = $this->getEntry($entryId);

    if($entry === false){
     echo '<h3 style="text-align: center;">This order does not exist!</h3>';
     die();
     return ob_get_clean();
    }

    echo '<h3 style="text-align: center;">Your order has been successfully submitted!</h3>';

    return ob_get_clean();

   }
    


  public function getQueryStringEntryId($id = ""){
    
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

    return $data  = [
      'id'            => $entry['id'],
      'cost'          => $entry[$fields['cost']],
      'total'         => $entry[$fields['total']],
      'discount'      => $entry[$fields['discount']],
      'nested'        => $entry[$fields['nestedProduct']],
      'totalDiscount' => $entry[$fields['totalDiscount']],
      'totalCard'     => $entry[$fields['totalCard']],
      'firstName'     => $entry[$fields['firstName']],
      'lastName'      => $entry[$fields['lastName']]
    ];

  }


  public function urlparamGetCost($args){

    if( !isset( $args['param'] )){
      return "";
    }
  
    $id = isset( $args['entryid'] ) ? $args['entryid'] : ""; //get entryId from email template form backend
    $getData = $this->getQueryStringEntryId($id);

    $key     = $args['param'];
    switch( $key ){
      case 'id':
        return $getData[ $key ] . 'x' . $getData['totalCard'];
      break;
      case 'discount':
        $costPrice   =  !empty($getData['cost']) ? $getData['cost'] : 0;
        //$disPrice    =  !empty($getData[$key]) ? $getData[$key] : 0;
        //$tlDisPrice  =  !empty($getData['totalDiscount']) ? $getData['totalDiscount'] : 0;
        //$discountSum =  $disPrice +  $tlDisPrice; 
        //return $this->currencySybmol. ( $costPrice  - $disPrice);
        // return $this->currencySybmol. $costPrice;
        return GFCommon::to_money( $costPrice, $this->customObj->currencyName );

      break;
      case 'totaldiscount':
        $disPrice    =  !empty($getData['discount']) ? $getData['discount'] : 0;
        //$tlDisPrice  =  !empty($getData['totalDiscount']) ? $getData['totalDiscount'] : 0;
        //$discountSum =  $disPrice +  $tlDisPrice;
        // return '-' . $this->currencySybmol.$disPrice;
        return '-' . GFCommon::to_money( $disPrice, $this->customObj->currencyName );
      break;
      case 'cost':
        $csPrice    =  !empty($getData[$key]) ? $getData[$key] : 0;
        // return $this->currencySybmol.$csPrice;
        return GFCommon::to_money( $csPrice, $this->customObj->currencyName );
      break;
    }
   
    return "";
  }

  #
  # Currency Symbol For Partial Payment
  #
  /*public function currencySybmol(){
   global $wpdb;
     $locale     = 'en-US';
     $currency   = get_option( 'rg_gforms_currency' );
     $formatter  = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
     $fmt        = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
     $symbol     = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
     return $currency;
  }*/


  public function redirectAfterSubmission( $confirmation, $form, $entry, $ajax ){

    if(!isset($confirmation['redirect'])){
      return $confirmation;
    }

    if(is_wp_error($entry)){
      return $confirmation;
    }

    $entryId     = !empty($entry['id']) ? $entry['id'] : "";
    $enrtyString = urlencode(base64_encode($entryId));
    $queryParam              = $_GET;
    $queryParam['id']        = $enrtyString;
    $currentURL              = $confirmation['redirect'];
    $queryParam              = http_build_query( $queryParam );
    $questionMark            = strpos($currentURL,'?') === false ? "?" : "&";
    $currentURL              =  "{$currentURL}{$questionMark}{$queryParam}";
    $confirmation            = array( 'redirect' => $currentURL );
    return $confirmation;
  }


  public function getEntry($entryId){
    $entry = GFAPI::get_entry( $entryId );
    if( is_wp_error( $entry ) ) {
      return false;
    }
    return $entry;
  }

  ##########################
  private static $_instance = null;
  public static function instance() {
    if ( is_null( self::$_instance ) )
      self::$_instance = new self();
    return self::$_instance;
  } // End instance()

}
function redirectAfterSubmit() {
  return redirectAfterSubmit::instance();
}


?>