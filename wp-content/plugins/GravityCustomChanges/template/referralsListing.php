<?php

/** *************************** RENDER TEST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */

  //Create an instance of our package class...
  $referralsListTable = new IDSReferralsListTable();

  //Fetch, prepare, sort, and filter our data...
  $referralsListTable->prepare_items();
?>
<div class="wrap">
  <?php echo $referralsListTable->message; ?> 
  <form method="post" action="">
    <?php $referralsListTable->display(); ?> 
  </form>               
</div>
