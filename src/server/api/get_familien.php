<?php

function parse_searchstring($string) {
  $string = trim( $string );
  
  $matches;
  // https://stackoverflow.com/a/366239/
  if ( !preg_match_all( "/(!?=?)(?:(['\"])(.*?)(?<!\\\\)(?>\\\\\\\\)*\\2|([^\\s]+))/", $string, $matches, PREG_SET_ORDER ) )
    return false;
  return $matches;
}

function parse_searchmatches($matches, &$data, $columns, $prefix=':__search_') {
  $parts = array();
  foreach ( $matches as $i => $match ) {
    $matchparts = array();
    if ( array_key_exists( 4, $match ) ) {
      $text = $match[4];
    } else {
      $text = $match[3];
    }
    $like = true; $not = false;
    $comp = "LIKE";
    if ( $match[1] === "!=" ) {
      $like = false; $not = true;
      $comp = "<>";
    } else if ( $match[1] === "!" ) {
      $not = true;
      $comp = "NOT LIKE";
    } else if ( $match[1] === "=" ) {
      $like = false;
      $comp = "=";
    }
    $numeric = preg_match( "/^\\d+$/", $text );
    
    $name = sprintf( "%s%s", $prefix, $i );
    $data[$name] = $like ? sprintf( "%%%s%%", $text ) : $text;
    foreach ( $columns as $column => $non_num ) {
      if ( !$numeric && !$non_num )
        continue;

      $matchparts[] = sprintf( "`%s` %s %s", $column, $comp, $name );
    }
    if ( !empty( $matchparts ) )
      $parts[] = sprintf( " ( %s ) ", implode( $not ? " AND " : " OR ", $matchparts ) );
  }
  if ( empty( $parts ) ) return "";
  return sprintf( " ( %s ) ", implode( " AND ", $parts ) );
}

function api_getfam($msg) {
  global $conn;

  if ( array_key_exists( 'search', $_POST ) ) {
    $num = intval( $_POST['search'] );
    if ( !empty( $num ) )
      $id = $num;
  }

  $data = array();
  $parts = array( "1=1" );
  if ( array_key_exists( 'ID', $_POST ) || isset( $id ) ) {
    if ( !isset( $id ) )
      $id = $_POST['ID'];
    $data[":ID"] = $id;
    $parts[] = "`ID` = :ID";
    $single = true;
  } else {
    $single = false;
    if ( array_key_exists( 'ort', $_POST ) ) {
      $data[":ort"] = $_POST['ort'];
      $parts[] = "`Ort` = :ort";
    }
    if ( array_key_exists( 'gruppe', $_POST ) ) {
      $data[":gruppe"] = $_POST['gruppe'];
      $parts[] = "`Gruppe` = :gruppe";
    }
    if ( array_key_exists( 'search', $_POST ) ) {
      $search = $_POST['search'];
      $cols = array(
        'ID' => false,
        'Name' => true,
        'Ort' => true,
        'Gruppe' => false,
        'lAnwesenheit' => true,
        'Notizen' => true,
        'Num' => false,
        'Adresse' => true,
        'Telefonnummer' => true,
      );
      $searchparts = array();
      $m = parse_searchstring( $search );
      if ( $m !== false )
        $parts[] = parse_searchmatches( $m, $data, $cols );
    }
    $page = array_key_exists( 'page', $_POST ) ? intval( $_POST['page'] ) : 1;
    if ( $page < 1 ) $page = 1;
    $pagesize = array_key_exists( 'pagesize', $_POST ) ? intval( $_POST['pagesize'] ) : 50;
    if ( $pagesize < 1 && $pagesize != -1 ) $pagesize = 50;
  }

  try {
    $where = implode( " AND ", $parts );
    $sql = "SELECT * FROM `familien` WHERE $where ORDER BY `Ort`, `Gruppe`, `Num`, `ID`";

    if ( !$single && $pagesize != -1 ) {
      $pagesstmt = $conn->prepare( "SELECT COUNT(*) AS `cnt` FROM `familien` WHERE $where" );
      $pagesstmt->setFetchMode( PDO::FETCH_ASSOC );
      $pagesstmt->execute( $data );
      $msg['pages'] = ceil( intval( $pagesstmt->fetch()['cnt'] ) / $pagesize );

      $offset = ( $page - 1 ) * $pagesize;
      $sql = sprintf( "%s LIMIT %s OFFSET %s", $sql, $pagesize, $offset );
    }
    
    $stmt = $conn->prepare( $sql );
    $stmt->setFetchMode( PDO::FETCH_ASSOC );
    $stmt->execute( $data );
    if ( $single ) {
      $msg['data'] = $stmt->fetch();
    } else {
      $msg['data'] = $stmt->fetchAll();
    }

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