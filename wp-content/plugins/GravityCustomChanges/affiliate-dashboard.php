<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class classAfiliateDashboard_old{

  protected static $_instance = null;
  public $classChange         = "";

  private $filePath;
  private $fileURL;

  public $tabs = [
    'Affiliates' => 'affiliates',
    'Referrals'  => 'referrals',
    'Payouts'    => 'payouts',
  ];

  public function debug($args){
    echo "<pre>";
      print_r($args);
    echo "</pre>";
  }

  public function __construct(){

    if (!class_exists('customChanges')) {
      return  "Custom Changes Class does not exist";
    }

    if(  !empty($_GET['debug']) && $_GET['debug'] == 'bugg' ){
      ini_set('display_startup_errors', 1);
      ini_set('display_errors', 1);
      error_reporting(-1);
    }

    $this->classChange  =  customChanges();

    $this->filePath = $this->classChange->plugin_path; 
    ## add menu
    add_action( 'admin_menu', array( $this, 'addMenu' ) );
  }

  ## Add Dashboard Menus Callback
  public function addMenu() {
    add_menu_page(
      'Affiliate',                 // page_title
      'Affiliate',                 // menu_title
      'manage_options',            // Capability
      'affiliate',                 // Slug
      array( $this, 'dealers' ),   // Calling bikes Function here
      'dashicons-dashboard',       // Used For Icon
      9
    );
  }

  public function dealers(){
    $cfp = $this->filePath;

    $currentTabAct = isset($_GET['tab']) ? $_GET['tab'] : 'affiliates';
    $this->ad_admin_tabs($this->tabs,$currentTabAct);

    if($currentTabAct == 'affiliates'){
      include_once $cfp .'template/crud/affiliate.php';
      include_once $cfp.'template/affiliateListing.php';
    }    

    if($currentTabAct == 'referrals'){
      include_once $cfp .'template/crud/referrals.php';
      include_once $cfp.'template/referralsListing.php';
    }

    if($currentTabAct == 'payouts'){
      include_once $cfp .'template/crud/payout.php';
      include_once $cfp.'template/payoutListing .php';
    }    

    //$this->debug($testListTable);
    die();
  }
  
  # Affiliates dashboard admin tabs
  # This function is used to display of the admin tabs
  public function ad_admin_tabs( $tabs = [] , $current = 'homepage') {
    if(empty($tabs))
      return false;
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $name => $tab ){
      $class = ( $tab == $current ) ? ' nav-tab-active' : '';
      echo "<a class='nav-tab $class' href='?page=".$_REQUEST['page']."&action=edit&tab=$tab&debuggin=true'>$name</a>";
    }
    echo '</h2>';
  }



  # We can't use this in common class
  public static function instance () {
    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()

}

function classAfiliateDashboard_old(){
  //return classAfiliateDashboard_old::instance();
}

