(function($){

 function updateProxy(){

 	if(typeof(gform_coupon_script_strings) !== 'undefined'
 	&& typeof(gform_coupon_script_strings['ajaxurl']) !== 'undefined'){
 		gform_coupon_script_strings['ajaxurl'] = fakiesProxyObj['ajaxurl'];
 	}

 }
 
 updateProxy();
 
 $(document).ready(updateProxy);

})(jQuery);