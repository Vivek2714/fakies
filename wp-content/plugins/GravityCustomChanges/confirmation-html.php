<?php
  
  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  $entry  = $this->getQueryStringEntryId();
  $imgUrl = $this->customObj->plugin_url;

  #
  # In case entry is invalid return with error message
  if(empty($entry)){ 
    ?>
        <div class="confirmation_order"> 
          <div class="order-error">
             <img src="<?php echo $imgUrl ?>img/error.png">
             <h3><strong>Order does not exist</strong></h3>
          </div> 
        </div>
    <?php 
    return;
  }
?>

<div class="confirmation_order">
	<div class="order_msg">
		<img src="<?php echo $imgUrl ?>img/circle-icon.png">
		<h1>Order confirmed!</h1>
		&nbsp;
		<h3>Awesome, We have successfuly </br>received your order!</h3>
	</div>

	<div class="order_num">
		<h1>your order number is <strong><?php echo do_shortcode('[urlcost param="id" ]') ?></strong> and your </br> total cost is <strong><?php echo do_shortcode('[urlcost param="cost" ]') ?> .</strong></h1>
		&nbsp;
		<p>you can choose from one of the following payment methods.please note you </br> have <strong>7 days</strong> to make payment</p>
	</div>

	<div class="order-img">
		<img src="<?php echo $imgUrl ?>img/finger-pointing-down-emoji-by-google.png" width="100px">
	</div>

	<div style="visibility: hidden; position: absolute; width: 0px; height: 0px;">
	  <svg xmlns="http://www.w3.org/2000/svg">
	    <symbol viewBox="0 0 24 24" id="expand-more">
	      <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/><path d="M0 0h24v24H0z" fill="none"/>
	    </symbol>
	    <symbol viewBox="0 0 24 24" id="close">
	      <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/><path d="M0 0h24v24H0z" fill="none"/>
	    </symbol>
	  </svg>
	</div>

	<details open>
	  <summary>
	    Bank Deposit (we don't charge any fees for this)
	    <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
	    <svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
	  </summary>
	  <p>Open up your internet banking page, click "Transfer" and then enter the following details:<br><br>
	    Account Name: <b>NOVELTY PRINTS</b><br>
	    BSB: <b>342098</b> <br>
	Account Number: <b>584493412</b><br>
	    Payment Description: <b> <?php echo do_shortcode('[urlcost param="id" ]') ?>  (- VERY IMPORTANT TO INCLUDE THIS)</b>
	</p>
	</details>

	<details>

	  <summary>
	    PayPal (we charge $10 for these payments)
	    <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
	    <svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
	  </summary>
	  <p>To pay by PayPal, please email us at <b>fakiesaustralia@gmail.com</b> and tell us your order number and "I would like to pay by PayPal"</p>
	</details>
</div>
