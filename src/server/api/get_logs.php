<?php

function api_getlogs($msg) {
  global $conn;

  $data = array();
  $parts = array( "1=1" );
  if ( array_key_exists( 'begin', $_POST ) ) {
    $data[":begin"] = $_POST['begin'];
    $parts[] = "`DTime` >= :begin";
  }
  if ( array_key_exists( 'end', $_POST ) ) {
    $data[":end"] = $_POST['end'];
    $parts[] = "`DTime` <= :end";
  }
  if ( array_key_exists( 'type', $_POST ) ) {
    $data[":type"] = $_POST['type'];
    $parts[] = "`Type` = :type";
  }
  $page = array_key_exists( 'page', $_POST ) ? intval( $_POST['page'] ) : 1;
  if ( $page < 1 ) $page = 1;
  $pagesize = array_key_exists( 'pagesize', $_POST ) ? intval( $_POST['pagesize'] ) : 20;
  if ( $pagesize < 1 && $pagesize != -1 ) $pagesize = 20;

  try {
    $where = implode( " AND ", $parts );
    $sql = "SELECT * FROM `logs` WHERE $where";

    if ( $pagesize != -1 ) {
      $pagesstmt = $conn->prepare( "SELECT COUNT(*) AS `cnt` FROM `logs` WHERE $where" );
      $pagesstmt->setFetchMode( PDO::FETCH_ASSOC );
      $pagesstmt->execute( $data );
      $msg['pages'] = ceil( intval( $pagesstmt->fetch()['cnt'] ) / $pagesize );

      $offset = ( $page - 1 ) * $pagesize;
      $sql = sprintf( "%s LIMIT %s OFFSET %s", $sql, $pagesize, $offset );
    }
    
    $stmt = $conn->prepare( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute( $data );
    $msg['data'] = $stmt->fetchAll();

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