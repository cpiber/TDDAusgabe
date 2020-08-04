<?php

function api_deleteort($msg) {
  global $conn;

  $id = array_key_exists( 'ID', $_POST ) ? intval( $_POST['ID'] ) : 0;

  if ( $id <= 0 ) {
    $msg['status'] = 'failure';
    $msg['message'] = 'Invalid ID';
  } else {
    try {
      $sql = "DELETE FROM `orte` WHERE `ID` = :ID";
      $stmt = $conn->prepare( $sql );
      $stmt->execute( array(":ID" => $id));

      $msg['status'] = 'success';
    } catch ( PDOException $e ) {
      $msg['status'] = 'failure';
      $msg['message'] = $e->getMessage();
    }
    if ( DEBUG ) $msg['sql'] = $sql;
  }
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>