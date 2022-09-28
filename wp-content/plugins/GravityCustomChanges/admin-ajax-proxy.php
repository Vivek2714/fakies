<?php

define( 'WP_USE_THEMES', false ); // Don't load theme support functionality
require( '../../../wp-load.php' );

if(function_exists('gf_coupons')){
  gf_coupons()->apply_coupon_code();
}

