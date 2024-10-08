<?php

$last_sync = "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync')";

function api_sync($msg) {
  global $conn;
  global $last_sync;
  global $famfields, $ortefields;

  $synctime = floatval( $conn->query( $last_sync )->fetchColumn() );
  $recordsFam = "SELECT * FROM `familien` WHERE `deleted` = 1 OR `last_update` > $last_sync OR `last_update` = '0000-00-00'";
  $recordsOrte = "SELECT * FROM `orte` WHERE `deleted` = 1 OR `last_update` > $last_sync OR `last_update` = '0000-00-00'";
  if ( $synctime === 0 ) {
    $recordsFam = "SELECT * FROM `familien`";
    $recordsOrte = "SELECT * FROM `orte`";
  }

  try { 
    $conn->exec( "LOCK TABLES `familien` WRITE, `orte` WRITE, `einstellungen` WRITE" );
    $conn->exec( "SET time_zone = '+00:00'" );
    $conn->beginTransaction();
    $syncData = array();

    $server = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncServer'" )->fetchColumn();
    if ( !$server || ( substr( $server, 0, 7 ) !== "http://" && substr( $server, 0, 8 ) !== "https://" ) ) throw new Exception( "Not a valid server $server" );
    $key = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncKey'" )->fetchColumn();

    // TODO: batches?
    $stmt = $conn->query( $recordsFam );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $syncData['familien'] = $stmt->fetchAll();

    $stmt = $conn->query( $recordsOrte );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $syncData['orte'] = $stmt->fetchAll();

    $syncData['static'] = getStaticFiles( STATIC_DIR );

    $stmt = $conn->query( "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync_servertime')" );
    $syncData['sync'] = $stmt->fetchColumn();

    $serverdata = server_send( $key, $server, "list", HTTP_PUT, 'Content-Type: application/json', json_encode( $syncData ) );

    // update with remote values
    if ( count( $serverdata['familien'] ) > 0 ) {
      $sql = buildInsert( "familien", $famfields, $serverdata['familien'], $data );
      $stmt = $conn->prepare( $sql );
      $stmt->execute( $data );
    }
    if ( count( $serverdata['orte'] ) > 0 ) {
      $sql = buildInsert( "orte", $ortefields, $serverdata['orte'], $data );
      $stmt = $conn->prepare( $sql );
      $stmt->execute( $data );
    }
    $msg['numfam'] = count( $serverdata['familien'] );
    $msg['numorte'] = count( $serverdata['orte'] );

    // delete deleted values, both uploaded by us and downloaded by server
    $famIds = array_map( function ($v) { return $v['ID']; }, $syncData['familien'] );
    $orteIds = array_map( function ($v) { return $v['ID']; }, $syncData['orte'] );
    $famIds = array_merge( $famIds, array_map( function ($v) { return $v['ID']; }, $serverdata['familien'] ) );
    $orteIds = array_merge( $orteIds, array_map( function ($v) { return $v['ID']; }, $serverdata['orte'] ) );
    $famPlaceholder = substr( str_repeat( ",?", count( $famIds ) ), 1 );
    $ortePlaceholder = substr( str_repeat( ",?", count( $orteIds ) ), 1 );

    if ( count( $famIds ) > 0 ) {
      $stmt = $conn->prepare( "DELETE FROM `familien` WHERE `ID` IN ($famPlaceholder) AND `deleted` = 1" );
      $stmt->execute( $famIds );
    }
    if ( count( $orteIds ) > 0 ) {
      $stmt = $conn->prepare( "DELETE FROM `orte` WHERE `ID` IN ($ortePlaceholder) AND `deleted` = 1" );
      $stmt->execute( $orteIds );
    }

    $stmt = $conn->prepare( "UPDATE `einstellungen` SET `val` = ? WHERE `name` = 'last_sync_servertime'" );
    $stmt->execute( array( $serverdata['sync'] ) );
    $stmt = $conn->prepare( "UPDATE `einstellungen` SET `val` = NOW()+0 WHERE `name` = 'last_sync'" );
    $stmt->execute();
    $conn->commit();

    // figure out which files we need to download
    $files = getStaticFiles( STATIC_DIR );
    $filesDownload = queryMissingFiles( $conn, $files );
    $msg['numupload'] = count( $serverdata['static_upload'] );
    $msg['numdownload'] = count( $filesDownload );
    // NOTE: Workaround because for some reason all further requests fail, maybe Nginx problem?
    $msg['static_upload'] = $serverdata['static_upload'];
    $msg['static_download'] = $filesDownload;
    
    $conn->exec( "UNLOCK TABLES" );

    $msg['status'] = 'success';
  } catch ( Exception $e ) {
    if ( $conn->inTransaction() ) $conn->rollBack();
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
    if ( $e instanceof HTTPException ) $serverdata = $e->serverdata;
    if ( isset( $serverdata ) && isset( $serverdata['message'] ) ) $msg['message'] .= " ({$serverdata['message']})";
    else if ( isset( $response ) ) $msg['message'] .= " ($response)";
    if ( isset( $serverdata ) ) $msg['server'] = $serverdata;
  }
  $conn->exec( "UNLOCK TABLES" );

  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}
