<?php 
session_start();
  
/* Login form if necessary. */
$url_field = $_POST['steamURL'];
if (isset($url_field)) {
  $steamID_url = $url_field.'?xml=1';

  // Figure out the SteamID from the XML
  error_reporting(0); // Suppress errors for XML loading.
  $xml = simplexml_load_file($steamID_url);
  error_reporting(E_ALL ^ E_NOTICE); // Reset errors
  $error_msg = '';
  $error = 0;

  if (!$xml) {        // Error upon invalid URL.
   $error_msg = "We were unable to retrieve that URL. Please try again!\n";
  }
  
  
  $avatar = $xml->avatarMedium;
  $username = $xml->steamID;
  $sID = $xml->steamID64;
  
  $_SESSION['steamID'] = sprintf($sID);
  $_SESSION['username'] = sprintf($username); /* Awful hack to fix some weird deal with simpleXML */
  $_SESSION['avatar'] = sprintf($avatar);

  if ($xml && (empty($steamID) || empty($username))) {
    $error_msg = "We were unable to retrieve the SteamID from that URL. Please try again!\n";
  }

}

?>
    <div id="header_banner">
      <a href="index.php"><img id="banner_img" src="media/engie_toolbox_50.png" alt="TF2 Toolbox"></a>
      <img id="banner_button" src="media/indev.png" alt="indev">
      <a href="index.php"><span id="banner_text">TF2Toolbox.com</span></a>
    </div>
    <div id="current_user">
<?php
  if (empty($_SESSION['steamID'])) {
    echo 'Not currently signed in.
      <img id="avatar" src="media/blank.png" alt="avatar">
    ';
  }
  else {
    echo '<a href="http://steamcommunity.com/profiles/'.strval($_SESSION['steamID']).'">'.$_SESSION['username'].'</a>';
    echo '
      <a href="http://steamcommunity.com/profiles/'.strval($_SESSION['steamID']).'"><img id="avatar" src="'.$_SESSION['avatar'].'" alt="avatar"></a>
    ';
  }
?>
      <div id="signin_forms">
        <?php
        /* We want to return to the same page the user is on upon new login. */
        $currentFile = basename($_SERVER["SCRIPT_NAME"]);
        if ($currentFile == "bbcode_result.php") {
          $currentFile = "bbcode_lookup.php";
        }
        if ($currentFile == "metal_result.php") {
          $currentFile = "metal_lookup.php";
        }
        
        echo '
        <form id="signin_form" action="'.$currentFile.'" method="post">
        Lookup a Steam Community URL:
        <input type="text" id="signin_input" name="steamURL" size="36" value="http://steamcommunity.com/id/" />
        </form>
        ';
        ?>
      </div>
    </div>
