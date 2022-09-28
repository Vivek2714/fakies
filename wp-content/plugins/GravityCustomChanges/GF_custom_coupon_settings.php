<?php

// GFForms::include_feed_addon_framework();

// if ( class_exists( 'GF_Field' ) ) {
//  require_once( 'class-gf-field-coupon.php' );
// }

class GFCouponSetting extends GFCoupons {

  private static $__instance = null;
 
    public static function instance () {
      if ( is_null( self::$__instance ) )
        self::$__instance = new self();
      return self::$__instance;
    } // End instance()  


  /**
   * Handle rendering/saving the settings on the feed (coupon) edit page.
   *
   * @param integer $feed_id The current feed ID.
   * @param integer $form_id The form ID the coupon applies to or Zero for all forms.
   */
  public function coupon_edit_page( $feed_id, $form_id ) {

    $messages = '';
    // Save feed if appropriate
    $feed_fields = $this->get_feed_settings_fields();

    $feed_id = absint( $this->maybe_save_feed_settings( $feed_id, $form_id ) );

    $this->_coupon_feed_id = $feed_id;

    //update the form_id on the feed
    $feed = $this->get_feed( $feed_id );

    if ( is_array( $feed ) ) {
      $this->update_feed_form_id( $feed_id, rgar( $feed['meta'], 'gravityForm' ) );
    }

    ?>
    <h3><span><?php echo $this->feed_settings_title() ?></span></h3>
    <input type="hidden" name="gf_feed_id" value="<?php echo esc_attr( $feed_id ) ?>"/>

    <?php
    $this->set_settings( $feed['meta'] );
    GFCommon::display_admin_message( '', $messages );
    $this->render_settings( $feed_fields );
  }

  public function settings( $sections ) {
    $is_first = true;
    foreach ( $sections as $section ) {
      // if ( $this->setting_dependency_met( rgar( $section, 'dependency' ) ) ) {
      //     $this->single_section( $section, $is_first );
      // }
      $this->single_section( $section, $is_first );
      $is_first = false;
    }
  }

  public function maybe_save_feed_settings( $feed_id, $form_id ) {

    if ( ! rgpost( 'gform-settings-save' ) ) {
       return $feed_id;
    }

    // check_admin_referer( $this->_slug . '_save_settings', '_' . $this->_slug . '_save_settings_nonce' );
    // if ( ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
    //    GFCommon::add_error_message( esc_html__( "You don't have sufficient permissions to update the form settings.", 'gravityforms' ) );
    //    return $feed_id;
    // }


    // store a copy of the previous settings for cases where action would only happen if value has changed
     $feed = $this->get_feed( $feed_id );
     $this->set_previous_settings( $feed['meta'] );
     $settings = $this->get_posted_settings();
     $sections = $this->get_feed_settings_fields();
     $settings = $this->trim_conditional_logic_vales( $settings, $form_id );
     $is_valid = $this->validate_settings( $sections, $settings );
     $result   = false;

     if ( $is_valid ) {
      $settings = $this->filter_settings( $sections, $settings );

      if( !empty($form_id) ){
        $settings['gravityForm'] = $form_id;
      }

      $feed_id = $this->save_feed_settings( $feed_id, $form_id, $settings );

      if ( $feed_id ) {
         GFCommon::add_message( $this->get_save_success_message( $sections ) );
      } else {
        GFCommon::add_error_message( $this->get_save_error_message( $sections ) );
      }
     } else {
        GFCommon::add_error_message( $this->get_save_error_message( $sections ) );
     }
     return $feed_id;
  }

}

function GFCouponSetting(){
  return GFCouponSetting::instance();
}

GFCouponSetting();
