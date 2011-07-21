<?php

function output_section($data, $check, $header='') {
  if ($check and !empty($data)) {
    if ($header) {
      echo "[b][u]".$header."[/u][/b]\n";
    }
      
    foreach ( array_keys($data) as $item ) {
      
      echo "[*][b]";
      
      echo $item;
      
      if ($data[$item] > 1) {
        echo " x ".$data[$item];
      }
      
      echo "[/b]\n";
    }
    echo "\n";
  }
}

function open_header($header, $credit, &$first_item) {
  echo "[b][u]".$header."[/u][/b]";
  if ($first_item) {
    $first_item = false;
    echo $credit;
  }
  echo "[list]\n";
}

function close_header() {
  echo "[list]\n";
}
?> 