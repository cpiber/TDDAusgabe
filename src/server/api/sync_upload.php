<?php

function api_sync_upload($msg) {
  global $conn;
  try { 
    if ( !array_key_exists( 'file', $_REQUEST ) ) throw new InvalidArgumentException( "File is required" );
    $file = $_REQUEST['file'];
    if ( strpos( $file, "/" ) !== false || strpos( $file, "\\" ) !== false ) throw new InvalidArgumentException( "Invalid file" );
    if ( !file_exists( STATIC_DIR . $file ) ) throw new InvalidArgumentException( "Invalid file" );
    if ( !file_exists( STATIC_DIR ) ) mkdir( STATIC_DIR, 0755 );

    $server = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncServer'" )->fetchColumn();
    $key = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncKey'" )->fetchColumn();
    upload_file( $key, $server, "static&file=$file", STATIC_DIR . $file );

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
