<?php session_start() ?>
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
<?php
  if ($error_msg != '') {
    echo '  <div id="error_bar">'."\n";
    echo '    <span id="error_msg">'.$error_msg."</span>\n";
    echo "  </div>\n";
  }
?>  
  <div id="content">
    <form id="lookup_field_form" action="metal_result.php" method="post">
      <div id="tool_title">
        <img id="tool_img" src="media/metal_tool_80.png" alt="metal_tool">
        <div id="tool_text">
          <span id="tool_name">Metal Maker</span><br/>
          <span id="tool_desc">Organizes backpack weapons into scrap metal and token combinations</span>         
        </div>

      </div>
      <div id="checkboxes">

        <!-- <div class="checkbox_column checkbox_column_extra_wide">
          <span class="checkbox_title">Options:</span>
          <input type="checkbox" name="weapon_use[]" value="smart" checked>Keep all named, described, and off-level weapons<br />
          <input type="checkbox" name="weapon_use[]" value="all" checked>Keep all vintage weapons<br />
          <input type="checkbox" name="weapon_use[]" value="smart" checked>Keep at least one of each weapon<br />
          <br />
        </div> -->
        
        <div class="checkbox_column">
          <span class="checkbox_title">Pages to Search:</span>
          <input type="checkbox" name="pages[]" value="all" checked>All Backpack Pages<br />
          <input type="checkbox" name="pages[]" value="1">1<br />
          <input type="checkbox" name="pages[]" value="2">2<br />
          <input type="checkbox" name="pages[]" value="3">3<br />
        </div>
        
        <div class="checkbox_column">
          <span class="checkbox_title"></span>
          <input type="checkbox" name="pages[]" value="4">4<br />
          <input type="checkbox" name="pages[]" value="5">5<br />
          <input type="checkbox" name="pages[]" value="6">6<br />
        </div>
        
        <div class="checkbox_column checkbox_column_extra_wide">
          <span class="checkbox_title">Notes:</span>
          <ul>
            <li>Weapons will be split into <span class="special">special</span>, <span class="vintage">vintage</span>, and <span class="normal">normal</span> weapons.</li><br/>
            <li><span class="special">Special</span> weapons are ones with custom names, custom descriptions, or non-standard levels. These <u>are not</u> included in metal calculations.</li>
          </ul>
        </div>
        
      </div>
      
      <?php
      if(!empty($_SESSION['steamID'])) {
        echo '<input type="submit" id="lookup_submit" value="Go!"/>'; 
      }
      else {
        echo '<input type="submit" style="color:#bbbbbb; left:200px" id="lookup_submit" value="Please lookup an user first." disabled/>';
      }
      
      ?>
    </form>
  </div>
  
  <?php
  require('footer.php');
  ?>
  
<?php require("google_analytics.php") ?></body>
</html>