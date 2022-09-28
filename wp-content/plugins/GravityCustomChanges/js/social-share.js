(function(jQuery) {


    var formId        = socialShareObj.formId;
    var orderFields   = socialShareObj.orderFields;

    function getInputId(fieldId){
      return '#input_'+formId+'_'+fieldId;
    }
    function getChoiceId(fieldId ){
      return '#choice_'+formId+'_'+fieldId+'_0';
    }

    var freeShipping = getChoiceId( orderFields['freeShipping'] );
    var fbkShareHid  = getInputId( orderFields['fbkShareHid'] );
    var socialField  = getInputId( orderFields['socialField'] );


    function redirctWindow(url){

        var web_window = window.open(url, 'Share Link', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600,top=' + (screen.height/2 - 300) + ',left=' + (screen.width/2 - 300));
        
        var isCancelClicked = false;
        var check_window_close = setInterval(function() { 
          if (web_window.closed) {
            clearInterval(check_window_close);
            slc.innerHTML = old_slc_html;
            jQuery('.facebook-ajax-loader').show();
            setTimeout(function(){
                if( isCancelClicked ){
                    console.log('Cancel was clicked');
                    return;
                }
                jQuery(fbkShareHid).val(1);
                jQuery(fbkShareHid).keyup();
                jQuery(freeShipping).trigger('click');
                jQuery(freeShipping).attr('checked', 'checked');
                jQuery("#sociallocker-links").css('display', 'none');
                jQuery("#sociallocker-overlay").css('display', 'none');
                jQuery("#sociallocker-content").css('top', '0');
                jQuery('.facebook-ajax-loader').hide();
            }, 12000);
          }
        }, 100);


        jQuery('.cancel-verification-btn').unbind('click').click(function(){
          isCancelClicked = true;
          jQuery('.facebook-ajax-loader').hide();
        });

    }

   
    var sl = document.querySelector("#sociallocker");
    var slc = document.querySelector("#sociallocker-content");
    if (!sl) return;

    var old_slc_html = slc.innerHTML;
    slc.innerHTML = slc.innerHTML.replace(/(href=")(.*)(\")/g, "href=\"#\"");
    sl.querySelectorAll("#sociallocker-links a").forEach(function(ele) {
      ele.onclick = function(e) {
        var url = this.href;
        redirctWindow(url);        
        e.preventDefault();
        return false;
      };
    });
})(jQuery);