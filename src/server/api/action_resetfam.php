<?php

function api_resetfam($msg) {
  global $conn;

  try {
    $sql = "CALL resetFamNum()";
    $conn->exec( $sql );
    
    $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES ";
    $logdata = array();
    $sep = "";
    $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
    $logdata[] = 'resetFam';
    $logdata[] = null;
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
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>