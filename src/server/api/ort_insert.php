<?php

function api_newort($msg) {
  global $conn;

  $name = array_key_exists( 'Name', $_POST ) ? $_POST['Name'] : "";
  $gruppen = array_key_exists( 'Gruppen', $_POST ) ? intval( $_POST['Gruppen'] ) : null;
  if ( $gruppen !== null && $gruppen < 0 ) $gruppen = 0;
  $data = array();

  try {
    $sql = "INSERT INTO `orte` (";
    $val = "";
    if ( !empty( $name ) ) {
      $sql .= "`Name`";
      $val .= ":name";
      $data[":name"] = $name;
    }
    if ( !empty( $name ) && $gruppen !== null ) { $sql .= ", "; $val .= ", "; }
    if ( $gruppen !== null ) {
      $sql .= "`Gruppen`";
      $val .= ":gruppen";
      $data[":gruppen"] = $gruppen;
    }
    $sql =  sprintf( "%s) VALUES (%s)", $sql, $val );
    
    if ( count( $data ) === 2 ) {
      $stmt = $conn->prepare( $sql );
      $stmt->execute( $data );
      $msg['status'] = 'success';
      $msg['id'] = $conn->lastInsertId();
    } else {
      $msg['status'] = 'failure';
      $msg['message'] = 'No/Insufficient data';
    }

  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>