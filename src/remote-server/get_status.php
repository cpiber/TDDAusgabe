<?php

/** @param PDO $conn */
function get_status($conn) {
  if ( !array_key_exists( 'sync', $_GET ) || !is_numeric( $_GET['sync'] ) ) throw new InvalidArgumentException( "Sync is required" );

  $conn->exec( "LOCK TABLES `familien` READ, `orte` READ" );
  $syncData = array();

  $last_sync = floatval( $_GET['sync'] );
  $recordsFam = "SELECT COUNT(*) FROM `familien` WHERE `last_update` > $last_sync OR `last_update` = '0000-00-00'";
  $recordsOrte = "SELECT COUNT(*) FROM `orte` WHERE `last_update` > $last_sync OR `last_update` = '0000-00-00'";
  if ( $last_sync === 0 ) {
    $recordsFam = "SELECT COUNT(*) FROM `familien`";
    $recordsOrte = "SELECT COUNT(*) FROM `orte`";
  }

  $stmt = $conn->prepare( $recordsFam );
  $stmt->execute();
  $syncData['familien'] = $stmt->fetchColumn();

  $stmt = $conn->prepare( $recordsOrte );
  $stmt->execute();
  $syncData['orte'] = $stmt->fetchColumn();

  $conn->exec( "UNLOCK TABLES" );

  $syncData['static'] = count( getStaticFiles( STATIC_DIR ) );

  $syncData['status'] = 'success';
  echo json_encode( $syncData );
}
