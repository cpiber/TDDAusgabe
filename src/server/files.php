<?php


if ( array_key_exists( "file", $_GET ) ) {
  switch ( $_GET["file"] ) {
    case "js":
      header( 'Content-type: application/javascript' );
      echo require('../../build/tmp/client.js');
      break;

    case "css":
      header( 'Content-type: text/css' );
      echo require('../../build/tmp/client.css');
      break;

    case "cardjs":
      header( 'Content-type: application/javascript' );
      echo require('../../build/tmp/card.js');
      break;

    case "favicon":
      header( 'Content-type: image/vnd.microsoft.icon' );
      echo require('../files/favicon.ico');
      break;

    case "logo":
      header( 'Content-type: image/png' );
      echo require('../files/logo.png');
      break;

    case "placeholder":
      header( 'Content-type: image/png' );
      echo require('../files/placeholder.png');
      break;
    
    default:
      http_response_code( 404 );
      break;
  }
  exit;
}


?>