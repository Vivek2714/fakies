(function(jQuery) {


    var parentFormId  = spinnerAjaxObj.parentFormId;
    var childFrmId    = spinnerAjaxObj.childFormId;
    var orderFields   = spinnerAjaxObj.orderFields;
    var childFields   = spinnerAjaxObj.childFields;

    var nexPrvLoaderClass = '.next-prev-ajax-loader';

    // function getInputId(fieldId){
    //   return '#input_'+formId+'_'+fieldId;
    // }
    // function getChoiceId(fieldId ){
    //   return '#choice_'+formId+'_'+fieldId+'_0';
    // }

    function getInputId(fieldId, formId){
      return '#input_'+formId+'_'+fieldId;
    }
    function getFieldId(fieldId, formId){
      return '#field_'+formId+'_'+fieldId;
    }
    function getChoiceId(fieldId, formId ){
      return '#choice_'+formId+'_'+fieldId+'_0';
    }

    jQuery(document).on('submit','#gform_' + childFrmId ,function(){
      jQuery(nexPrvLoaderClass).css('display', 'block');
      return true; // return false to cancel form action
    });

    jQuery(document).on('gform_page_loaded', function(event, form_id, current_page){
      //jQuery('#gform_25').submit(function() {
      if(form_id != childFrmId){
        return ; 
      }
      jQuery(document).on('submit','#gform_' + childFrmId ,function(){
        //console.log('Show Loader');
        jQuery(nexPrvLoaderClass).css('display', 'block');
        return true; // return false to cancel form action
      });
      //console.log('Hide Loader');
      jQuery(nexPrvLoaderClass).css('display', 'none');
    });

    jQuery(document).on('gform_confirmation_loaded', function(event, formId){
      if(formId != childFrmId){
        return ; 
      }
      jQuery(nexPrvLoaderClass).css('display', 'none');
    });


    // Adding animation in the form

    jQuery( document ).bind( 'gform_post_render', function(event, form_id, current_page) {

      //console.log('We got here');

      if(form_id != childFrmId){
        return ; 
      }

      var parentWrapper = jQuery("#gform_"+form_id);
      var element = parentWrapper.find( 'li.gfield.gfield_error:first' );

      if( !element.length ){
       element = parentWrapper.find( 'li.gfield:visible:first' );
      }


      if( element.length > 0 ) {
        jQuery('html, body').animate({
            'scrollTop' : element.position().top
        }, function(){
          element.find( 'input, select, textarea' ).eq( 0 ).focus();
        });
      }
    } );


  /*jQuery('body').on('click','button.gpnf-add-entry' ,function(){
    updateTotalCardPrice();
  });*/


  function updateTotalCardPrice(){
    
    var isFull = true;

    var total = 0;

   // jQuery("#gform_fields_27 .gpnf-nested-entries tr[data-entryid][data-bind]").length

    jQuery("#gform_fields_" + parentFormId + " .gpnf-nested-entries tr[data-entryid][data-bind]").each(function(){
      // Half Price

      var price = jQuery(this).find("[data-bind='html: f"+childFields['secondCardPrice']+".label']").html();
      // var price = jQuery(this).find("[data-bind='html: f119.label']").html();
      if( isFull ){
        var price = jQuery(this).find("[data-bind='html: f"+childFields['firstCardPrice']+".label']").html();
        //var price = jQuery(this).find("[data-bind='html: f118.label']").html();
        isFull = false;
      }
      total += ( parseFloat(price) || 0 );

     // console.log("total of card" + total);
      
    })


    var discount       = parseFloat( jQuery(getInputId( orderFields['discount'], parentFormId )).val() ) || 0;
    
    
    // var totalPrice = total;
    //  if( total > discount){
    //   var totalPrice = total - discount;
    //    //jQuery(getInputId( orderFields['calPrice'], parentFormId )).val(total).trigger('change');
    //    //return;
    //  }


     var discount   = parseFloat( jQuery(getInputId( orderFields['discount'], parentFormId )).val() ) || 0;
     var totalPrice = ( total > discount) ? total - discount : 0;


    // console.log("total" + total);
    // console.log("discount" + discount);
    // console.log("totalPrice" + totalPrice);
    jQuery(getInputId( orderFields['calPrice'], parentFormId )).val(totalPrice).trigger('change');
    //jQuery(getInputId( orderFields['calPrice'], parentFormId )).val(total).trigger('change');

  }

   jQuery( document ).ajaxComplete(updateTotalCardPrice);
   // gform_post_render is not called, hence using timeout

   jQuery(document).on('gform_post_render', updateTotalCardPrice);
   //setTimeout(updateTotalCardPrice);

})(jQuery);