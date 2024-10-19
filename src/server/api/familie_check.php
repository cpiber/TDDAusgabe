<?php

function api_checkfamname($msg) {
  global $conn;

  $id = array_key_exists( 'ID', $_POST ) ? intval( $_POST['ID'] ) : -1;

  try {
    $sql = "SELECT 1 FROM `familien` WHERE `Name` = :Name AND `ID` <> :ID AND `deleted` = 0";
    if ( !array_key_exists( 'name', $_POST ) ) throw new InvalidArgumentException( 'Name required' );

    $stmt = $conn->prepare( $sql );
    $stmt->execute( array( ":Name" => $_POST['name'], ":ID" => $id ) );
    $found = strval($stmt->fetchColumn()) === "1";
    $msg['data'] = array( 'duplicate' => $found );

    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  if ( DEBUG ) $msg['id'] = $id;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>
