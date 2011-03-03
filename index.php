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
  
  <div id="content">
    <div id="released_tools" class="toolbox">
      <span class="tools_title">Released Tools</span>
      <div class="tool">
        <a href="bbcode_lookup.php"><img class="tool_img" src="media/bbcode_tool_spotlight.png" alt="bbcode_spotlight"></a>
        <a href="bbcode_lookup.php"><span class="tool_title" id="bbcode_tool">BBCode Translator</span></a>
        <span class="tool_desc">Converts backpack contents to eye-catching BBCode for trading on forums</span>
        <span class="tool_news">v1.0 - updated 2.23.2011</span>
      </div>
      

    </div>
    
    <div id="indev_tools" class="toolbox">
      <span class="tools_title">Tools In Development</span>
      <div class="tool">
        <a href="metal_lookup.php"><img class="tool_img" src="media/metal_tool_spotlight.png" alt="bbcode_spotlight"></a>
        <a href="metal_lookup.php"><span class="tool_title" id="metal_tool">Metal Maker</span></a>
        <span class="tool_desc">Organizes backpack weapons into scrap metal and token combinations</span>
        <span class="tool_news">v0.1 - updated 2.23.2011</span>
      </div>
      <div class="tool">
        <img class="tool_img" src="media/dictionary_tool_spotlight.png" alt="bbcode_spotlight">
        <span class="tool_title" id="dict_tool">TF2 Item Dictionary</span>
        <span class="tool_desc">Fetches current TF2 item schema with pictures and descriptions</span>
        <span class="tool_news">not started</span>
      </div>
    </div>
    
  </div>
  
  <?php
  require('footer.php');
  ?>
  
<?php require("google_analytics.php") ?></body>
</html>