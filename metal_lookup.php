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
  <div id="content" class="ad_padding">
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
        
        <?php
          $user_backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$_SESSION['steamID']."&key=74EA34072E00ED29B92691B6F929F590";
          $user_backpack_json = file_get_contents($user_backpack_url);
          $user_backpack = json_decode($user_backpack_json);
          $num_backpack_slots = $user_backpack->{"result"}->{"num_backpack_slots"};
          if (isset($num_backpack_slots)) {
            echo '        <div class="checkbox_column">
        ';
            echo '          <span class="checkbox_title">Pages to Search:</span>
                  <input type="checkbox" name="pages[]" value="all" checked>All Backpack Pages <img id="pages_tooltip" class="info_tooltip" src="media/info_tooltip.png"><br />'
                  ;
            $pages = $num_backpack_slots/50;
            $current_page = 1;
            $current_page_in_col = 2;
            while($pages > 0) {
              echo '          <input type="checkbox" name="pages[]" value="'.$current_page.'">'.$current_page.'<br />
        ';
              $current_page_in_col += 1;
              if ($current_page_in_col == 5) { // 5 is number of boxes allowed plus 1.
                $current_page_in_col = 1;
                echo '        </div>
                <div class="checkbox_column">
                  <span class="checkbox_title"></span>
        ';
              }
              $pages -= 1;
              $current_page += 1;
            }
            echo '        </div>
            ';
          }
        ?>
        
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
        echo '<input type="submit" style="color:#bbbbbb; left:200px" id="lookup_submit" value="Please lookup a user first." disabled/>';
      }
      
      ?>
    </form>
  </div>
  
  <!-- <div id="adstrip">
    <div id="adbox" style="width:468px; margin:auto;">
      <script type="text/javascript"><!--
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
  require('footer.php');
  ?>
  
  
  <script type="text/javascript" src="javascript/jquery-1.4.4.min.js"></script>
  <script type="text/javascript" src="javascript/jquery.qtip-1.0.0-rc3.js"></script>
  <script>
  $(document).ready(function() {

      /* Create tooltips for every single item on mouseover. */
      $("#pages_tooltip").each( function(i) {
        var data = "Includes all pages from Backpack Expanders";
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
  
<?php require("google_analytics.php") ?></body>
</html>