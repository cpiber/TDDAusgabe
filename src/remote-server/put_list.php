<?php

/** @param PDO $conn */
function put_list($conn) {
  global $ortefields, $famfields;

  $body = file_get_contents( 'php://input' );
  $body = json_decode( $body, true );
  if ( is_null( $body ) || !is_array( $body ) ) throw new InvalidArgumentException( "Invalid JSON body" );
  // validate schema
  if ( !array_key_exists( 'sync', $body ) || !array_key_exists( 'familien', $body ) || !array_key_exists( 'orte', $body ) || !array_key_exists( 'static', $body ) ) throw new InvalidArgumentException( "Missing required field" );
  if ( !is_numeric( $body['sync'] ) ) throw new InvalidArgumentException( "Sync is not a number" );
  if ( !is_array( $body['familien'] ) ) throw new InvalidArgumentException( "Familien is not an array" );
  if ( !is_array( $body['orte'] ) ) throw new InvalidArgumentException( "Orte is not an array" );
  if ( !is_array( $body['static'] ) ) throw new InvalidArgumentException( "Static is not an array" );
  // TODO further validation

  $conn->exec( "LOCK TABLES `familien` WRITE, `orte` WRITE" );
  $conn->beginTransaction();
  $syncData = array();

  $last_sync = floatval( $body['sync'] );
  $recordsFam = "SELECT * FROM `familien` WHERE `last_update` > $last_sync";
  $recordsOrte = "SELECT * FROM `orte` WHERE `last_update` > $last_sync";
  if ( $last_sync === 0 ) {
    $recordsFam = "SELECT * FROM `familien`";
    $recordsOrte = "SELECT * FROM `orte`";
  }

  $stmt = $conn->prepare( $recordsFam );
  $stmt->setFetchMode( PDO::FETCH_ASSOC );
  $stmt->execute();
  $syncData['familien'] = $stmt->fetchAll();

  $stmt = $conn->prepare( $recordsOrte );
  $stmt->setFetchMode( PDO::FETCH_ASSOC );
  $stmt->execute();
  $syncData['orte'] = $stmt->fetchAll();

  $famIds = array_map( function ($v) { return $v['ID']; }, $body['familien'] );
  $orteIds = array_map( function ($v) { return $v['ID']; }, $body['orte'] );
  $localFamIds = array_map( function ($v) { return $v['ID']; }, $syncData['familien'] );
  $localOrteIds = array_map( function ($v) { return $v['ID']; }, $syncData['orte'] );

  $bothModFam = array_intersect( $famIds, $localFamIds );
  $bothModOrte = array_intersect( $orteIds, $localOrteIds );
  if ( count( $bothModFam ) > 0 || count( $bothModOrte ) > 0 ) throw new ConflictException( $bothModFam, $bothModOrte );

  if ( count( $body['familien'] ) > 0 ) {
    $sql = buildInsert( "familien", $famfields, $body['familien'], $data );
    $stmt = $conn->prepare( $sql );
    $stmt->execute( $data );
  }
  if ( count( $body['orte'] ) > 0 ) {
    $sql = buildInsert( "orte", $ortefields, $body['orte'], $data );
    $stmt = $conn->prepare( $sql );
    $stmt->execute( $data );
  }
  
  $syncData['sync'] = $conn->query( "SELECT NOW()+0" )->fetchColumn();
  $conn->commit();
  $conn->exec( "UNLOCK TABLES" );

  $files = getStaticFiles( STATIC_DIR );
  $filesBoth = array_intersect( $files, $body['static'] );
  $filesRequest = array_diff( $body['static'], $filesBoth );
  $filesDownload = array_diff( $files, $filesBoth );
  $syncData['static_upload'] = array_values( $filesRequest );
  $syncData['static_download'] = array_values( $filesDownload );

  $syncData['status'] = 'success';
  echo json_encode( $syncData );
}
