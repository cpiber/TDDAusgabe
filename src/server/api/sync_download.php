<?php

function api_sync_download($msg) {
  global $conn;
  try { 
    if ( !array_key_exists( 'file', $_REQUEST ) ) throw new InvalidArgumentException( "File is required" );
    $file = $_REQUEST['file'];
    if ( strpos( $file, "/" ) !== false || strpos( $file, "\\" ) !== false ) throw new InvalidArgumentException( "Invalid file" );
    if ( !file_exists( STATIC_DIR ) ) mkdir( STATIC_DIR, 0755 );

    $server = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncServer'" )->fetchColumn();
    $key = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncKey'" )->fetchColumn();
    $serverdata = server_send( $key, $server, "static&file=$file", HTTP_GET, "", "", 100, false );
    $f = @fopen( STATIC_DIR . $file, 'wb' );
    if ( $f === false ) throw new UnexpectedValueException( "Could not open file $file" );
    if ( fwrite( $f, $serverdata ) === false ) throw new UnexpectedValueException( "Could not write file $file" );
    if ( fclose( $f ) === false ) throw new UnexpectedValueException( "Could not close file $file" );

    $msg['status'] = 'success';
  } catch ( HTTPException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
    $msg['server'] = $e->serverdata;
  } catch ( Exception $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }

  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}
