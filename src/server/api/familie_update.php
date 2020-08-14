<?php

function api_updatefam($msg) {
  global $conn;
  global $fam_data;

  $id = array_key_exists( 'ID', $_POST ) ? intval( $_POST['ID'] ) : 0;

  if ( $id <= 0 ) {
    $msg['status'] = 'failure';
    $msg['message'] = 'Invalid ID';
  } else {
    $data = array();
    $set = fields( $fam_data, $data, false );

    if ( array_key_exists( 'Gruppe', $data ) ) {
      $data['Gruppe'] = -intval( $data['Gruppe'] );
    }
    if ( array_key_exists( 'Num', $data ) ) {
      $data['Num'] = -intval( $data['Num'] );
    }

    try {
      $sql = "UPDATE `familien` SET $set WHERE `ID` = :ID";
      
      if ( !empty( $data ) ) {
        $data[":ID"] = $id;
        $stmt = $conn->prepare( $sql );
        $stmt->execute( $data );
      }

      $sql2 = "";
      $get = array();
      if ( array_key_exists( ':Gruppe', $data ) && $data[':Gruppe'] == 0 ) {
        $get[] = 'Gruppe';
      }
      if ( ( array_key_exists( ':Num', $data ) && $data[':Num'] == 0 ) || array_key_exists( ':Gruppe', $data ) ) {
        $get[] = 'Num';
      }
      if ( !empty( $get ) ) {
        $sql2 = sprintf( "SELECT %s FROM `familien` WHERE ID = :ID", implode( ", ", $get ) );
        $stmt = $conn->prepare( $sql2 );
        $stmt->setFetchMode( PDO::FETCH_ASSOC );
        $stmt->execute( array( ':ID' => $id ) );
        $msg['new'] = $stmt->fetch();
      }

      $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES ";
      $logdata = array();
      $sep = "";
      if ( array_key_exists( 'money', $_POST ) && floatval( $_POST['money'] ) !== 0 ) {
        $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
        $logdata[] = 'money';
        $logdata[] = $_POST['money'];
        $sep = ", ";
      }
      if ( array_key_exists( 'attendance', $_POST ) && preg_match( "/^\\d+\/\\d+$/", $_POST['attendance'] ) ) {
        $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
        $logdata[] = 'attendance';
        $logdata[] = $_POST['attendance'];
      }
      if ( !empty( $logdata ) ) {
        $stmt = $conn->prepare( $sql3 );
        $stmt->execute( $logdata );
      }

      $msg['status'] = 'success';
    } catch ( PDOException $e ) {
      $msg['status'] = 'failure';
      $msg['message'] = $e->getMessage();
    }
    if ( DEBUG ) $msg['sql'] = $sql;
    if ( DEBUG ) $msg['sql2'] = $sql2;
    if ( DEBUG ) $msg['sql3'] = $sql3;
    if ( DEBUG ) $msg['_data'] = $data;
  }
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>