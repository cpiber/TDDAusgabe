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
    $conn->beginTransaction();
    $msg['data'] = array();
    $sqls = array();

    $sql = "CREATE TEMPORARY TABLE logsubset SELECT `Type`, `Val`, splitStr(`Val`, '/', 1) AS `P1`, splitStr(`Val`, '/', 2) AS `P2` FROM `logs`";
    if ( !empty( $parts ) )
      $sql = sprintf( "%s WHERE %s", $sql, implode( " AND ", $parts ) );
    $sqls[] = $sql;

    $stmt = $conn->prepare( $sql );
    $stmt->execute( $data );
    
    $sqls[] = $sql = "SELECT SUM(`Val`) AS `Money` FROM logsubset WHERE `Type` = 'money'";
    $stmt = $conn->query( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $msg['data']['money'] = $stmt->fetch()['Money'];
    
    $sqls[] = $sql = "SELECT COUNT(*) AS `Families`, SUM(`P1`) AS `Adults`, SUM(`P2`) AS `Children` FROM logsubset WHERE `Type` = 'attendance'";
    $stmt = $conn->query( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $res = $stmt->fetch();
    $msg['data']['adults'] = $res['Adults'];
    $msg['data']['children'] = $res['Children'];
    $msg['data']['families'] = $res['Families'];

    $sqls[] = $sql = "DROP TEMPORARY TABLE logsubset";
    $conn->exec( $sql );
    
    $conn->commit();
    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql'] = $sqls;
  
  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>