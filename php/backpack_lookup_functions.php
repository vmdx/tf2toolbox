<?php

require('backpack_data.php');

/* Constants for backpack categorization. */
$WEAPON_SLOTS = array("primary", "secondary", "melee", "pda", "pda2");
$HAT_SLOTS = array("head", "misc");

/* Given item info, determine what category we want to use for it. */

function get_category($item_slot, $item_class, $item_name, $quality) {
  global $WEAPON_SLOTS, $HAT_SLOTS, $PROMO_WEAPONS_LIST, $HIGH_PROMO_HATS_LIST, $PROMO_HATS_LIST, $SET_HATS_LIST, $NEW_HATS_LIST, $PAINT_MAP;
  if(in_array($item_slot, $WEAPON_SLOTS) and $item_class != "slot_token") {
    if(in_array($item_name, $PROMO_WEAPONS_LIST)) {
      return "Promo Weapons";
    }
    switch($quality) {
      case "Unusual":
      case "Genuine":
        return "Promo Weapons";
      case "Vintage":
        return "Vintage Weapons";
      case "Strange":
        return "Strange Weapons";
      case "Unique":
        return "Normal Weapons";
    }
  }
  if(in_array($item_slot, $HAT_SLOTS)) {
    if($quality == "Unusual") {  // top priority
      return "Unusual Hats";
    }
    if(in_array($item_name, $HIGH_PROMO_HATS_LIST)) {
      return "High Promo Hats";
    }
    else if (in_array($item_name, $PROMO_HATS_LIST)) {
      return "Promo Hats";
    }
    else if (in_array($item_name, $SET_HATS_LIST)) {
      return "Set Hats";
    }
    else if (in_array($item_name, $NEW_HATS_LIST)) {
      return "New Hats";
    }
    switch($quality) {
      case "Genuine":
        return "Genuine Hats";
      case "Vintage":
        return "Vintage Hats";
      case "Unique":
        return "Normal Hats";
    }
  }
  if($item_class == "tool" and isset($PAINT_MAP[$item_name])) {
    return "Paints";
  }
  if($item_class == "tool" or $item_class == "class_token" or $item_class == "slot_token" or $item_slot == "action") {
    return "Tools";
  }
  if($item_class == "craft_item") {
    return "Metal";
  }
  if($item_class == "supply_crate") {
    return "Crates";
  }
  
  echo "ERROR - could not categorize: ".$item_slot." ".$item_class." ".$quality;
  return "None";
}


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