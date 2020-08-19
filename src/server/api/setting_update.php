<?php

function api_updatesetting($msg) {
  global $conn;

  $vals = array_key_exists( 'settings', $_POST ) ? $_POST['settings'] : "";
  
  $sql = "UPDATE `einstellungen` SET `Val` = :val WHERE `Name` = :name";
  $stmt = $conn->prepare( $sql );

  $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES ";
  $logdata = array();
  $sep = "";
  foreach ( $vals as $v ) {
    $name = array_key_exists( 'Name', $v ) ? $v['Name'] : "";
    $val = array_key_exists( 'Val', $v ) ? $v['Val'] : "";

    if ( empty( $name ) ) {
      $msg['status'] = 'failure';
      $msg['message'] = 'Invalid Name';
    } else {
      try {
        $stmt->execute(array(
          ":name" => $name,
          ":val" => $val
        ));

        $sql3 = sprintf( "%s%s(?, ?)", $sql3, $sep );
        $sep = ", ";
        $logdata[] = 'update';
        $logdata[] = sprintf( 'setting/%s', $name );

        $msg['status'] = 'success';
      } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        break;
      }
    }
  }
  if ( !empty( $logdata ) ) {
    $stmt = $conn->prepare( $sql3 );
    $stmt->execute( $logdata );
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>