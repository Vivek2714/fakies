<?php
/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class IDSPayoutsListTable extends WP_List_Table {

  public $message = "";
  /** ************************************************************************
   * Normally we would be querying data from a database and manipulating that
   * for use in your list table. For this example, we're going to simplify it
   * slightly and create a pre-built array. Think of this as the data that might
   * be returned by $wpdb->query()
   * 
   * In a real-world scenario, you would make your own custom query inside
   * this class' prepare_items() method.
   * 
   * @var array 
   **************************************************************************/
  function custom_record(){
    $response = array();
    for ($i=1;$i<=10;$i++) {
      $response[] = array(
        'id' => $i,
        'payout_id' => $i,
        'amount' => '$89.'.$i,
        'affiliate' => 'US affiliate ( ID:'.$i.')',
        'reference' => 'test2',
        'generate' => 'siteowner (User ID:'.$i.')',
        'payout_method' => 'payouts-service',
        'payout_ac' => 'STRIPE TEST BANK (*****'.$i.')',
        'date'  => date("d-m-Y",time()),
      );
    }  
    return $response;
  }


  /** ************************************************************************
   * REQUIRED. Set up a constructor that references the parent constructor. We 
   * use the parent reference to set some default configs.
   ***************************************************************************/
  function __construct(){
    global $status, $page;
    //Set parent defaults
    parent::__construct( array(
      'singular'  => 'id',     //singular name of the listed records
      'plural'    => 'ids',    //plural name of the listed records
      'ajax'      => false     //does this table support ajax?
    ) );
  }


  /** ************************************************************************
   * Recommended. This method is called when the parent class can't find a method
   * specifically build for a given column. Generally, it's recommended to include
   * one method for each column you want to render, keeping your package class
   * neat and organized. For example, if the class needs to process a column
   * named 'title', it would first see if a method named $this->column_title() 
   * exists - if it does, that method will be used. If it doesn't, this one will
   * be used. Generally, you should try to use custom column methods as much as 
   * possible. 
   * 
   * Since we have defined a column_title() method later on, this method doesn't
   * need to concern itself with any column with a name of 'title'. Instead, it
   * needs to handle everything else.
   * 
   * For more detailed insight into how columns are handled, take a look at 
   * WP_List_Table::single_row_columns()
   * 
   * @param array $item A singular item (one full row's worth of data)
   * @param array $column_name The name/slug of the column to be processed
   * @return string Text or HTML to be placed inside the column <td>
   **************************************************************************/
  function column_default($item, $column_name){
    // return $item[$column_name];
    // return 
    switch($column_name){
      case 'id':
      case 'payout_id':
      case 'amount':
      case 'affiliate':
      case 'reference':
      case 'generate':
      case 'payout_method';
      case 'payout_ac':
      case 'date':
        return   $item[$column_name];
      default:
        return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
  }


  /** ************************************************************************
   * Recommended. This is a custom column method and is responsible for what
   * is rendered in any column with a name/slug of 'title'. Every time the class
   * needs to render a column, it first looks for a method named 
   * column_{$column_title} - if it exists, that method is run. If it doesn't
   * exist, column_default() is called instead.
   * 
   * This example also illustrates how to implement rollover actions. Actions
   * should be an associative array formatted as 'slug'=>'link html' - and you
   * will need to generate the URLs yourself. You could even ensure the links
   * 
   * 
   * @see WP_List_Table::::single_row_columns()
   * @param array $item A singular item (one full row's worth of data)
   * @return string Text to be placed inside the column <td> (movie title only)
   **************************************************************************/
  public function column_amount($item){   
    //Build row actions
    $actions = [
      //'edit'      => sprintf('<a href="?page=%s&action=%s&post=%s&tab=%s&debuggin=true">Edit</a>',$_REQUEST['page'],'edit',$item['id'],$_REQUEST['page'],$_REQUEST['tab']),
      'delete'    => sprintf('<a href="?page=%s&task=%s&id=%s&tab=%s&debuggin=true">Delete</a>',$_REQUEST['page'],'delete',$item['id'],$_REQUEST['tab']),
    ];
    
    //Return the title contents
    return sprintf('%1$s %3$s',
      /*$1%s*/ $item['amount'],
      /*$2%s*/ $item['id'],
      /*$3%s*/ $this->row_actions($actions)
    );
  } 

  private function sort_data( $a, $b ){
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : '';
    $order   = (!empty($_GET['order']))   ? $_GET['order']   : 'asc';
    $result  = strcmp( $a[$orderby], $b[$orderby] );
    if($order === 'asc'){ return $result; }
    return -$result;
  }   



  /** ************************************************************************
   * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
   * is given special treatment when columns are processed. It ALWAYS needs to
   * have it's own method.
   * 
   * @see WP_List_Table::::single_row_columns()
   * @param array $item A singular item (one full row's worth of data)
   * @return string Text to be placed inside the column <td> (movie title only)
   **************************************************************************/
  public function column_cb($item){
    return sprintf(
      '<input type="checkbox" name="user[]"  value="%2$s" />',
      /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
      /*$2%s*/ $item['id']         //The value of the checkbox should be the record's id
    );
  }


  /** ************************************************************************
   * REQUIRED! This method dictates the table's columns and titles. This should
   * return an array where the key is the column slug (and class) and the value 
   * is the column's title text. If you need a checkbox for bulk actions, refer
   * to the $columns array below.
   * 
   * The 'cb' column is treated differently than the rest. If including a checkbox
   * column in your table you must create a column_cb() method. If you don't need
   * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
   * 
   * @see WP_List_Table::::single_row_columns()
   * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
   **************************************************************************/
  function get_columns(){
    $columns = array(
      'cb'            => '<input type="checkbox"/>', //Render a checkbox instead of text
      'payout_id'     => 'payout ID',
      'amount'        => 'Amount',
      'affiliate'     => 'Affiliate',
      'reference'     => 'Reference',
      'generate'      => 'Generated By',
      'payout_method' => 'Payout Method',
      'payout_ac'     => 'Payout Account',
      'date'          => 'Date'
    );
    return $columns;
  }


  /** ************************************************************************
   * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
   * you will need to register it here. This should return an array where the 
   * key is the column that needs to be sortable, and the value is db column to 
   * sort by. Often, the key and value will be the same, but this is not always
   * the case (as the value is a column name from the database, not the list table).
   * 
   * This method merely defines which columns should be sortable and makes them
   * clickable - it does not handle the actual sorting. You still need to detect
   * the ORDERBY and ORDER querystring variables within prepare_items() and sort
   * your data accordingly (usually by modifying your query).
   * 
   * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
   **************************************************************************/
  function get_sortable_columns() {
    $sortable_columns = array(
      'payout_id'     => array('payout_id' , false),
      'payout_method' => array('payout_method' , false),
      'date'          => array('date' , false),
      'test1'         => array('test1',false),     //true means it's already sorted
    );
    return $sortable_columns;
  }


  /** ************************************************************************
   * Optional. If you need to include bulk actions in your list table, this is
   * the place to define them. Bulk actions are an associative array in the format
   * 'slug'=>'Visible Title'
   * 
   * If this method returns an empty value, no bulk action will be rendered. If
   * you specify any bulk actions, the bulk actions box will be rendered with
   * the table automatically on display().
   * 
   * Also note that list tables are not automatically wrapped in <form> elements,
   * so you will need to create those manually in order for bulk actions to function.
   * 
   * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
   **************************************************************************/
  function get_bulk_actions() {
    $actions = array(
      'deleted'    => 'Delete'
    );
    return $actions;
  }

  /** ************************************************************************
   * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
   * For this example package, we will handle it in the class to keep things
   * clean and organized.
   * 
   * @see $this->prepare_items()
   **************************************************************************/
  public function process_bulk_action() {
    global $wpdb;
    try {
      $action = $this->current_action();
      if($action != 'deleted'){
        return;
      }
      if( empty($_POST['user']) ){
        throw new Exception('Sorry an error occur while deleting your entries');
      } 
      $this->message = $this->requiredMessage('success',implode(',',$_POST['user']). " ID's has been deleted");
    } catch (Exception $e) {
      $this->message = $this->requiredMessage('error',$e->getMessage());
    }
  }
  
  public  function requiredMessage($status , $text){
    return "<div style='display:block;' class='notice-".$status." notice'><p>".$text."</p></div>";
  }  

  public function process_delete_action() {
    global $wpdb;   
    try {
      //Detect when a bulk action is being triggered...
      $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
      if($task != 'delete'){
        return;
      }
      $id = $_REQUEST['id'];
      if( empty($id) ){
        throw new Exception('Sorry an error occur while deleting your entry');
      }       
      $this->message = $this->requiredMessage('success',"ID '{$id}' has been deleted");
    } catch (Exception $e) {
      $this->message = $this->requiredMessage('error',$e->getMessage());
    }    
  }
 

  /** ************************************************************************
   * REQUIRED! This is where you prepare your data for display. This method will
   * usually be used to query the database, sort and filter the data, and generally
   * get it ready to be displayed. At a minimum, we should set $this->items and
   * $this->set_pagination_args(), although the following properties and methods
   * are frequently interacted with here...
   * 
   * @global WPDB $wpdb
   * @uses $this->_column_headers
   * @uses $this->items
   * @uses $this->get_columns()
   * @uses $this->get_sortable_columns()
   * @uses $this->get_pagenum()
   * @uses $this->set_pagination_args()
   **************************************************************************/
  function prepare_items() {
      global $wpdb; //This is used only if making any database queries

      /**
       * First, lets decide how many records per page to show
       */

      $per_page = 10;    
      /**
       * REQUIRED. Now we need to define our column headers. This includes a complete
       * array of columns to be displayed (slugs & titles), a list of columns
       * to keep hidden, and a list of columns that are sortable. Each of these
       * can be defined in another method (as we've done here) before being
       * used to build the value for our _column_headers property.
       */
      $columns  = $this->get_columns();
      $hidden   = array();
      $sortable = $this->get_sortable_columns();
      
      
      /**
       * REQUIRED. Finally, we build an array to be used by the class for column 
       * headers. The $this->_column_headers property takes an array which contains
       * 3 other arrays. One for all columns, one for hidden columns, and one
       * for sortable columns.
       */
      $this->_column_headers = array($columns, $hidden, $sortable);
      
      
      /**
       * Optional. You can handle your bulk actions however you see fit. In this
       * case, we'll handle them within our package just to keep things clean.
       */
      $this->process_bulk_action();
      $this->process_delete_action();
      
      /**
       * Instead of querying a database, we're going to fetch the example data
       * property we created for use in this plugin. This makes this example 
       * package slightly different than one you might build on your own. In 
       * this example, we'll be using array manipulation to sort and paginate 
       * our data. In a real-world implementation, you will probably want to 
       * use sort and pagination data to build a custom query instead, as you'll
       * be able to use your precisely-queried data immediately.
       */
      $data = $this->custom_record();    

      usort( $data, array( &$this, 'sort_data' ) );        
      
      /***********************************************************************
       * ---------------------------------------------------------------------
       * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
       * 
       * In a real-world situation, this is where you would place your query.
       *
       * For information on making queries in WordPress, see this Codex entry:
       * http://codex.wordpress.org/Class_Reference/wpdb
       * 
       * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
       * ---------------------------------------------------------------------
       **********************************************************************/
      
              
      /**
       * REQUIRED for pagination. Let's figure out what page the user is currently 
       * looking at. We'll need this later, so you should always include it in 
       * your own package classes.
       */
      $current_page = $this->get_pagenum();
      
      /**
       * REQUIRED for pagination. Let's check how many items are in our data array. 
       * In real-world use, this would be the total number of items in your database, 
       * without filtering. We'll need this later, so you should always include it 
       * in your own package classes.
       */

      $total_items = count($data);
      
      /**
       * REQUIRED. Now we can add our *sorted* data to the items property, where 
       * it can be used by the rest of the class.
       */
      $this->items = $data;
     
      /**
       * REQUIRED. We also have to register our pagination options & calculations.
       */
      $this->items = array_slice( $data, (($current_page-1) * $per_page), $per_page );

      $this->set_pagination_args([
        'total_items' => $total_items,  //WE have to calculate the total number of items
        'per_page'    => $per_page      //WE have to determine how many items to show on a page
      ]);

      $this->items = $this->items;
  }
}