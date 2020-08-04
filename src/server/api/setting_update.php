<?php

function api_updatesetting($msg) {
  global $conn;

  $vals = array_key_exists( 'settings', $_POST ) ? $_POST['settings'] : "";
  
  $sql = "UPDATE `einstellungen` SET `Val` = :val WHERE `Name` = :name";
  $stmt = $conn->prepare( $sql );
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

        $msg['status'] = 'success';
      } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
      }
    }
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>