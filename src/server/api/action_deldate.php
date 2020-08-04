<?php

function api_deldate($msg) {
  global $conn;

  $col = array_key_exists( 'col', $_POST ) ? $_POST['col'] : "";
  $date = array_key_exists( 'date', $_POST ) ? $_POST['date'] : "";

  if ( array_search( $col, array('lAnwesenheit', 'Karte') ) === false ) {
    $msg['status'] = 'failure';
    $msg['message'] = 'Illegal column';
  } else if ( empty( $date ) ) {
    $msg['status'] = 'failure';
    $msg['message'] = 'Missing date';
  } else {
    try {
      $sql = "DELETE FROM `familien` WHERE `$col` <= :date AND `$col` IS NOT NULL";
      $stmt = $conn->prepare( $sql );
      $stmt->execute(array(
        ":date" => $date
      ));
      $msg['entries'] = $stmt->rowCount();
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