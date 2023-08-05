<?php

require "../synchelpers.php";

$last_sync = "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync')";

function api_sync($msg) {
  global $conn;
  global $last_sync;
  global $famfields, $ortefields;

  $recordsFam = "SELECT * FROM `familien` WHERE `deleted` = 1 OR `last_update` > $last_sync";
  $recordsOrte = "SELECT * FROM `orte` WHERE `deleted` = 1 OR `last_update` > $last_sync";
  $synctime = floatval( $conn->query( $last_sync )->fetchColumn() );
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

    // TODO: batches?
    
    $stmt = $conn->prepare( $recordsFam );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute();
    $syncData['familien'] = $stmt->fetchAll();

    $stmt = $conn->prepare( $recordsOrte );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute();
    $syncData['orte'] = $stmt->fetchAll();

    $stmt = $conn->prepare( "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync_servertime')" );
    $stmt->execute();
    $syncData['sync'] = $stmt->fetchColumn();

    $context = stream_context_create( array(
      'http' => array(
        'method'  => 'PUT',
        'header'  => 'Content-Type: application/json',
        'content' => json_encode( $syncData ),
        'timeout' => 500,
        'ignore_errors' => true,
      ),
    ) );
    $response = @file_get_contents( $server . "?api=list", false, $context );
    $serverdata = json_decode( $response, true );

    $status = intval( explode( " ", $http_response_header[0] )[1] ); // HTTP/1.1 200 OK => 200
    if ( $status != 200 || !is_array( $serverdata ) ) throw new Exception( "Request failed: {$http_response_header[0]}" );

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

    $msg['status'] = 'success';
  } catch ( Exception $e ) {
    $conn->rollBack();
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
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
