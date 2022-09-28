jQuery(document).ready(function($){


  var parentFormId  = fakieAjaxObj.parentFormId;
  var childFormId   = fakieAjaxObj.childFormId;
  var orderFields   = fakieAjaxObj.orderFields;
  var childFields   = fakieAjaxObj.childFields;

  var currencySymbol = fakieAjaxObj.currency;
  var perCardDis     = fakieAjaxObj.perCardDis;

  

  var custFilePrefix  = '_sel_file_delete';
  var uploadFieldIds  = [
   "#"+ childFields['fileUploadField']+ custFilePrefix,
  ];

  function getInputId(fieldId, formId){
    return '#input_'+formId+'_'+fieldId;
  }
  function getFieldId(fieldId, formId){
    return '#field_'+formId+'_'+fieldId;
  }
  function getChoiceId(fieldId, formId ){
    return '#choice_'+formId+'_'+fieldId+'_0';
  }
  
  jQuery('body').on('click','.fakies-upload-btn',function(){
    var gfUpFieldId  = jQuery(this).attr("data-id");
    jQuery("#field_"+childFormId+"_"+gfUpFieldId+" input[type='button']").trigger("click");
  });

  jQuery.each(uploadFieldIds, function(k,v){
    $('body').on('click',v,function(){
      var fieldId     = jQuery(this).attr("data-sl-fld-id");
      jQuery('.fakies-ajax-loader').hide();
      jQuery(".input_"+fieldId+"-fakies-logo").html("");
      jQuery(".fakies-upload-"+fieldId).removeClass("file-exist-class");
      jQuery("#field_"+childFormId+"_"+fieldId).find(".gform_delete").trigger( "click" );
    });
  });

   gform.addFilter('gform_file_upload_markup', function (html, file, up, strings, imagesUrl) {

    var formId   = up.settings.multipart_params.form_id,
    fieldId      = up.settings.multipart_params.field_id;

    jQuery(".fakies-upload-"+fieldId).removeClass("file-exist-class");
    if(typeof(file.name) !== 'undefined'){
      text         = '<strong>' + file.name + "</strong> <img class='custom_gform_delete' "
      + "id='" + fieldId + "_sel_file_delete' "
      + "data-sl-fld-id='" + fieldId + "' "
      + "src='" + imagesUrl + "/delete.png' "
      + "alt='" + strings.delete_file + "' title='" + strings.delete_file + "' />";
      jQuery(".input_"+fieldId+"-fakies-logo").html(text);
      jQuery(".fakies-upload-"+fieldId).addClass("file-exist-class");
      jQuery('.fakies-ajax-loader').hide();
    }
    return html;
  });


  jQuery('body').on('click','button.upload-cancel-btn',function(){
    var gfUpFieldId  = jQuery(this).attr("data-id");
    jQuery('.ginput_preview a').trigger('click');
  });


 jQuery( 'body' ).on( 'change', '.fakies-file-field input[type="file"]', function(){

    var parent = jQuery( this ).parent().parent().attr( 'id' );
    var target = parent.replace( 'gform_multifile_upload_'+childFormId+'_', '' );

    gfMultiFileUploader.uploaders[ parent ].bind('CancelUpload', function( up, file ) {
      jQuery('.fakies-ajax-loader').hide();
      jQuery('.fakies-file-field').removeClass('upload-in-progress');
    });

    // bofore upload start
    gfMultiFileUploader.uploaders[ parent ].bind('BeforeUpload', function( up, file ) {
      jQuery('.fakies-ajax-loader').show();
      jQuery('.fakies-file-field').addClass('upload-in-progress');
    });
   
    // on uploading 
    gfMultiFileUploader.uploaders[ parent ].bind('UploadProgress', function(up, file) {
      var percent = file.percent;
      var addCustomClass = "file-uploader-percentage c100 p" + percent+" green";
      var percentage     = percent + "%";
      jQuery('.file-uploader-percentage').attr( 'class',  addCustomClass);
      jQuery('.file-uploader-percentage span').html(percentage);
      //console.log('File upload percent', file.percent);
    });

    //after file upload
    gfMultiFileUploader.uploaders[ parent ].bind('FileUploaded', function(file) {
      jQuery('.fakies-ajax-loader').hide();
      jQuery('.fakies-file-field').removeClass('upload-in-progress');
    });

  });

  // gform.addFilter( 'gform_product_total', function(total, formId){
  //   // do something with the total
  //   if(formId  != parentFormId){
  //     return total;
  //   }
  //   var totalCard      = jQuery(getInputId( orderFields['totalCard'], parentFormId ) ).val();

  //   if(typeof(totalCard) === "undefined" || totalCard === "" || totalCard == 0){
  //     return total;
  //   }
  //   //var totalDiscount  = jQuery(getInputId( orderFields['totalDiscount'], parentFormId )).val();
  //   var discount       = parseFloat( jQuery(getInputId( orderFields['discount'], parentFormId )).val() ) || 0;
  //   //var actualDiscount = parseInt(totalDiscount) + parseInt(discount);
  //   if( total > discount){
  //     return total - discount;
  //   }
  //   return total;
  // });


  function updateTextOfDiscount(){

    var totalCard      = jQuery(getInputId( orderFields['totalCard'], parentFormId ) ).val();
    // var totalDiscount  = jQuery(getInputId( orderFields['totalDiscount'], parentFormId )).val();
    // var totalMsgDiv    = jQuery(getFieldId( orderFields['totalMsgDis'], parentFormId )).find('.card-added-message-two').find('b');
    // var totalMsgDis    = jQuery(getFieldId( orderFields['totalMsgDis'], parentFormId )).find('.card-added-message-two').find('span');
    // var totalMsgDisOne = jQuery(getFieldId( orderFields['totalMsgDisOne'], parentFormId )).find('.card-added-message-one').find('b');
    


    if(typeof(totalCard) === "undefined" || totalCard === "" || totalCard == 0){
      jQuery("#top-menu-nav .order-count a span.order-count").hide();
      return;
    }

    jQuery("#top-menu-nav .order-count a span.order-count").show();
    jQuery("#top-menu-nav .order-count a span.order-count").html(totalCard);
    

    /*var nextDis        = parseInt(totalDiscount) + parseInt(perCardDis);
    if(totalCard !== "" && totalCard == 1){
    totalMsgDisOne.html(currencySymbol + nextDis);
    return;
    }
    totalMsgDiv.html(currencySymbol + totalDiscount);
    totalMsgDis.html("<b>" + currencySymbol + nextDis + "</b>");*/
  }

  /*********Added for when user add, delete,and edit child form entry *******/
  
  jQuery( document ).ajaxComplete(updateTextOfDiscount);


  // gform_post_render is not called, hence using timeout
  // jQuery(document).on('gform_post_render', updateTextOfDiscount);
  // setTimeout(updateTextOfDiscount);
});


