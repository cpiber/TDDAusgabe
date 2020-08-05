<?php

function api_updateort($msg) {
  global $conn;

  $id = array_key_exists( 'ID', $_POST ) ? intval( $_POST['ID'] ) : 0;

  if ( $id <= 0 ) {
    $msg['status'] = 'failure';
    $msg['message'] = 'Invalid ID';
  } else {
    $name = array_key_exists( 'Name', $_POST ) ? $_POST['Name'] : "";
    $gruppen = array_key_exists( 'Gruppen', $_POST ) ? intval( $_POST['Gruppen'] ) : null;
    if ( $gruppen !== null && $gruppen < 0 ) $gruppen = 0;
    $data = array();

    try {
      $sql = "UPDATE `orte` SET ";
      if ( !empty( $name ) ) {
        $sql .= "`Name` = :name ";
        $data[":name"] = $name;
      }
      if ( !empty( $name ) && $gruppen !== null ) $sql .= ", ";
      if ( $gruppen !== null ) {
        $sql .= "`Gruppen` = :gruppen ";
        $data[":gruppen"] = $gruppen;
      }
      $sql .= "WHERE `ID` = :ID";
      
      if ( !empty( $data ) ) {
        $data[":ID"] = $id;
        $stmt = $conn->prepare( $sql );
        $stmt->execute( $data );
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