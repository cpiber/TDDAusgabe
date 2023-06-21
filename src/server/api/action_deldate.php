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
      $sql = "UPDATE `familien` SET `deleted` = 1 WHERE `$col` <= :date AND `$col` IS NOT NULL";
      $stmt = $conn->prepare( $sql );
      $stmt->execute(array(
        ":date" => $date
      ));
      $msg['entries'] = $stmt->rowCount();
      
      $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES ";
      $logdata = array();
      $sep = "";
      $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
      $logdata[] = 'delete';
      $logdata[] = sprintf( '%s familie', $msg['entries'] );
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
  }
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>