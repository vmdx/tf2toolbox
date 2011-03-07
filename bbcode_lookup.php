<?php session_start() ?>
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
    <form id="lookup_field_form" action="bbcode_result.php" method="post">
      <div id="tool_title">
        <img id="tool_img" src="media/bbcode_tool_80.png" alt="bbcode_tool">
        <div id="tool_text">
          <span id="tool_name">BBCode Translator</span><br/>
          <span id="tool_desc">Converts backpack contents to eye-catching BBCode for trading on forums</span>         
        </div>

      </div>
      <div id="checkboxes">
        <div class="checkbox_column">
          <span class="checkbox_title">Display:</span>
          <input type="checkbox" name="u_hats" value="True" checked>Unusual Hats<br />
          <input type="checkbox" name="v_hats" value="True" checked>Vintage Hats<br />
          <input type="checkbox" name="hats" value="True" checked>Normal Hats<br />
          <input type="checkbox" name="v_weps" value="True" checked>Vintage Weapons<br />
          <input type="checkbox" name="weps" value="True" checked>Normal Weapons<br />
        </div>
        <div class="checkbox_column">
          <span class="checkbox_title"></span>
          <input type="checkbox" name="tools" value="True" checked>Tools<br />
          <input type="checkbox" name="paints" value="True" checked>Paints<br />
          <input type="checkbox" name="crates" value="True" checked>Crates<br />
          <input type="checkbox" name="metal" value="True" checked>Metal<br />
        </div>

        <div class="checkbox_column checkbox_column_wide">
          <span class="checkbox_title">Options:</span>
          <input type="checkbox" name="dup_weps_only" value="True" />Only display duplicate weapons<br />
          <input type="checkbox" name="display_levels" value="True" disabled/><span style="color: #707070">Display item levels (in development)</span><br />
          <br />
          <!-- <span class="checkbox_title">Output:</span>
          <input type="radio" name="output" value="list">Bulleted List<br />
          <input type="radio" name="output" value="have_want">Have / Want<br /> -->
        </div>
        
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
        
      </div>
      
      <?php
      if(!empty($_SESSION['steamID'])) {
        echo '<input type="submit" id="lookup_submit" value="Go!" />'; 
      }
      else {
        echo '<input type="submit" style="color:#bbbbbb" id="lookup_submit" value="Please lookup an user first." disabled/>';
      }
      
      ?>
    </form>
  </div>
  
  <?php
  require('footer.php');
  ?>

<?php require("google_analytics.php") ?></body>
</html>