<?php

function api_getsettings($msg) {
  global $conn;

  try {
    $sql = "SELECT * FROM `einstellungen`";
    
    $stmt = $conn->prepare( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute();
    $msg['data'] = array();
    foreach ( $stmt->fetchAll() as $r ) {
      $msg['data'][$r['Name']] = $r['Val'];
    }

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