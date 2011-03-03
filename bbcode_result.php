<?php session_start();

require_once('backpack_lookup_functions.php');
require_once('backpack_data.php');

// Retrieve POST and SESSION variables.
$show_u_hats = $_POST['u_hats'];
$show_v_hats = $_POST['v_hats'];
$show_hats = $_POST['hats'];
$show_v_weps = $_POST['v_weps'];
$show_weps = $_POST['weps'];
$show_tools = $_POST['tools'];
$show_metal = $_POST['metal'];
$show_paints = $_POST['paints'];
$show_crates = $_POST['crates'];

$dup_weps_only = $_POST['dup_weps_only'];

$valid_pages = $_POST['pages'];

$steamID = $_SESSION['steamID'];
//$steamID = 76561197961814215;
$avatar = $_SESSION['avatar'];
$username = $_SESSION['username'];

// Call Steam API
$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$steamID."&key=74EA34072E00ED29B92691B6F929F590";
/* Evil Mav's backpack 76561197961814215*/
//$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=76561197961814215&key=74EA34072E00ED29B92691B6F929F590";

$schema_url = "http://api.steampowered.com/ITFItems_440/GetSchema/v0001/?key=74EA34072E00ED29B92691B6F929F590&language=en";

$backpack_json = file_get_contents($backpack_url);
$backpack = json_decode($backpack_json);

$schema_json = file_get_contents($schema_url);
$schema = json_decode($schema_json);

/* Schema setup */
// Create associative arrays mapping item defindexes to their names and their image URLs.
$item_names = array();
$item_slots = array();
$item_classes = array();

foreach ( $schema->{"result"}->{"items"}->{"item"} as $entry ) {
  $item_names[$entry->{"defindex"}] = $entry->{"item_name"};
  $item_slots[$entry->{"defindex"}] = $entry->{"item_slot"};
  $item_classes[$entry->{"defindex"}] = $entry->{"item_class"};
}

/* TF2 Schema specific setup. Dictionaries, definition maps, etc. */
$vintage_quality = $schema->{"result"}->{"qualities"}->{"vintage"};
$normal_quality = $schema->{"result"}->{"qualities"}->{"unique"};
$unusual_quality = $schema->{"result"}->{"qualities"}->{"rarity4"};

/* Inventory setup */
$promo_weapons = array();
$vintage_weapons = array();
$weapons = array();

$unusual_hats = array();
$vintage_hats = array();
$high_promo_hats = array();
$promo_hats = array();
$polycount_set_hats = array();
$xmas2010_hats = array();
$hats = array();

$paints = array();
$tools = array();

$metals = array();

$crates = array();

$weapon_slots = array("primary", "secondary", "melee", "pda", "pda2");

$mask = 0xFFFF;   // Get the first word in the inventory token -> corresponds to inventory slot.


if(isset($steamID)) {
  foreach ( $backpack->{"result"}->{"items"}->{"item"} as $inv_entry ) {
    $my_defindex = $inv_entry->{"defindex"};
    $my_quality = $inv_entry->{"quality"};

    $my_item_name = $item_names[$my_defindex];
    $my_item_slot = $item_slots[$my_defindex];
    $my_item_class = $item_classes[$my_defindex];
    
    $inventory_token = $inv_entry->{"inventory"};
    $my_inventory_slot = (int)$inventory_token & $mask;
    $my_inventory_page = floor(($my_inventory_slot - 1) / 50) + 1;
    
    /* Skip invalid pages */
    if (!in_array("all", $valid_pages) and !in_array($my_inventory_page, $valid_pages)) {
      continue;
    }
    
    /* Weapons - don't show custom-named stock weapons (defindex 0 through 30) */
    if (in_array($my_item_slot, $weapon_slots) and $my_defindex > 30) {
      if (in_array($my_item_name, $PROMO_WEAPONS_DICT)) {
        if ($my_quality == $vintage_quality) {
          $promo_weapons = set_item_in_array($promo_weapons, "Vintage ".$my_item_name);
        }
        else {
          $promo_weapons = set_item_in_array($promo_weapons, $my_item_name);
        }
      }
      else if ($my_quality == $vintage_quality) {
        $vintage_weapons = set_item_in_array($vintage_weapons, $my_item_name);
      }
      else if ($my_quality == $normal_quality) {
        $weapons = set_item_in_array($weapons, $my_item_name);
      }
      else if ($my_quality == $unusual_quality and $my_item_name == "Horseless Headless Horsemann's Headtaker") {
        if(isset($weapons["UHHH Axe"])) {
          $weapons["UHHH Axe"] += 1;
        }
        else {
          $weapons["UHHH Axe"] = 1;
        }
      }
    }

    /* Hats */ 
    else if (($my_item_slot == "head") or ($my_item_slot == "misc")) {
      /* We save unusuals as strings right from the start. */
      if ($my_quality == $unusual_quality) {
        $attrs = $inv_entry->{"attributes"}->{"attribute"};
        $effect = "";
        foreach ( $inv_entry->{"attributes"}->{"attribute"} as $attr ) {
          if ($attr->{"defindex"} == 134) {   // 134 is the unusual particle effect attr
            $effect = $EFFECT_MAP[intval($attr->{"float_value"})];
            break;
          }
        }
        array_push($unusual_hats, "Unusual ".$my_item_name." (".$effect.")");
      }
      
      else if (in_array($my_item_name, $HIGH_PROMO_HATS_DICT)) {
        if ($my_item_name == "Gentle Manne's Service Medal") {
          $high_promo_hats = set_item_in_array($high_promo_hats, $my_item_name." (#".$inv_entry->{"attributes"}->{"attribute"}[0]->{"value"}.")");
        }
        else if ($my_quality == $vintage_quality) {
          $high_promo_hats = set_item_in_array($high_promo_hats, "Vintage ".$my_item_name);
        }
        else {
          $high_promo_hats = set_item_in_array($high_promo_hats, $my_item_name);
        }
      }
      else if (in_array($my_item_name, $PROMO_HATS_DICT)) {
        if ($my_quality == $vintage_quality) {
          $promo_hats = set_item_in_array($promo_hats, "Vintage ".$my_item_name);
        }
        else {
          $promo_hats = set_item_in_array($promo_hats, $my_item_name);
        }
      }
      else if (in_array($my_item_name, $POLYCOUNT_SET_HATS_DICT)) {
        $polycount_set_hats = set_item_in_array($polycount_set_hats, $my_item_name);
      }
      else if (in_array($my_item_name, $XMAS2010_HATS_DICT)) {
        $xmas2010_hats = set_item_in_array($xmas2010_hats, $my_item_name);
      }
      else if ($my_quality == $vintage_quality) {
        $vintage_hats = set_item_in_array($vintage_hats, $my_item_name);
      }
      else if ($my_quality == $normal_quality and !in_array($my_item_name, $HAT_BLACKLIST)) {
        $hats = set_item_in_array($hats, $my_item_name);
      }

    }

    /* Paints */
    else if (($my_item_class == "tool") and (isset($PAINT_MAP[$my_item_name]))) {
      $paints = set_item_in_array($paints, $my_item_name);
    }

    /* Tools - name/desc tags, slot/class tokens*/
    else if ($my_item_class == "tool" or $my_item_name == "Duel MiniGame" or $my_item_class == "class_token" or $my_item_class == "slot_token") {
      $tools = set_item_in_array($tools, $my_item_name);
    }
    
    /* Metal */
    else if ($my_item_class == "craft_item") {
      $metals = set_item_in_array($metals, $my_item_name);
    }
    
    /* Crates (item_class == supply_crate)*/
    else if ($my_item_class == "supply_crate") {
      $series_number = $inv_entry->{"attributes"}->{"attribute"}[0]->{"float_value"};
      $crates = set_item_in_array($crates, $series_number);
    }

  }
}
?>

<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TF2Toolbox: Backpack Tools for Team Fortress 2</title>
	<link href="stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/bbcode_style.css" media="screen" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="media/favicon.ico" />
	<link rel="icon" type="image/png" href="media/engie_toolbox_32.png" />
</head>
<body>
  <div id="header">
    
  <?php
  require('header.php');
  ?>
  
    <div id="header_toolbar">
      <table id="header_toolbar_table">
        <tr>
          <td><a href="index.php">Home</a></td>
          <td id="active_tool_cell"><a href="bbcode_lookup.php">BBCode Translator</a></td>
          <td><a href="metal_lookup.php">Metal Maker</a></td>
        </tr>
      </table>
    </div>
  </div>
  
  <div id="content">
   
    <div id="bbcode_output">
      <textarea id="bbcode_text" rows="22" cols="102">
<?php

/* HATS - vintage and non vintage*/
if ($show_v_hats == "True" or $show_hats == "True" or $show_u_hats == "True") {
  echo "[b][u]Hats For Trade[/b][/u][list]\n";

  if ($show_u_hats == "True" and !empty($unusual_hats)) {
    echo "[b][u]Unusuals[/b][/u]\n";
    foreach ( $unusual_hats as $u_hat) {
      echo "[*][b][color=#8650AC]";
      
      echo $u_hat;

      echo "[/b][/color]\n";
    }
    echo "\n";
  }
  
  if ($show_hats == "True" and !empty($high_promo_hats)) {
    echo "[b][u]Rare Promos[/b][/u]\n";
    foreach ( array_keys($high_promo_hats) as $hp_hat ) {
      echo "[*][b]";
      
      if (substr($hp_hat, 0, 7) == "Vintage") {
        echo "[color=#476291]";
      }
      else {
        echo "[color=#A59003]";
      }

      if ($high_promo_hats[$hp_hat] > 1) {
        echo $hp_hat." (".$high_promo_hats[$hp_hat].")";
      }
      else {
        echo $hp_hat;
      }

      echo "[/b][/color]\n";
    }
    echo "\n";
  }
  
  if ($show_v_hats == "True" and !empty($vintage_hats)) {
    echo "[b][u]Vintage Hats[/b][/u]\n";
    foreach ( array_keys($vintage_hats) as $v_hat ) {
      echo "[*][b][color=#476291]";

      if ($vintage_hats[$v_hat] > 1) {
        echo "Vintage ".$v_hat." (".$vintage_hats[$v_hat].")";
      }
      else {
        echo "Vintage ".$v_hat;
      }

      echo "[/b][/color]\n";

    }
    echo "\n";
  }
  
  if ($show_hats == "True" and !empty($polycount_set_hats)) {
    echo "[b][u]Polycount Set Hats[/b][/u]\n";
    foreach ( array_keys($polycount_set_hats) as $hat ) {
      echo "[*][b][color=#A59003]";

      if ($polycount_set_hats[$hat] > 1) {
        echo $hat." (".$polycount_set_hats[$hat].")";
      }
      else {
        echo $hat;
      }

      echo "[/b][/color]\n";
    }
    echo "\n";
  }
  
  if ($show_hats == "True" and !empty($xmas2010_hats)) {
    echo "[b][u]Australian Christmas Hats[/b][/u]\n";
    foreach ( array_keys($xmas2010_hats) as $hat ) {
      echo "[*][b][color=#A59003]";

      if ($xmas2010_hats[$hat] > 1) {
        echo $hat." (".$xmas2010_hats[$hat].")";
      }
      else {
        echo $hat;
      }

      echo "[/b][/color]\n";

    }
    echo "\n";
  }
  
  if ($show_hats == "True" and !empty($hats)) {
    echo "[b][u]Regular Hats[/b][/u]\n";
    foreach ( array_keys($hats) as $hat ) {
      echo "[*][b][color=#A59003]";

      if ($hats[$hat] > 1) {
        echo $hat." (".$hats[$hat].")";
      }
      else {
        echo $hat;
      }

      echo "[/b][/color]\n";
    }
    echo "\n";
  }
  
  if ($show_hats == "True" and !empty($promo_hats)) {
    echo "[b][u]Promo Hats[/b][/u]\n";
    foreach ( array_keys($promo_hats) as $p_hat ) {
      echo "[*][b]";
      
      if (substr($p_hat, 0, 7) == "Vintage") {
        echo "[color=#476291]";
      }
      else {
        echo "[color=#A59003]";
      }

      if ($promo_hats[$p_hat] > 1) {
        echo $p_hat." (".$promo_hats[$p_hat].")";
      }
      else {
        echo $p_hat;
      }

      echo "[/b][/color]\n";
    }
    echo "\n";
  }

}

if ($show_v_hats == "True" or $show_hats == "True" or $show_u_hats == "True") {
  echo "[/list]\n";
}

/* WEAPONS - vintage and non-vintage */

if ($show_v_weps == "True" or $show_weps == "True") {
  echo "[b][u]Weapons For Trade[/b][/u][list]\n";
  
  if ($show_v_weps == "True" and !empty($vintage_weapons)) {
    echo "[b][u]Vintage Weapons[/b][/u]\n";
    foreach ( array_keys($vintage_weapons) as $v_weapon ) {
      
      $vint_name = "Vintage ".$v_weapon;

      if ($dup_weps_only == "True") {
        if ($vintage_weapons[$v_weapon] > 1) {
          echo "[*][b][color=#476291]";
          $tempnum = $vintage_weapons[$v_weapon] - 1;
          echo $vint_name;
          if ($tempnum > 1) {
            echo " (".$tempnum.")";
          }
          echo "[/b][/color]\n";   // Close the tag.
        }
      }

      else {
        echo "[*][b][color=#476291]";
        echo $vint_name;
        if ($vintage_weapons[$v_weapon] > 1) {
          echo " (".$vintage_weapons[$v_weapon].")";
        }
        echo "[/b][/color]\n";   // Close the tag.
      }
    }
    echo "\n";
  }

  if ($show_weps == "True" and !empty($weapons)) {
    echo "[b][u]Normal Weapons[/b][/u]\n";
    foreach ( array_keys($weapons) as $weapon ) {

      if ($dup_weps_only == "True") {
        if ($weapons[$weapon] > 1) {
          echo "[*][b][color=#A59003]";
          $tempnum = $weapons[$weapon] - 1;
          echo $weapon;
          if ($tempnum > 1) {
            echo " (".$tempnum.")";
          }
          echo "[/b][/color]\n";
        }
      }

      else {
        echo "[*][b][color=#A59003]";
        if ($weapons[$weapon] > 1) {
          echo $weapon." (".$weapons[$weapon].")";
        }
        else {
          echo $weapon;
        }
        echo "[/b][/color]\n";
      }

    }
    echo "\n";
  }
  
  if ($show_weps =="True" and !empty($promo_weapons)) {
    echo "[b][u]Promo Weapons[/b][/u]";
    foreach ( array_keys($promo_weapons) as $p_wep ) {
      echo "[*][b]";
      
      if (substr($p_wep, 0, 7) == "Vintage") {
        echo "[color=#476291]";
      }
      else {
        echo "[color=#A59003]";
      }

      if ($promo_weapons[$p_wep] > 1) {
        echo $p_wep." (".$promo_weapons[$p_wep].")";
      }
      else {
        echo $p_wep;
      }

      echo "[/b][/color]\n";
    }
    echo "\n";
  }
}

if ($show_v_weps == "True" or $show_weps == "True") {
  echo "[/list]\n";
}

/* PAINT - all colors */
if ($show_paints == "True") {
  echo "[b][u]Paints For Trade[/b][/u][list]\n";

  foreach ( array_keys($paints) as $paint ) {
    echo "[*][b]";    // No color: paint translate will handle color.

    if ($paints[$paint] > 1) {
      echo $PAINT_MAP[$paint]." (".$paints[$paint].")";
    }
    else {
      echo $PAINT_MAP[$paint];
    }

    echo "[/b][/color]\n";
  }

  echo "[/list]\n";
}


/* Tools and Misc */
if ($show_tools == "True") {
  echo "[b][u]Tools/Misc. For Trade[/b][/u][list]\n";

  foreach ( array_keys($tools) as $tool ) {
    echo "[*][b]";    // No color for tools.

    if ($tools[$tool] > 1) {
      echo $tool." (".$tools[$tool].")";
    }
    else {
      echo $tool;
    }

    echo "[/b]\n";
  }

  echo "[/list]\n";
}

/* Crates */
if ($show_crates == "True") {
  echo "[b][u]Crates For Trade[/b][/u][list]\n";

  foreach ( array_keys($crates) as $crate ) {
    echo "[*][b]";    // No color for crates.

    echo "Series ".$crate." Crate (".$crates[$crate].")";

    echo "[/b]\n";
  }

  echo "[/list]\n";
}

/* Metal */
if ($show_metal == "True") {
  echo "[b][u]Metal For Trade[/b][/u][list]\n";

  foreach ( array_keys($metals) as $metal ) {
    echo "[*][b]";    // No color for tools.

    if ($metals[$metal] > 1) {
      echo $metal." (".$metals[$metal].")";
    }
    else {
      echo $metal;
    }

    echo "[/b]\n";
  }

  echo "[/list]";
}

?>
      </textarea>

    </div>
    
    <div id="bbcode_status">
      <span id="bbcode_status_display">Status</span>
      <ul id="bbcode_status_list">
<?php

/* Items to display status */
$items_to_display_str = "Displaying ";
if ($show_u_hats == "True") {
  $items_to_display_str = $items_to_display_str."unusual hats, ";
}
if ($show_v_hats == "True") {
  $items_to_display_str = $items_to_display_str."vintage hats, ";
}
if ($show_hats == "True") {
  $items_to_display_str = $items_to_display_str."normal hats, ";
}
if ($show_v_weps == "True") {
  $items_to_display_str = $items_to_display_str."vintage weapons, ";
}
if ($show_weps == "True") {
  $items_to_display_str = $items_to_display_str."weapons, ";
}
if ($show_tools == "True") {
  $items_to_display_str = $items_to_display_str."tools and metal, ";
}
if ($show_paints == "True") {
  $items_to_display_str = $items_to_display_str."paints, ";
}
if ($show_crates == "True") {
  $items_to_display_str = $items_to_display_str."crates, ";
}

if (strlen($items_to_display_str) == 11) {
  echo "          <li>Displaying no items. Huh?</li>\n";
}
else {
  echo "          <li>".substr($items_to_display_str, 0, -2)."</li>\n";
}

/* Duplicate weapons display status */
if ($dup_weps_only == "True") {
  echo "        <li>Only displaying duplicate weapons.</li>\n";
}

/* Backpage page display status */
if (in_array("all", $valid_pages)) {
  echo "        <li>Displaying all backpack pages</li>\n";
}
else if (empty($valid_pages)) {
  echo "        <li>Not displaying any backpack pages. Huh?</li>\n";
}
else if (count($valid_pages) == 1) {
  echo "        <li>Displaying backpack page ".strval($valid_pages[0])."</li>\n";
}
else {
  $page_str = "<li>Displaying backpack pages ";
  foreach($valid_pages as $page) {
    $page_str = $page_str.strval($page).", ";
  }
  $page_str = substr($page_str, 0, -2);
  echo $page_str."</li>\n";
}


?>
      </ul>
      
      <form action="bbcode_lookup.php" method="get">
        <input type="submit" id="retry_button" value="Try Again" />
      </form>
    </div>
    
  </div>
  
  <?php
  require('footer.php');
  ?>
<?php require("google_analytics.php") ?>
</body>
</html>


