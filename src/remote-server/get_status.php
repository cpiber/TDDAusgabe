<?php

/** @param PDO $conn */
function get_status($conn) {
  if ( !array_key_exists( 'sync', $_GET ) || !is_numeric( $_GET['sync'] ) ) throw new InvalidArgumentException( "Sync is required" );

  $conn->exec( "LOCK TABLES `familien` READ, `orte` READ" );
  $conn->exec( "SET time_zone = '+00:00'" );
  $syncData = array();

  $last_sync = floatval( $_GET['sync'] );
  $recordsFam = "SELECT COUNT(*) FROM `familien` WHERE `last_update` >= $last_sync";
  $recordsOrte = "SELECT COUNT(*) FROM `orte` WHERE `last_update` >= $last_sync";

  $stmt = $conn->prepare( $recordsFam );
  $stmt->execute();
  $syncData['familien'] = $stmt->fetchColumn();

  $stmt = $conn->prepare( $recordsOrte );
  $stmt->execute();
  $syncData['orte'] = $stmt->fetchColumn();

  $conn->exec( "UNLOCK TABLES" );

  $syncData['status'] = 'success';
  echo json_encode( $syncData );
}
