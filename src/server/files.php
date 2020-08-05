<?php


if ( array_key_exists( "file", $_GET ) ) {
  switch ( $_GET["file"] ) {
    case "js":
      header( 'Content-type: application/javascript' );
      echo <<<'FILE_NOWDOC____'
require "../files/client.ts";
FILE_NOWDOC____;
      break;

    case "css":
      header( 'Content-type: text/css' );
      echo <<<'FILE_NOWDOC____'
require "../files/client.css";
FILE_NOWDOC____;
      break;

    case "favicon":
      header( 'Content-type: image/vnd.microsoft.icon' );
      $file = <<<'FILE_NOWDOC____'
require "../files/favicon.ico!base64";
FILE_NOWDOC____;
      echo base64_decode( $file );
      break;

    case "logo":
      header( 'Content-type: image/png' );
      $file = <<<'FILE_NOWDOC____'
require "../files/logo.png!base64";
FILE_NOWDOC____;
      echo base64_decode( $file );
      break;
    
    default:
      http_response_code( 404 );
      break;
  }
  exit;
}


?>