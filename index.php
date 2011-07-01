<?php session_start() ?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TF2Toolbox: Backpack Tools for Team Fortress 2</title>
	<link href="stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/mainpage_style.css" media="screen" rel="stylesheet" type="text/css" />
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
          <td id="active_tool_cell"><a href="index.php">Home</a></td>
          <td><a href="bbcode_lookup.php">BBCode Translator</a></td>
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
    <div id="released_tools" class="toolbox">
      <span class="tools_title">Released Tools</span>
      <div class="tool">
        <a href="bbcode_lookup.php"><img class="tool_img" src="media/bbcode_tool_spotlight.png" alt="bbcode_spotlight"></a>
        <a href="bbcode_lookup.php"><span class="tool_title" id="bbcode_tool">BBCode Translator</span></a>
        <span class="tool_desc">Converts backpack contents to eye-catching BBCode for trading on forums</span>
        <!-- <span class="tool_news">updated 3.9.2011</span> -->
      </div>
      <div class="tool">
        <a href="metal_lookup.php"><img class="tool_img" src="media/metal_tool_spotlight.png" alt="bbcode_spotlight"></a>
        <a href="metal_lookup.php"><span class="tool_title" id="metal_tool">Metal Maker</span></a>
        <span class="tool_desc">Organizes backpack weapons into scrap metal and token combinations</span>
        <!-- <span class="tool_news">updated 3.7.2011</span> -->
      </div>

    </div>
    
    <div id="indev_tools" class="toolbox">
      <span class="tools_title">Tools In Development</span>
      <div class="tool">
        <img class="tool_img" src="media/dictionary_tool_spotlight.png" alt="bbcode_spotlight">
        <span class="tool_title" id="dict_tool">Weapon Inventory</span>
        <span class="tool_desc">There are so many weapons nowadays! See which ones you are missing</span>
        <!-- <span class="tool_news">not started</span> -->
      </div>
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
  require('footer.php');
  ?>
  
<?php require("google_analytics.php") ?></body>
</html>