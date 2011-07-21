<?php session_start();

require_once('php/backpack_lookup_functions.php');
require_once('php/backpack_data.php');

// Retrieve POST and SESSION variables.
$valid_pages = $_POST['pages'];
//$valid_pages = array("all");

$steamID = $_SESSION['steamID'];
//$steamID = 76561197961814215;
$avatar = $_SESSION['avatar'];
$username = $_SESSION['username'];

// Call Steam API
$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$steamID."&key=74EA34072E00ED29B92691B6F929F590";
/* Evil Mav's backpack */
//$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=76561197961814215&key=74EA34072E00ED29B92691B6F929F590";
/* VMDX's backpack */
//$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=76561197998913767&key=74EA34072E00ED29B92691B6F929F590";
/* Fireblade's backpack */
//$backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=76561197986187065&key=74EA34072E00ED29B92691B6F929F590";


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
$craft_classes = array();
$used_by_classes = array();
$image_urls = array();

foreach ( $schema->{"result"}->{"items"}->{"item"} as $entry ) {
  $item_names[$entry->{"defindex"}] = $entry->{"item_name"};
  $item_slots[$entry->{"defindex"}] = $entry->{"item_slot"};
  $item_classes[$entry->{"defindex"}] = $entry->{"item_class"};
  $craft_classes[$entry->{"defindex"}] = $entry->{"craft_class"};
  $used_by_classes[$entry->{"defindex"}] = $entry->{"used_by_classes"}->{"class"};
  $image_urls[$entry->{"defindex"}] = $entry->{"image_url"};
}

/* TF2 Schema specific setup. Dictionaries, definition maps, etc. */
$vintage_quality = $schema->{"result"}->{"qualities"}->{"vintage"};
$normal_quality = $schema->{"result"}->{"qualities"}->{"unique"};
$unusual_quality = $schema->{"result"}->{"qualities"}->{"rarity4"};
$genuine_quality = $schema->{"result"}->{"qualities"}->{"rarity1"};
$community_quality = $schema->{"result"}->{"qualities"}->{"community"};
$selfmade_quality = $schema->{"result"}->{"qualities"}->{"selfmade"};
$valve_quality = $schema->{"result"}->{"qualities"}->{"developer"};

/* Inventory setup: special weapons contain all off-level, named, and described weapons */
$all_weapons = array(
  "Scout" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Soldier" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Pyro" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Demoman" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Heavy" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Engineer" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Medic" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Sniper" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "Spy" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array()),
  "MultiClass" => array("SPECIAL_WEAPONS" => array(), "VINTAGE_WEAPONS" => array(), "WEAPONS" => array())
);

$tokens = array();

$metals = array();

$weapon_slots = array("primary", "secondary", "melee", "pda", "pda2");

$mask = 0xFFFF;   // Get the first word in the inventory token -> corresponds to inventory slot.

/* Keep track of all seen weapons. */
$seen_weapons = array(
  "Scout" => array(),
  "Soldier" => array(),
  "Pyro" => array(),
  "Demoman" => array(),
  "Heavy" => array(),
  "Engineer" => array(),
  "Medic" => array(),
  "Sniper" => array(),
  "Spy" => array(),
  "MultiClass" => array()
);

/* Keep track of image - display name map */
$weapon_to_image_map = array();

/* Keep track of primary and secondary weps. */
$v_pwep_count = 0;
$nv_pwep_count = 0;
$v_swep_count = 0;
$nv_swep_count = 0;
$unique_primaries = array();
$unique_secondaries = array();

$backpack_error = "";
if($backpack->{"result"}->{"status"} == 15) {
  $backpack_error = "Sorry, this backpack is private. :(";
}

else if(isset($steamID)) {
  foreach ( $backpack->{"result"}->{"items"}->{"item"} as $inv_entry ) {
    $my_defindex = $inv_entry->{"defindex"};
    $my_quality = $inv_entry->{"quality"};

    $my_item_name = $item_names[$my_defindex];
    $my_item_slot = $item_slots[$my_defindex];
    $my_item_class = $item_classes[$my_defindex];
    $my_craft_class = $craft_classes[$my_defindex];
    $my_image = $image_urls[$my_defindex];
    
    
    $inventory_token = $inv_entry->{"inventory"};
    $my_inventory_slot = (int)$inventory_token & $mask;
    $my_inventory_page = floor(($my_inventory_slot - 1) / 50) + 1;
    
    /* Skip invalid pages */
    if (!in_array("all", $valid_pages) and !in_array($my_inventory_page, $valid_pages)) {
      continue;
    }
    
    /* Weapons - don't show custom-named stock weapons (defindex 0 through 30, 190 through 212) */
    //if ($my_craft_class == "weapon" and $my_defindex > 30 and ($my_defindex < 190 or $my_defindex > 212)) {
    if (in_array($my_item_slot, $weapon_slots) and $my_item_class != "slot_token") {
      /* Fill the image map. */
      if (!isset($weapon_to_image_map[$my_item_name])) {
        $weapon_to_image_map[$my_item_name] = $my_image;
      }
      
      /* Check for special weapons (named, descd, offlevel) */
      $special = false;
      $special_info = "";
      
      /* Primary + secondary weapon checking */
      if ($my_item_slot == "primary") {
        if ($my_quality == $vintage_quality) {
          $v_pwep_count += 1;
        }
        else if ($my_quality == $normal_quality) {
          $nv_pwep_count += 1;
        }
        if (!in_array($my_item_name, $unique_primaries)) {
          array_push($unique_primaries, $my_item_name);
        }
      }
      else if ($my_item_slot == "secondary") {
        if ($my_quality == $vintage_quality) {
          $v_swep_count += 1;
        }
        else if ($my_quality == $normal_quality) {
          $nv_swep_count += 1;
        }
        if (!in_array($my_item_name, $unique_secondaries)) {
          array_push($unique_secondaries, $my_item_name);
        }
      }
      
      
      /* Array choice: which array in weapons to go to, SPECIAL_WEAPONS, VINTAGE_WEAPONS, or WEAPONS
         Display name: the display name of the weapon: Vintage if needed, custom if needed. */
      if ($my_quality == $unusual_quality || $my_quality == $genuine_quality || $my_quality == $community_quality || $my_quality == $valve_quality ||
        $my_quality == $selfmade_quality) {
        $array_choice = "SPECIAL_WEAPONS";
        $special = true;
        
        if ($my_quality == $unusual_quality) {
          $display_name = "Unusual ".$my_item_name;
        }
        else if ($my_quality == $genuine_quality) {
          $display_name = "Genuine ".$my_item_name;
        }
        else if ($my_quality == $valve_quality) {
          $display_name = "Valve ".$my_item_name;
        }
        else if ($my_quality == $community_quality) {
          $display_name = "Community ".$my_item_name;
        }
        else if ($my_quality == $selfmade_quality) {
          $display_name = "Self-made ".$my_item_name;
        }
        
      }
      else if ($my_quality == $vintage_quality) {
        $array_choice = "VINTAGE_WEAPONS";
        $display_name = "Vintage ".$my_item_name;
      }
      else {
        $array_choice = "WEAPONS";
        $display_name = $my_item_name;
      }
      
      /* Custom name + desc check */
      if ($inv_entry->{"custom_name"}) {
        $special_info = $display_name;
        $display_name = $inv_entry->{"custom_name"};
        $special = true;
      }
      
      if ($inv_entry->{"custom_desc"}) {
        $custom_desc = $inv_entry->{"custom_desc"};
        $special = true;
        if ($special_info != "") {
          $special_info = $special_info.", ";
        }
        $special_info = $special_info."Description: '".$custom_desc."'";
      }
      else {
        $custom_desc = "";
      }
      
      /* Level -1 if standard, offlevel if offlevel. */
      if (!$WEAPON_LEVEL_MAP[$inv_entry->{"level"}] or !in_array($my_item_name, $WEAPON_LEVEL_MAP[$inv_entry->{"level"}])) {
        $level = $inv_entry->{"level"};
        $special = true;
        if ($special_info != "") {
          $special_info = $special_info.", ";
        }
        $special_info = $special_info."Level ".$level;
      }
      else {
        $level = -1;
      }
      
      /* Add the special info to the display name. */
      if ($special) {
        $array_choice = "SPECIAL_WEAPONS";
        $display_name = $display_name." (".$special_info.")";
      }
      
      /* Only look up use class for weapons. */
      if(count($used_by_classes[$my_defindex]) > 1) {
        $my_use_class = "MultiClass";
      }
      else if (count($used_by_classes[$my_defindex]) == 1) {
        $my_use_class = $used_by_classes[$my_defindex][0];
      }
      
      /* Mark that we've seen the weapon. */
      if (!in_array($my_item_name, $seen_weapons[$my_use_class])) {
        array_push($seen_weapons[$my_use_class], $my_item_name);
      }
       
      /* Put stuff into our all_weapons array. */
      if ($array_choice == "SPECIAL_WEAPONS") {
        /* Create the weapon entry. */
        $entry = array(
          "display_name" => $display_name,
          "image" => $my_image,
          "slot" => $my_item_slot,
          "level" => $level,
          "custom_desc" => $custom_desc
        );
        array_push($all_weapons[$my_use_class][$array_choice], $entry);
      }
      else {
        $all_weapons[$my_use_class][$array_choice] = set_item_in_array($all_weapons[$my_use_class][$array_choice], $my_item_name);
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
	<link href="stylesheets/metal_output.css" media="screen" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="media/favicon.ico" />
	<link rel="icon" type="image/png" href="media/engie_toolbox_32.png" />
</head>
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
          <td><a href="bbcode_lookup.php">BBCode Translator</a></td>
          <td id="active_tool_cell"><a href="metal_lookup.php">Metal Maker</a></td>
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
   
    <div id="metal_output">
      
      <div id="weapon_content">
        <div id="class_avatars">
          <span class="class_avatar" style="background-image:url('media/class_avatars/scout.png');">Scout</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/soldier.png');">Soldier</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/pyro.png');">Pyro</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/demoman.png');">Demoman</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/heavy.png');">Heavy</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/engineer.png');">Engineer</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/medic.png');">Medic</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/sniper.png');">Sniper</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/spy.png');">Spy</span>
          <span class="class_avatar" style="background-image:url('media/class_avatars/multiclass.png');">Multiple</span>     
        </div>

        <div class="cross_column_title">
          <span class="vintage">Vintage</span><span style="color: #dddddd;"> & </span><span class="normal">Normal</span><span style="color: #dddddd;"> Weapons</span>
        </div>
      
<?php
  $all_classes = array("Scout", "Soldier", "Pyro", "Demoman", "Heavy", "Engineer", "Medic", "Sniper", "Spy", "MultiClass");
  foreach ($all_classes as $class) {
    echo "        <div class=\"class_column\">\n";
    
    foreach ($seen_weapons[$class] as $seen_weapon) {
      if (!isset($all_weapons[$class]["VINTAGE_WEAPONS"][$seen_weapon]) and !isset($all_weapons[$class]["WEAPONS"][$seen_weapon])) {
        continue;
      }
      echo "          <div class=\"weapon_entry\">\n";
      echo "            <img class=\"weapon_image\" src=\"".$weapon_to_image_map[$seen_weapon]."\" alt=\"".$seen_weapon."\">\n";
      
      if (isset($all_weapons[$class]["VINTAGE_WEAPONS"][$seen_weapon]) and isset($all_weapons[$class]["WEAPONS"][$seen_weapon])) {
        echo "            <span class=\"quantity q_double q_x\">x</span><span class=\"quantity q_double q_num vintage\">".$all_weapons[$class]["VINTAGE_WEAPONS"][$seen_weapon]."</span>\n";
        echo "            <span class=\"quantity q_double q_x\">x</span><span class=\"quantity q_double q_num normal\">".$all_weapons[$class]["WEAPONS"][$seen_weapon]."</span>\n";
      }
      
      else if (isset($all_weapons[$class]["VINTAGE_WEAPONS"][$seen_weapon])) {
                echo "            <span class=\"quantity q_single q_x\">x</span><span class=\"quantity q_single q_num vintage\">".$all_weapons[$class]["VINTAGE_WEAPONS"][$seen_weapon]."</span>\n";
      }
      
      else if (isset($all_weapons[$class]["WEAPONS"][$seen_weapon])) {
        echo "            <span class=\"quantity q_single q_x\">x</span><span class=\"quantity q_single q_num normal\">".$all_weapons[$class]["WEAPONS"][$seen_weapon]."</span>\n";        
      }
      
      echo "          </div>\n";
    }
    
    if (empty($seen_weapons[$class])) {
      echo "          <br/>";
    }
    echo "        </div>\n";
  }
?>

        <div class="clearbar"></div>
        <hr>
        
        <div id="calculations">
<?php

  $total_nv_sum = 0;
  $total_v_sum = 0;
  
  foreach ($all_classes as $class) {
    echo "          <div class=\"class_column center\">\n";
    
    $sum = 0;
    foreach ($all_weapons[$class]["WEAPONS"] as $num_weapons) {
      $sum += $num_weapons;
      $total_nv_sum += $num_weapons;
    }
    echo "            <span class=\"calc normal\">".$sum." NV</span><span class=\"calc white\"> = <br/><span class=\"calc white center_number\">".strval($sum/2)."</span><img class=\"scrap_metal_pic\" src=\"media/scrap.png\"</img>\n";
        
    echo "            <div class=\"clearbar calc_space\"></div>\n";
    
    $sum = 0;
    foreach ($all_weapons[$class]["VINTAGE_WEAPONS"] as $num_weapons) {
      $sum += $num_weapons;
      $total_v_sum += $num_weapons;
    }
    echo "            <span class=\"calc vintage\">".$sum." V</span><span class=\"calc white\"> = <br/><span class=\"calc white center_number\">".strval($sum/2)."</span><img class=\"scrap_metal_pic\" src=\"media/scrap.png\"</img>\n";
    
    echo "            <div class=\"clearbar calc_space\"></div>\n";
    echo "            <span class=\"calc_small unique\">".count($seen_weapons[$class])." Unique</span>";
    echo "<br/>\n";
    echo "          </div>\n";
  }
?>          
        </div>
        
        <div class="cross_column_title">
          <span class="special">Special Weapons</span>
        </div>

<?php
  foreach ($all_classes as $class) {
    echo "        <div class=\"class_column\">\n";
    foreach ($all_weapons[$class]["SPECIAL_WEAPONS"] as $special_weapon) {
      echo "          <img class=\"weapon_image\" src=\"".$special_weapon["image"]."\" alt=\"".$special_weapon["display_name"]."\">\n";
    }
    echo "<br/>\n";
    echo "        </div>\n";
  }
?>

      </div>
    
      <div id="statistics">
        <span class="stat_title">Numbers</span>
        <br/>
        <span class="stat plain subtitle">Total # of weapons:</span>
        <span class="stat white subsubtitle"><?php echo strval($total_v_sum+$total_nv_sum)." (".strval(($total_v_sum+$total_nv_sum)/2); ?> scrap)</span>
        <span class="stat normal">&nbsp&#8226 Non-vintage: <?php echo $total_nv_sum; ?></span>
        <span class="stat vintage">&nbsp&#8226 Vintage: <?php echo $total_v_sum; ?></span>
        <br/>
        <span class="stat plain subtitle"># of Primary Weapons:</span>
        <span class="stat white subsubtitle"><?php echo strval($nv_pwep_count+$v_pwep_count)." (".strval(($nv_pwep_count+$v_pwep_count)/3); ?> tokens)</span>
        <span class="stat normal">&nbsp&#8226 Non-vintage: <?php echo $nv_pwep_count; ?></span>
        <span class="stat vintage">&nbsp&#8226 Vintage: <?php echo $v_pwep_count; ?></span>
        <span class="stat unique">&nbsp&#8226 Unique Weapon Types: <?php echo count($unique_primaries); ?></span>
        <br/>
        <span class="stat plain subtitle"># of Secondary Weapons:</span>
        <span class="stat white subsubtitle"><?php echo strval($nv_swep_count+$v_swep_count)." (".strval(($nv_swep_count+$v_swep_count)/3); ?> tokens)</span>
        <span class="stat normal">&nbsp&#8226 Non-vintage: <?php echo $nv_swep_count; ?></span>
        <span class="stat vintage">&nbsp&#8226 Vintage: <?php echo $v_swep_count; ?></span>
        <span class="stat unique">&nbsp&#8226 Unique Weapon Types: <?php echo count($unique_secondaries); ?></span>
      
        <br/>
        <span class="stat_title">Notes</span>
        <ul id="stat_notes">
<?php
if (in_array("all", $valid_pages)) {
  echo "          <li>Displaying all backpack pages</li>\n";
}
else if (empty($valid_pages)) {
  echo "          <li>Not displaying any backpack pages. Huh?</li>\n";
}
else if (count($valid_pages) == 1) {
  echo "          <li>Displaying backpack page ".strval($valid_pages[0])."</li>\n";
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
          <br/>
          <li><span style="color:#70B04A;">Special weapons</span> are not considered in any calculations except for counting unique weapon types.</li>
        </ul>
      </div>
    
      <div class="clearbar"></div>
      
    </div>
    
  </div>
  
  <?php
  require('php/footer.php');
  ?>

<script type="text/javascript" src="javascript/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="javascript/jquery.qtip-1.0.0-rc3.js"></script>
<script>
$(document).ready(function() {

    /* Create tooltips for every single item on mouseover. */
    $(".weapon_image").each( function(i) {
      var data = $(this).attr('alt');
      if (data !== "") {
        $(this).qtip({
          content: data,
          position: { target: 'mouse'},
          show: { delay: 0, effect: {length: 0} },
          hide: { delay: 0, effect: {length: 0} },
          style: {
            textAlign: 'center',
            name: 'dark',
            'font-size': 14,
            'font-family': 'Verdana'
          }
        });
      }
    }); 
})
</script>

<?php require("php/google_analytics.php") ?></body>
</html>


