<?php

function api_newort($msg) {
  global $conn;

  $name = array_key_exists( 'Name', $_POST ) ? $_POST['Name'] : "";
  $gruppen = array_key_exists( 'Gruppen', $_POST ) ? intval( $_POST['Gruppen'] ) : 0;
  if ( $gruppen < 0 ) $gruppen = 0;
  $data = array();

  try {
    $sql = "INSERT INTO `orte` (";
    $val = "";
    if ( !empty( $name ) ) {
      $sql .= "`Name`, ";
      $val .= ":name, ";
      $data[":name"] = $name;
    }
    $sql .= "`Gruppen`";
    $val .= ":gruppen";
    $data[":gruppen"] = $gruppen;
    
    $sql =  sprintf( "%s) VALUES (%s)", $sql, $val );
    
    if ( count( $data ) === 2 ) {
      $stmt = $conn->prepare( $sql );
      $stmt->execute( $data );
      $id = $msg['id'] = $conn->lastInsertId();

      $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES ";
      $logdata = array();
      $sep = "";
      $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
      $logdata[] = 'insert';
      $logdata[] = sprintf( 'ort/%s', $id );
      if ( !empty( $logdata ) ) {
        $stmt = $conn->prepare( $sql3 );
        $stmt->execute( $logdata );
      }
      
      $msg['status'] = 'success';
    } else {
      $msg['status'] = 'failure';
      $msg['message'] = 'Name fehlt';
    }

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