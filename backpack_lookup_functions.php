<?php

/* Given an array (ex: $v_hats), and an item name ("Engineer's Cap")*/
function set_item_in_array($item_array, $item_name) {
    if(isset($item_array[$item_name])) {
      $item_array[$item_name] += 1;
    }
    else {
      $item_array[$item_name] = 1;
    }
    return $item_array;
}
?>