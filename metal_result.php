<?php session_start();

require_once('backpack_lookup_functions.php');
require_once('backpack_data.php');

// Retrieve POST and SESSION variables.
$weapon_use = $_POST['weapon_use'];

$valid_pages = $_POST['pages'];

$steamID = $_SESSION['steamID'];
//$steamID = 76561197961814215;
$avatar = $_SESSION['avatar'];
$username = $_SESSION['username'];

// Call Steam API
$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$steamID."&key=74EA34072E00ED29B92691B6F929F590";
/* Evil Mav's backpack */
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
$used_by_classes = array();

foreach ( $schema->{"result"}->{"items"}->{"item"} as $entry ) {
  $item_names[$entry->{"defindex"}] = $entry->{"item_name"};
  $item_slots[$entry->{"defindex"}] = $entry->{"item_slot"};
  $item_classes[$entry->{"defindex"}] = $entry->{"item_class"};
  $used_by_classes[$entry->{"defindex"}] = $entry->{"used_by_classes"}->{"class"};
}

/* TF2 Schema specific setup. Dictionaries, definition maps, etc. */
$vintage_quality = $schema->{"result"}->{"qualities"}->{"vintage"};
$normal_quality = $schema->{"result"}->{"qualities"}->{"unique"};

/* Inventory setup: special weapons contain all off-level, named, and described weapons */
$scout_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$soldier_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$pyro_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$demoman_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$heavy_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$engineer_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$medic_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$sniper_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$spy_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());
$multi_class_weapons = array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array());

$tokens = array();

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
    $my_use_class = implode("+", $used_by_classes[$my_defindex]);
    
    
    $inventory_token = $inv_entry->{"inventory"};
    $my_inventory_slot = (int)$inventory_token & $mask;
    $my_inventory_page = floor(($my_inventory_slot - 1) / 50) + 1;
    
    /* Skip invalid pages */
    if (!in_array("all", $valid_pages) and !in_array($my_inventory_page, $valid_pages)) {
      continue;
    }
    
    /* Weapons - don't show custom-named stock weapons (defindex 0 through 30, 190 through 212) */
    if (in_array($my_item_slot, $weapon_slots) and $my_defindex > 30 and ($my_defindex < 190 or $my_defindex > 212)) {
      if (in_array($my_item_name, $PROMO_WEAPONS_DICT) or ($my_quality != $vintage_quality and $my_quality != $normal_quality)) {
        continue;
      }
      
      switch ($my_use_class) {
        case "Scout":
          $scout_weapons = set_item_in_class_weapon_array($scout_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Soldier":
          $soldier_weapons = set_item_in_class_weapon_array($soldier_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Pyro":
          $pyro_weapons = set_item_in_class_weapon_array($pyro_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Demoman":
          $demoman_weapons = set_item_in_class_weapon_array($demoman_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Heavy":
          $heavy_weapons = set_item_in_class_weapon_array($heavy_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Engineer":
          $engineer_weapons = set_item_in_class_weapon_array($engineer_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Medic":
          $medic_weapons = set_item_in_class_weapon_array($medic_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Sniper":
          $sniper_weapons = set_item_in_class_weapon_array($sniper_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        case "Spy":
          $spy_weapons = set_item_in_class_weapon_array($spy_weapons, $my_item_name, $inv_entry, $my_quality == $vintage_quality, !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}]));
          break;
        default:
          break;
      }

    }

    /* Tokens - name/desc tags, slot/class tokens*/
    else if ($my_item_class == "class_token" or $my_item_class == "slot_token") {
      $tokens = set_item_in_array($tokens, $my_item_name);
    }
    
    /* Metal */
    else if ($my_item_class == "craft_item") {
      $metals = set_item_in_array($metals, $my_item_name);
    }

  }
}
?>

<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TF2Toolbox: Backpack Tools for Team Fortress 2</title>
	<link href="stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/metal_style.css" media="screen" rel="stylesheet" type="text/css" />
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
          <td><a href="bbcode_lookup.php">BBCode Translator</a></td>
          <td id="active_tool_cell"><a href="metal_lookup.php">Metal Maker</a></td>
        </tr>
      </table>
    </div>
  </div>
  
  <div id="content">
   
    <div id="metal_output">

    </div>

    
  </div>
  
  <?php
  require('footer.php');
  ?>
<?php require("google_analytics.php") ?>
</body>
</html>


