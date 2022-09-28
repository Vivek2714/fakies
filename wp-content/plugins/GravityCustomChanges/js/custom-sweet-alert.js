
jQuery(document).ready(function(){

  var parentFormId  = sweetalertObj.formId;
  var orderFields   = sweetalertObj.fields;

  function getInputId(fieldId, formId){
   return '#input_'+formId+'_'+fieldId;
  }

  var isClickOnEdit = '.custom-fakies-parent .gpnf-row-actions ul li.edit';
  var isClikOnAdd   = 'button.gpnf-add-entry';

  jQuery(isClikOnAdd).click(function(){
    jQuery(getInputId( orderFields['isClikOnAdd'], parentFormId ) ).val(true);
  });

  // jQuery(isClickOnEdit).click(function(){
  //   jQuery(getInputId( orderFields['isClikOnAdd'], parentFormId ) ).val(false);
  // });
  jQuery('body').on('click', isClickOnEdit, function(){
    jQuery(getInputId( orderFields['isClikOnAdd'], parentFormId ) ).val(false);
  });


  jQuery(document).on('gform_confirmation_loaded', function(event, formId){

  	var showSwal = jQuery(getInputId( orderFields['isClikOnAdd'], parentFormId ) ).val();

    if( showSwal == 'false'){
      return;
    };

  	var totalCard = jQuery(getInputId( orderFields['totalCard'], parentFormId ) ).val();
    
    var htmlMsg  =  ""
    if(totalCard == 1) {
  	  htmlMsg   = "You've added your 1st card. Add some more cards or enter your shipping details to finish up.";
    }

   
    Swal.fire(
      'Card Added!',
      htmlMsg,
      'success',
    );

    /*Swal.fire(
      {
		  icon: 'success',
		  title: 'Good job!',
		  html:  htmlMsg,
		  showConfirmButton: true,
		  timer: 1500
	  });*/


  });


});
