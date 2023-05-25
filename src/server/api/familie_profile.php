<?php

function api_getfampicture() {
  header( 'Content-Type: image/png' );
  $loc = array_key_exists( 'image', $_GET ) ? STATIC_DIR . strval( $_GET['image'] ) : null;

  if (is_null( $loc )) {
    http_response_code( 400 );
  } else if (strpos( $_GET['image'], '/' ) !== false)  {
    http_response_code( 400 );
  } else if (!file_exists( $loc )) {
    http_response_code( 404 );
    echo require('../../files/placeholder.png');
  } else {
    readfile( $loc );
  }
}

?>
