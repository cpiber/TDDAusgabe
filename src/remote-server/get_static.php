<?php

function get_static() {
  if ( !array_key_exists( 'file', $_GET ) ) throw new InvalidArgumentException( "File is required" );
  $file = $_GET['file'];
  if ( strpos( $file, "/" ) !== false || strpos( $file, "\\" ) !== false ) throw new InvalidArgumentException( "Invalid file" );
  if ( !file_exists( STATIC_DIR . $file ) )  throw new InvalidArgumentException( "Invalid file" );

  header( "Content-Type: application/octet-stream" );
  echo file_get_contents( STATIC_DIR . $file );
}
