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

  if ($xml && (empty($sID) || empty($username))) {
    $error_msg = "We were unable to retrieve the SteamID from that URL. Please try again!\n";
  }

  else {
    $user_backpack_url = "http://api.steampowered.com/ITFItems_440/GetPlayerItems/v0001/?SteamID=".$_SESSION['steamID']."&key=74EA34072E00ED29B92691B6F929F590";

    $user_backpack_json = file_get_contents($user_backpack_url);
    $user_backpack = json_decode($user_backpack_json);
    if($user_backpack->{"result"}->{"status"} == 15) {
      $error_msg = "Note: This backpack is private and cannot be looked up.";
    }
    else if ($user_backpack->{"result"}->{"status"} == 8) {
      $error_msg = "This backpack has an invalid Steam ID.";
    }
    else if ($user_backpack->{"result"}->{"status"} != 1) {
      $error_msg = "Unknown backpack error.";
    }
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
      <a href="http://steamcommunity.com/profiles/'.strval($_SESSION['steamID']).'" target="_blank"><img id="avatar" src="'.$_SESSION['avatar'].'" alt="avatar"></a>
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
