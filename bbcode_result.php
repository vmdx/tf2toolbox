<?php session_start(); ?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TF2Toolbox: Backpack Tools for Team Fortress 2</title>
	<link href="stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/bbcode_style.css" media="screen" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="media/favicon.ico" />
	<link rel="icon" type="image/png" href="media/engie_toolbox_32.png" />
</head>
<?php flush();

require_once('php/backpack_lookup_functions.php');
require_once('php/backpack_data.php');

$steamID = $_SESSION['steamID'];
$avatar = $_SESSION['avatar'];
$username = $_SESSION['username'];

// Call Steam API
$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$steamID."&key=74EA34072E00ED29B92691B6F929F590";
$schema_url = "http://api.steampowered.com/ITFItems_440/GetSchema/v0001/?key=74EA34072E00ED29B92691B6F929F590&language=en";


/* OFFLINE TESTING: To do offline testing, uncomment the following block. */
// 
// $_POST['pages'] = array("all");
// $_POST['u_hats'] = "True";
// $_POST['v_hats'] = "True";
// $atreus = 76561197961105061;
// $vmdx = 76561197998913767;
// $evilmav = 76561197961814215;
// $lordfa9 = 76561197990146376;
// $steamID = $atreus;
// $backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$steamID."&key=74EA34072E00ED29B92691B6F929F590";

/* Retrieve the backpack and schema*/
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

/* Quality map: 0->Normal, 1->Genuine, etc. */
$quality_map = array();
foreach($schema->{"result"}->{"qualities"} as $key=>$value) {
  $quality_map[$value] = $schema->{"result"}->{"qualityNames"}->{$key};
}

/* Inventory setup */
$vintage_weapons = array();
$weapons = array();
$promo_weapons = array();
$strange_weapons = array();

$unusual_hats = array();
$high_promo_hats = array();
$genuine_hats = array();
$new_hats = array();
$vintage_hats = array();
$set_hats = array();
$hats = array();
$promo_hats = array();

$paints = array();
$tools = array();

$metals = array();

$crates = array();

$mask = 0xFFFF;   // Get the first word in the inventory token -> corresponds to inventory slot.

$backpack_error = "";
if($backpack->{"result"}->{"status"} == 15) {
  $backpack_error = "Sorry, this backpack is private. :(";
}

else if(isset($steamID)) {
  foreach ( $backpack->{"result"}->{"items"}->{"item"} as $inv_entry ) {
    
    /* Skip invalid pages */
    $inv_token = $inv_entry->{"inventory"};
    $inv_slot = (int)$inv_token & $mask;
    $inv_page = floor(($inv_slot - 1) / 50) + 1;

    if (!in_array("all", $_POST['pages']) and !in_array($inv_page, $_POST['pages'])) {
      continue;
    }
    
    /* Skip untradables if marked.*/
    if($_POST['hide_untradable'] and $inv_entry->{"flag_cannot_trade"}) {
      continue;
    }
    
  
    $defindex = $inv_entry->{"defindex"};

    $item_name = $item_names[$defindex];
    $item_slot = $item_slots[$defindex];
    $item_class = $item_classes[$defindex];
    $craftnum = -1;
    
    /* Skip blacklisted items */
    if(in_array($item_name, $HAT_BLACKLIST) or in_array($item_name, $ITEM_BLACKLIST)) {
      continue;
    }
    
    /* Suffixes for strings such as "(Level 66, Gifted, Untradable)"*/
    $suffix_tags = array();
    
    if ($inv_entry->{"flag_cannot_trade"}) {
      array_push($suffix_tags, "Untradable");
    }
    
    /* Check attributes for gifted status, painted status, and unique craft index. */
    $is_gifted = false;
    
    if ($inv_entry->{"attributes"}->{"attribute"}) {
      foreach($inv_entry->{"attributes"}->{"attribute"} as $attr) {
        if ($attr->{"defindex"} == 186) { // gifted
          $is_gifted = true;
          array_push($suffix_tags, "Gifted");
        }
        else if ($_POST['display_paint'] and $attr->{"defindex"} == 142 and $PAINT_NUMBER_MAP[intval($attr->{"float_value"})]) { // 142 == paint tint
          array_push($suffix_tags, $PAINT_NUMBER_MAP[intval($attr->{"float_value"})]);
        }
        else if ($attr->{"defindex"} == 229) { // 229 = unique craft index
          $craftnum = $attr->{"value"};
        }
      }
    }

    /* Skip gifted if marked. */
    if($_POST['hide_gifted'] and $is_gifted) {
      continue;
    }
    
    /* Display hat levels in suffix if needed. */
    if (($item_slot == "head" or $item_slot == "misc") and $_POST['display_hat_levels']) {
      array_unshift($suffix_tags, "Level ".$inv_entry->{"level"});
    }

    /* Prefix for dirty/clean items. */
    $prefix = "";
    if(in_array($item_name, $CLEAN_DIRTY_ITEMS)) {
      $prefix = "Clean ";
      if ($inv_entry->{"flag_cannot_trade"} or $is_gifted) {
        $prefix = "Dirty ";
      }
    }
    
    $suffix = "";
    if (!empty($suffix_tags)) {
      $suffix = " (".implode(", ", $suffix_tags).")";
    }
    
    /* BBCode color now goes in prefix and suffix. */
    switch($quality_map[$inv_entry->{"quality"}]) {
      case "Unusual":
        $prefix = "[color=#8650AC]Unusual ".$prefix;
        $suffix = "[/color]".$suffix;
        break;
      case "Genuine":
        $prefix = "[color=#4D7455]Genuine ".$prefix;
        $suffix = "[/color]".$suffix;
        break;
      case "Strange":
        $prefix = "[color=#CD9B1D]Strange ".$prefix;
        $suffix = "[/color]".$suffix;
        break;
      case "Vintage":
        $prefix = "[color=#476291]Vintage ".$prefix;
        $suffix = "[/color]".$suffix;
        break;
      case "Unique":
        $prefix = "[color=#A59003]".$prefix;
        $suffix = "[/color]".$suffix;
        break;
    }
    
    /* Craft number goes directly in the item name. */
    if ($craftnum > 0 and ($craftnum < 101 or $_POST['display_craft_num'])) {
      $item_name .= " #".$craftnum;
    }
    
    switch(get_category($item_slot, $item_class, $item_name, $quality_map[$inv_entry->{"quality"}])) {
      case "Unusual Hats":
        $effect = "";
        foreach ( $inv_entry->{"attributes"}->{"attribute"} as $attr ) {
          if ($attr->{"defindex"} == 134) {   // 134 is the unusual particle effect attr
            $effect = $EFFECT_MAP[intval($attr->{"float_value"})];
            break;
          }
        }
        $unusual_hats = set_item_in_array($unusual_hats, $prefix.$item_name." (".$effect.")".$suffix);
        break;
      case "High Promo Hats":
        if ($item_name == "Gentle Manne's Service Medal") {
          $high_promo_hats = set_item_in_array($high_promo_hats, $prefix.$item_name." (#".$inv_entry->{"attributes"}->{"attribute"}[0]->{"value"}.")".$suffix);
        }
        else {
          $high_promo_hats = set_item_in_array($high_promo_hats, $prefix.$item_name.$suffix);
        }
        break;
      case "Genuine Hats":
        $genuine_hats = set_item_in_array($genuine_hats, $prefix.$item_name.$suffix);
        break;
      case "Vintage Hats":
        $vintage_hats = set_item_in_array($vintage_hats, $prefix.$item_name.$suffix);
        break;
      case "Set Hats":
        $set_hats = set_item_in_array($set_hats, $prefix.$item_name.$suffix);
        break;
      case "New Hats":
        $new_hats = set_item_in_array($new_hats, $prefix.$item_name.$suffix);
        break;
      case "Promo Hats":
        $promo_hats = set_item_in_array($promo_hats, $prefix.$item_name.$suffix);
        break;
      case "Normal Hats":
        $hats = set_item_in_array($hats, $prefix.$item_name.$suffix);
        break;
        
      case "Strange Weapons":
        $strange_weapons = set_item_in_array($strange_weapons, $prefix.$item_name.$suffix);
				break;
      case "Vintage Weapons":
        /* Display any off level weapons. */
        if (!$WEAPON_LEVEL_MAP[$inv_entry->{"level"}] or !in_array($item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}])) {
          array_unshift($suffix_tags, "Level ".$inv_entry->{"level"});
          $suffix = "[/color] (".implode(", ", $suffix_tags).")";
        }
        $vintage_weapons = set_item_in_array($vintage_weapons, $prefix.$item_name.$suffix);     
        break;
      case "Normal Weapons":
        /* Don't show custom named stock weapons - (defindex < 30, > 189 and < 213) */
        if ($defindex > 30 and !($defindex > 189 and $defindex < 213)) {
          $weapons = set_item_in_array($weapons, $prefix.$item_name.$suffix);
        }
        break;
      case "Promo Weapons":
        $promo_weapons = set_item_in_array($promo_weapons, $prefix.$item_name.$suffix);
        break;
        
      case "Paints":
        $paints = set_item_in_array($paints, $item_name);
        break;
      case "Tools":
        $tools = set_item_in_array($tools, $item_name);
        break;
      case "Metal":
        $metals = set_item_in_array($metals, $item_name);
        break;
      case "Crates":
        $series_number = $inv_entry->{"attributes"}->{"attribute"}[0]->{"float_value"};
        $crates = set_item_in_array($crates, $series_number);
        break;
    }

  }
}

/* Sort the arrays according to our sort option. */
function cmpWithLevels($a, $b) {
  $a_position = strpos($a, "(");
  $b_position = strpos($b, "(");
  $a_strip = $a;
  $b_strip = $b;
  if ($a_position === false and $b_position === false) {
    return strcmp($a, $b);
  }
  if ($a_position !== false) {
    $a_strip = substr($a, 0, strpos($a, "("));
  }
  if ($b_position !== false) {
    $b_strip = substr($b, 0, strpos($b, "("));
  }

  if ($a_strip == $b_strip) {
    if (strlen($a) == strlen($b)) {
      return strcmp($a, $b);
    }
    else {
      return strlen($a) - strlen($b);
    }
  }
  else {
    return strcmp($a, $b);
  }
}
if($_POST['output_sort'] == "alpha") {
	uksort($strange_weapons, "cmpWithLevels");
  uksort($promo_weapons, "cmpWithLevels");
  uksort($vintage_weapons, "cmpWithLevels");
  uksort($weapons, "cmpWithLevels");
  uksort($unusual_hats, "cmpWithLevels");
  uksort($vintage_hats, "cmpWithLevels");
  uksort($genuine_hats, "cmpWithLevels");
  uksort($high_promo_hats, "cmpWithLevels");
  uksort($promo_hats, "cmpWithLevels");
  uksort($set_hats, "cmpWithLevels");
  uksort($new_hats, "cmpWithLevels");
  uksort($hats, "cmpWithLevels");
}
    
ksort($metals); // Metals should be sorted Ref/Rec/Scrap/Others. TODO.
ksort($crates); // Tools, crates, paints ALWAYS sorted alphabetically.
ksort($tools);  
ksort($paints);

?>

<body>
  <div id="header">
    
  <?php
  require('php/header.php');
  if (!empty($backpack_error)) {
    $error_msg = $backpack_error;
  }
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

<?php
  if ($error_msg != '') {
    echo '  <div id="error_bar">'."\n";
    echo '    <span id="error_msg">'.$error_msg."</span>\n";
    echo "  </div>\n";
  }
?>

  <div id="content">
   
    <div id="bbcode_output">
      <textarea id="bbcode_text" rows="22" cols="102">
<?php

require_once('php/bbcode_output.php');

$first_item = true;

$credit = '';
if ($_POST['display_credit']) {
  $credit = "[color=#cd5c5c] (List generated at [URL=http://tf2toolbox.com/bbcode_lookup.php]TF2Toolbox.com[/URL])[/color]";
}

/* HATS - vintage and non vintage*/
if ($_POST['v_hats'] or $_POST['hats'] or $_POST['u_hats'] or $_POST['hp_hats'] or $_POST['p_hats'] or $_POST['g_hats']) {
  open_header("Hats For Trade", $credit, &$first_item);

  output_section($unusual_hats, $_POST['u_hats'], "Unusuals");
  output_section($high_promo_hats, $_POST['hp_hats'], "Rare Promos");
  output_section($genuine_hats, $_POST['g_hats'], "Genuine Hats");
  output_section($new_hats, $_POST['hats'], "Uber Update Hats");
  output_section($vintage_hats, $_POST['v_hats'], "Vintage Hats");
  output_section($set_hats, $_POST['hats'], "Polycount Set Hats");
  output_section($hats, $_POST['hats'], "Regular Hats");
  output_section($promo_hats, $_POST['p_hats'], "Promo Hats");

  if(empty($vintage_hats) and empty($hats) and empty($promo_hats) and empty($high_promo_hats) and empty($unusual_hats) and empty($new_hats) and empty($set_hats) and empty($genuine_hats)) {
    echo "None\n";
  }
  echo "[/list]\n";
}

/* WEAPONS - strange, vintage and non-vintage */

if ($_POST['v_weps'] or $_POST['weps'] or $_POST['p_weps'] or $_POST['s_weps']) {
  open_header("Weapons For Trade", $credit, &$first_item);
  
  output_section($strange_weapons, $_POST['s_weps'], "Strange Weapons");
  output_section($vintage_weapons, $_POST['v_weps'], "Vintage Weapons");
  output_section($weapons, $_POST['weps'], "Normal Weapons");
  output_section($promo_weapons, $_POST['p_weps'], "Promo Weapons");
  
  if(empty($vintage_weapons) and empty($weapons) and empty($promo_weapons) and empty($strange_weapons)) {
    echo "None\n";
  }
  echo "[/list]\n";
}

/* PAINT - all colors */
if ($_POST['paints']) {
  open_header("Paint For Trade", $credit, &$first_item);

foreach ( array_keys($paints) as $paint ) {
  echo "[*][b]";    // No color: paint translate will handle color.
  if ($paints[$paint] > 1) {
    echo $PAINT_MAP[$paint]."[/color] x ".$paints[$paint];
  }
  else {
    echo $PAINT_MAP[$paint]."[/color]";
  }
  echo "[/b]\n";
}

  if(empty($paints)) {
    echo "None\n";
  }

  echo "[/list]\n";
}


/* Tools and Misc */
if ($_POST['tools']) {
  open_header("Tools/Misc. For Trade", $credit, &$first_item);

  output_section($tools, $_POST['tools']);
  
  if(empty($tools)) {
    echo "None\n";
  }

  echo "[/list]\n";
}

/* Crates */
if ($_POST['crates']) {
  open_header("Crates For Trade", $credit, &$first_item);

  foreach ( array_keys($crates) as $crate ) {
    echo "[*][b]";    // No color for crates.

    echo "Series ".$crate." Crate x ".$crates[$crate];

    echo "[/b]\n";
  }

  if(empty($crates)) {
    echo "None\n";
  }
  
  echo "[/list]\n";
}

/* Metal */
if ($_POST['metal']) {
  open_header("Metal For Trade", $credit, &$first_item);

  output_section($metals, $_POST['metal']);
  
  if(empty($metals)) {
    echo "None\n";
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
if ($_POST['u_hats']) {
  $items_to_display_str .= "unusual hats, ";
}
if ($_POST['g_hats']) {
  $items_to_display_str .= "genuine hats, ";
}
if ($_POST['v_hats']) {
  $items_to_display_str .= "vintage hats, ";
}
if ($_POST['hats']) {
  $items_to_display_str .= "normal hats, ";
}
if ($_POST['s_weps']) {
	$items_to_display_str .= "strange weapons, ";
}
if ($_POST['v_weps']) {
  $items_to_display_str .= "vintage weapons, ";
}
if ($_POST['weps']) {
  $items_to_display_str .= "weapons, ";
}
if ($_POST['tools']) {
  $items_to_display_str .= "tools, ";
}
if ($_POST['paints']) {
  $items_to_display_str .= "paints, ";
}
if ($_POST['crates']) {
  $items_to_display_str .= "crates, ";
}
if ($_POST['metal']) {
  $items_to_display_str .= "metal, ";
}

if (strlen($items_to_display_str) == 11) {
  echo "          <li>Displaying no items. Huh?</li>\n";
}
else {
  echo "          <li>".substr($items_to_display_str, 0, -2)."</li>\n";
}

/* Options display status */
if ($_POST['dup_weps_only']) {
  echo "        <li>Only displaying duplicate weapons; keeping ONE of each unique weapon, priority to off-level, then vintage, then normal</li>\n";
}
if ($_POST['display_hat_levels']) {
  echo "        <li>Displaying levels for all hats</li>\n";
}
if ($_POST['hide_untradable']) {
  echo "        <li>Hiding untradable (dirty) items</li>\n";
}
if ($_POST['hide_gifted']) {
  echo "        <li>Hiding gifted (dirty) items</li>\n";
}

switch ($_POST['output_sort']) {
  case "alpha":
    echo "        <li>Sorting items alphabetically</li>\n";
    break;
  case "class":
    echo "        <li>Sorting items by class</li>\n";
    break;
  case "item_slot":
    echo "        <li>Sorting items by item slot</li>\n";
    break;
}

/* Backpage page display status */
if (in_array("all", $_POST['pages'])) {
  echo "        <li>Displaying all backpack pages</li>\n";
}
else if (empty($_POST['pages'])) {
  echo "        <li>Not displaying any backpack pages. Huh?</li>\n";
}
else if (count($_POST['pages']) == 1) {
  echo "        <li>Displaying backpack page ".strval($_POST['pages'][0])."</li>\n";
}
else {
  $page_str = "<li>Displaying backpack pages ";
  foreach($_POST['pages'] as $page) {
    $page_str = $page_str.strval($page).", ";
  }
  $page_str = substr($page_str, 0, -2);
  echo $page_str."</li>\n";
}


?>
      </ul>
      
      <!-- <script type="text/javascript">
      function copyToClipboard() {
        window.clipboardData.setData("text", "test");
        alert("BBCode copied to clipboard!");
      }
      </script>

      <button type="button" onclick="copyToClipboard()">Copy to Clipboard</button> -->

      <form action="bbcode_lookup.php" method="get">
        <input type="submit" id="retry_button" value="Try Again" />
      </form>
    </div>
    
  </div>

  <!-- <div id="adstrip">
    <div id="adbox" style="width:468px; margin:auto;">
      <script type="text/javascript">
      google_ad_client = "ca-pub-2260733802952622";
      /* Standard Banner */
      google_ad_slot = "1084975241";
      google_ad_width = 468;
      google_ad_height = 60;
      </script>
      <script type="text/javascript"
      src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
      </script>
    </div>
  </div> -->
    
  <?php
  require('php/footer.php');
  ?>
  
<?php require("php/google_analytics.php") ?></body>
</html>


