<?php


class gravityFormUploadCare{

    private $formId;

    private $publicKey = 'demopublickey';
    private $privateKey = 'demoprivatekey';

    private static $firstInstance = false;

    private $fieldIds = array();

    function __construct($formID, $fieldsIds){
   	  add_action( "gform_enqueue_scripts_{$formID}", array( $this, 'enqueueUploadCarescript' ), 10, 2 );
   	  add_action( "gform_admin_pre_render{$formID}", array( $this, 'gformAdminPreRender' ), 10, 2 );


      // One time binding only
   	  if(self::$firstInstance === false){
   	  	add_filter('gform_add_field_buttons', array( $this, 'gformAddFieldButtons'));
   	  	add_action('gform_editor_js', array( $this, 'editorScript'), 10, 5);
   	  	self::$firstInstance = true;
   	  }

	  add_action('gform_field_input', array( $this, 'changeInpuField' ), 10, 5);
	  add_action('wp_head', array( $this, 'addPublicScript' ));


    }

    public function addPublicScript(){ 
      ?><script>UPLOADCARE_PUBLIC_KEY = '<?php echo $this->publicKey; ?>';</script><?php
    }

    function changeInpuField($input, $field, $value, $lead_id, $form_id) {
      if ($field['type'] == 'uploadcare') {
      	?><script>UPLOADCARE_PUBLIC_KEY = '<?php echo $this->publicKey; ?>';</script><?php
        $input = sprintf(
        '<p><input type="hidden" name="input_%s" role="uploadcare-uploader" id="%s" class="gform_uploadcare" data-multiple="true" data-images-only="true" value="" /></p>',
        $field['id'],
        'uploadcare-'.$field['id']
        );
      }
      return $input;
    }


	function gformAdminPreRender($form) {
	  foreach ($form['fields'] as $i => $field) {
	    if ($field['type'] == 'uploadcare') {
	      $form['fields'][$i]['displayAllCategories'] = true;
	      $form['fields'][$i]['adminLabel'] = 'Receipts';
	      $form['fields'][$i]['adminOnly'] = false;
	    }
	   }
	  return $form;
	}


	function gformAddFieldButtons($field_groups) {
	  foreach ($field_groups as &$group) {
	    if ($group['name'] == 'advanced_fields') {
	      $group['fields'][] = array(
	        'class' => 'button',
	        'value' => __('Uploadcare', 'gravityforms'),
	        'data-type' => 'uploadcare'
	       );
	      break;
	    }
	  };
	  return $field_groups;
	}


	function editorScript(){
	?>
	<script>
	  fieldSettings['uploadcare'] = '.label_setting, .description_setting, .admin_label_setting, .size_setting, .default_value_textarea_setting, .error_message_setting, .css_class_setting, .visibility_setting'
	</script>
	<?php
	}

    function enqueueUploadCarescript($form, $is_ajax){
      wp_enqueue_script( 'uploadcare', 'https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js' );
    }

}