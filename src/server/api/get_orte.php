<?php

function api_getorte($msg) {
  global $conn;

  try {
    $sql = "SELECT * FROM `orte` WHERE `deleted` = 0";
    
    $stmt = $conn->query( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $msg['data'] = $stmt->fetchAll();

    $msg['status'] = 'success';
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