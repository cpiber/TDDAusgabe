<?php

function api_getloginfo($msg) {
  global $conn;

  $data = array();
  $parts = array();
  if ( array_key_exists( 'begin', $_POST ) ) {
    $data[":begin"] = $_POST['begin'];
    $parts[] = "`DTime` >= :begin";
  }
  if ( array_key_exists( 'end', $_POST ) ) {
    $data[":end"] = $_POST['end'];
    $parts[] = "`DTime` <= :end";
  }

  try {
    $msg['data'] = array();
    $where = "";
    if ( !empty( $parts ) )
      $where = sprintf( " WHERE %s", implode( " AND ", $parts ) );
    
    $sql = "SELECT SUM(IF(`Type`='money',`Val`,0)) AS `Money`, SUM(IF(`Type`='attendance',1,0)) AS `Families`, SUM(IF(`Type`='attendance',`P1`,0)) AS `Adults`, SUM(IF(`Type`='attendance',`P2`,0)) AS `Children` FROM logsalt $where";
    $stmt = $conn->prepare( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute( $data );
    $res = $stmt->fetch();
    $msg['data']['money'] = $res['Money'];
    $msg['data']['adults'] = $res['Adults'];
    $msg['data']['children'] = $res['Children'];
    $msg['data']['families'] = $res['Families'];

    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql'] = $sql;
  if ( DEBUG ) $msg['_data'] = $data;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>