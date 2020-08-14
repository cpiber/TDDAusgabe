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
      $msg['status'] = 'success';
      $msg['id'] = $conn->lastInsertId();
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