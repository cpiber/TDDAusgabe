<?php

function api_newfam($msg) {
  global $conn;
  global $fam_data;

  $data = array();
  $insert = fields( $fam_data, $data );

  try {
    $sql = "INSERT INTO `familien` $insert";
    
    if ( !empty( $data ) ) {
      $stmt = $conn->prepare( $sql );
      $stmt->execute( $data );
      $id = $conn->lastInsertId();
    }

    $get = array();
    if ( ( array_key_exists( 'Gruppe', $data ) && $data['Gruppe'] == 0 ) || !array_key_exists( 'Gruppe', $data ) ) {
      $get[] = 'Gruppe';
    }
    if ( ( array_key_exists( 'Num', $data ) && $data['Num'] == 0 ) || !array_key_exists( 'Num', $data ) ) {
      $get[] = 'Num';
    }
    if ( !empty( $get ) ) {
      $sql2 = sprintf( "SELECT %s FROM `familien` WHERE ID = :ID", implode( ", ", $get ) );
      $stmt = $conn->prepare( $sql2 );
      $stmt->setFetchMode( PDO::FETCH_ASSOC );
      $stmt->execute( array( ':ID' => $id ) );
      $msg['new'] = $stmt->fetch();
    }
    $msg['new']['ID'] = $id;

    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  if ( DEBUG ) $msg['_data'] = $data;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>