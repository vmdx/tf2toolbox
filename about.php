<?php session_start() ?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TF2Toolbox: Backpack Tools for Team Fortress 2</title>
	<link href="stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/mainpage_style.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="stylesheets/about_style.css" media="screen" rel="stylesheet" type="text/css" />
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
    
    <span class="about_title">About</span>
    
    <p>
      TF2Toolbox is a project with two main purposes. It is the primary vehicle by which I am developing my web design skills; I've worked hard
      to try and manage the entire website from the bottom up, learning lots about all aspects of how a website comes to be. From tinkering with
      nginx server settings, to deploying a suitable development environment for myself, from simmering up loads of CSS spaghetti to pair with my
      (hopefully) more orderly HTML, to playing in the playground of PHP, it has been and should continue to be an enlightening experience for me.
    </p>
    
    <p>
      It's purpose is also, of course, to give back to the TF2 community I have enjoyed so thoroughly over the last couple of years. I admit that
      I am probably just as concerned about the contents of my backpack as many other players, and all of the tools I've worked on so far simply
      arose from my own attempts to search for these tools online. When I couldn't find them, I realized making them was not impossible; it just
      simply hadn't been done yet. Hopefully some of you will find these tools useful, if so, I am glad. Thanks for stopping by!
      
    </p>
    
    <p>
    - VMDX
    </p>
    
  </div>
  
  <?php
  require('footer.php');
  ?>
  
<?php require("google_analytics.php") ?></body>
</html>