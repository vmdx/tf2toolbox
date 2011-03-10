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

<?php
  if ($error_msg != '') {
    echo '  <div id="error_bar">'."\n";
    echo '    <span id="error_msg">'.$error_msg."</span>\n";
    echo "  </div>\n";
  }
?>
  
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
          <input type="checkbox" name="u_hats" value="True" checked>Hats - Unusual<br />
          <input type="checkbox" name="hp_hats" value="True" checked>Hats - Rare Promos  <img id="rare_promos_tooltip" class="info_tooltip" src="media/info_tooltip.png"><br />
          <input type="checkbox" name="v_hats" value="True" checked>Hats - Vintage<br />
          <input type="checkbox" name="p_hats" value="True" checked>Hats - Promo<br />
          <input type="checkbox" name="hats" value="True" checked>Hats - Normal<br />
        </div>
        <div class="checkbox_column">
          <span class="checkbox_title"></span>
          <input type="checkbox" name="v_weps" value="True" checked>Weapons - Vintage<br />
          <input type="checkbox" name="p_weps" value="True" checked>Weapons - Promo<br />
          <input type="checkbox" name="weps" value="True" checked>Weapons - Normal<br />
        </div>
        <div class="checkbox_column checkbox_column_bigright">
          <span class="checkbox_title"></span>
          <input type="checkbox" name="tools" value="True" checked>Tools<br />
          <input type="checkbox" name="paints" value="True" checked>Paints<br />
          <input type="checkbox" name="crates" value="True" checked>Crates<br />
          <input type="checkbox" name="metal" value="True" checked>Metal<br />
        </div>

        <div class="checkbox_column checkbox_column_bigright">
          <span class="checkbox_title">Options:</span>
          <input type="checkbox" name="dup_weps_only" value="True" disabled/><span style="color: #707070">Only display duplicate weapons (in dev!)  </span><img id="dup_weps_tooltip" class="info_tooltip" src="media/info_tooltip.png"><br />
          <input type="checkbox" name="display_hat_levels" value="True" />Display hat levels<br />
          <input type="checkbox" name="hide_untradable" value="True" />Exclude all untradable items (dirty)<br />
          <input type="checkbox" name="hide_gifted" value="True" />Exclude all gifted items (dirty)<br />
          <input type="checkbox" name="display_credit" value="True" checked/>Display TF2Toolbox credit - thanks! :)<br />
          <br />
        </div>
        <div class="checkbox_column checkbox_column_bigright">
          <span class="checkbox_title">Sort Items By:</span>
          <input type="radio" name="output_sort" value="alpha" checked>Alphabetical<br />
          <input type="radio" name="output_sort" value="class" disabled><span style="color: #707070">Class (in dev!)</span><br />
          <input type="radio" name="output_sort" value="item_slot" disabled><span style="color: #707070">Item Slot (in dev!)</span><br />
        </div>

          <!-- <span class="checkbox_title">Output:</span>
          <input type="radio" name="output" value="list">Bulleted List<br />
          <input type="radio" name="output" value="have_want">Have / Want<br /> -->
        
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
  
  <script type="text/javascript" src="javascript/jquery-1.4.4.min.js"></script>
  <script type="text/javascript" src="javascript/jquery.qtip-1.0.0-rc3.js"></script>
  <script>
  $(document).ready(function() {

      /* Create tooltips for every single item on mouseover. */
      $("#dup_weps_tooltip").each( function(i) {
        var data = "Keeps one of each unique weapon. Priority to off-level, then vintage, then normal quality weapons.";
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
      
      $("#rare_promos_tooltip").each( function(i) {
        var data = "Bill's Hat, Max's Severed Head, Hat of Undeniable Wealth And Respect, Earbuds, Voodoo Juju, Spine-Chilling Skull, Gentle Manne's Service Medal, Hero's Hachimaki";
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