<?php

function put_static() {
  if ( !array_key_exists( 'file', $_REQUEST ) ) throw new InvalidArgumentException( "File is required" );
  $file = $_REQUEST['file'];
  if ( strpos( $file, "/" ) !== false || strpos( $file, "\\" ) !== false ) throw new InvalidArgumentException( "Invalid file" );

  $body = file_get_contents( 'php://input' );
  if ( !file_exists( STATIC_DIR ) ) mkdir( STATIC_DIR, 0755 );
  $f = @fopen( STATIC_DIR . $file, 'wb' );
  if ( $f === false ) throw new UnexpectedValueException( "Could not open file $file" );
  if ( fwrite( $f, $body ) === false ) throw new UnexpectedValueException( "Could not write file $file" );
  if ( fclose( $f ) === false ) throw new UnexpectedValueException( "Could not close file $file" );

  echo json_encode( array( 'status' => 'success' ) );
}
