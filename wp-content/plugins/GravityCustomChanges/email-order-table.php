 <?php 
  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  
  if(empty($id)){
    return "Order Id does not exist!";
  }
  $entry = GFAPI::get_entry( $id );
  if( is_wp_error($entry) ) {
    return $entry;
  }
  $pFields        = $this->customObj->orderFields;
  $cFields        = $this->customObj->childFields;
  $shippingFields = $this->customObj->shipping;
  $nestedIds      = isset($entry[$pFields['nestedField']]) ?   $entry[$pFields['nestedField']] : '';
  $nestedIds      = explode(',', $nestedIds );
  ?>
  <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
  <thead width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #ddd;line-height: 45px;">
    <tr>
      <th colspan="2" scope="col">Card Type</th>
      <th scope="col">Qty</th>
      <th scope="col">Price</th>
    </tr>
  </thead>
  <tbody>

    <?php 

      $productInfo = GFCommon::get_product_fields( $form, $entry, false, true );
      $products    = !empty( $productInfo['products']) ?  $productInfo['products'] : [];

      $subTotal = 0;
      $isFirst  = true;
      foreach ($nestedIds as $nestedId) {
        $nEntry      = GFAPI::get_entry( $nestedId );
        $cardType    = isset($nEntry[$cFields['cardType']])  ? $nEntry[$cFields['cardType']] : '';
        $cardQnty    = isset($nEntry[$cFields['cardQnty']])  ? $nEntry[$cFields['cardQnty']] : '';
        $cardQnty    = substr($cardQnty, 0, strpos($cardQnty, '|'));
        $cardQntyTl  = '2';
        if(empty($cardQnty) || $cardQnty == 'No' ){
          $cardQntyTl = '1';
        }
        $cardTotal = isset($nEntry[$cFields['secondCardPrice']]) ?  $nEntry[$cFields['secondCardPrice']] : '0';
        if($isFirst){
          $cardTotal = isset($nEntry[$cFields['firstCardPrice']]) ?  $nEntry[$cFields['firstCardPrice']] : '0';
          $isFirst = false;
        }
        $subTotal += $cardTotal;
        $cardTotal = GFCommon::to_money( $cardTotal, $this->customObj->currencyName );
        ?> 
        <tr>
          <td colspan="2" style="border: 1px solid #ddd;line-height: 10px; padding:8px;"><?php echo $cardType; ?></td>
          <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo $cardQntyTl; ?></td>
          <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo $cardTotal; ?></td>
        </tr>
        <?php
      }
      $shipPrice    = 0;
      $shippingName = "Shipping";
      foreach($shippingFields as $shipId){ 
        if( isset($products[$shipId] )){
          $shippingName = $products[$shipId]['name'];
          $shipPrice    = $products[$shipId]['price'];
          break;
        }
      }

      $subTotal  = $subTotal + $shipPrice;
      $shipPrice = empty($shipPrice) ? "Free" : GFCommon::to_money( $shipPrice, $this->customObj->currencyName );
    ?>
    <tr>
      <td colspan="3" style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><b><?php echo $shippingName; ?></b></td>
      <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo $shipPrice; ?></td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3" style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><b>Subtotal</b></td>
      <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo GFCommon::to_money( $subTotal, $this->customObj->currencyName );  ?></td>
    </tr>
    <?php 
      $discount   = isset($entry[$pFields['discount']]) ? $entry[$pFields['discount']] : '';
      if(!empty($discount)){ ?>
        <tr>
          <td colspan="3" style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><b>Discount</b></td>
          <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo do_shortcode('[urlcost param="totaldiscount" entryId="'.$id.'" ]') ;  ?></td>
        </tr> 
        <?php 
      }
      $coupon       = isset($entry[$pFields['couponCode']]) ? $entry[$pFields['couponCode']] : '';
      $couponName   = "Coupon Discount";
      $couponeValue = do_shortcode('[urlcost param="discount" entryId="'.$id.'" ]');

      if(!empty(  $products[ $coupon ] ) && !empty( $products[ $coupon ]['name'] )){
        $couponName   = "Coupon ({$products[ $coupon ]['name']})";
        $couponValue  = GFCommon::to_money( $products[ $coupon ]['price'], $this->customObj->currencyName );
      }

      if(!empty($coupon)){ 
        ?>
        <tr>
        <td colspan="3" style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><b><?php echo $couponName; ?></b></td>
        <td style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><?php echo $couponValue;  ?></td>
        </tr>
      <?php }
    ?>
    <tr>
      <td colspan="3" style="border: 1px solid #ddd;line-height: 20px; padding:8px;"><b>Total</b></td>
      <td style="border: 1px solid #ddd;line-height: 20px; padding:8px; font-size:20px;font-style:normal;font-weight:bold; color:#333333"; ><?php echo do_shortcode('[urlcost param="cost" entryId="'.$id.'" ]');  ?></td>
    </tr>
  </tfoot>
</table>