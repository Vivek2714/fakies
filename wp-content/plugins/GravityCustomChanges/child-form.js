(function($){

  // This function is used inside other files too
  function getInputId(fieldId, formId){
    return '#input_'+formId+'_'+fieldId;
  }

  function getFieldId(fieldId, formId){
    return '#field_'+formId+'_'+fieldId;
  }
  function isValidCardName() {
    if( fakieAjaxObj ){
      var childFormId  = fakieAjaxObj.childFormId;
      var orderFields  = fakieAjaxObj.childFields;
      return jQuery(getInputId( orderFields['cardType'], childFormId ) ).find('input:checked').val() || "";
    }
    return;
  }

  // By defuault not to show second step
  var isThisFirstTime = false;  


  function getItemInCart(){

    if( fakieAjaxObj ){
      var parentFormId  = fakieAjaxObj.parentFormId;
      var orderFields   = fakieAjaxObj.orderFields;
      return jQuery(getInputId( orderFields['totalCard'], parentFormId ) ).val();
    }
    return;

  }

  // Only show second step in case add button is clicked
  jQuery("body").on("click", ".gpnf-add-entry", function(){

    // Child form has value selected
    var totalCard = getItemInCart();
    if( typeof(totalCard) !== 'undefined' && totalCard == 0 && isValidCardName() ){
      isThisFirstTime = true;
    }
  });

  jQuery(document).on('gform_post_render', function(event, form_id, current_page){


    if( fakieAjaxObj ){
      var parentFormId  = fakieAjaxObj.parentFormId;
      var childFormId   = fakieAjaxObj.childFormId;
      var cOrderFields  = fakieAjaxObj.childFields;
      var pOrderFields  = fakieAjaxObj.orderFields;
   
      // Binding Click Event for the first step
      jQuery(getInputId( cOrderFields['cardType'], childFormId ) ).find("input[type='radio']").change(function(){
        jQuery(this).closest('form').find('.gform_next_button:visible').trigger('click');
      });


      var messageID = getFieldId( pOrderFields['discountInfo'], parentFormId );
      var couponID  = "#gf_coupon_code_" + parentFormId;
      var countID   = getInputId( pOrderFields['totalCard'], parentFormId );

      // Discount Message
      function showDiscountMessage(){
        var count  = parseInt(jQuery( countID ).val())  || 0;
        var coupon = jQuery( couponID ).val();
        console.log({count, coupon});
        if( coupon && !count){
          return jQuery( messageID ).slideDown();
        }
        return jQuery( messageID ).slideUp();
      }
    
      // In case card is removed from card
      jQuery( document ).ajaxComplete(showDiscountMessage);

      jQuery([messageID, couponID].join(",")).change(showDiscountMessage);
      jQuery(couponID).keyup(showDiscountMessage);

    }

    var totalCard = getItemInCart();

    if(
         childFormDetails 
      && childFormDetails.parent_id
      && childFormDetails.parent_id == form_id
      && typeof(totalCard) !== 'undefined'
      && totalCard == 0
      // && isValidCardName() 
       ){
        jQuery('.gpnf-add-entry[data-formid="'+childFormDetails.parent_id+'"]').trigger('click');
        // When trigger the click value is not present
        isThisFirstTime= true;
    }

    if( childFormDetails.form_id != form_id  ){
      return;
    }

    // Making google autocomplete work
    if( typeof(initAutocomplete) !== 'undefined' && typeof(google) !== 'undefined' ){
      initAutocomplete(form_id);
    }

    // Showing Mirage Image, something is wrong with Mirage-cloudflare
    jQuery( "#gform_" + form_id  ).find(".gfield_radio label img[data-cfsrc]").each(function(){
      jQuery(this).attr('src', jQuery(this).attr('data-cfsrc') );
      jQuery(this).removeAttr('style'); // Remvoing Inline CSS
    })



    // console.log({ isThisFirstTime, current_page, cardType });

    if(isThisFirstTime && current_page === 1 && isValidCardName()){
      jQuery('.gform_page#gform_page_'+form_id+'_1').hide();
      jQuery('.gform_page#gform_page_'+form_id+'_2').show();
      jQuery('#gform_target_page_number_'+form_id).val("3");
      jQuery('#gform_source_page_number_'+form_id).val("2");
      isThisFirstTime = false;
    }

  
    var container = jQuery( '.custom-date-selector select' ).closest('.custom-date-selector');
    var date  = parseInt(container.find('select:eq(0)').val()) || "";
    var month = parseInt(container.find('select:eq(1)').val()) || "";
    var year  = parseInt(container.find('select:eq(2)').val()) || "";

    if( date == "" || month == "" || year == ""){
      return true;
    }

    dob       = new Date(year, month -1, date);
    var today = new Date();
    var age   = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
    container.find(".gfield_description span").html( age );
  });


})(jQuery);