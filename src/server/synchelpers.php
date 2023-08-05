<?php

/** @param string $table
  * @param string[] $fields
  * @param array[] $data
  * @param ?array &$out_data
  * @return string */
function buildInsert($table, $fields, $data, &$out_data) {
  $out_data = array();
  $q = array();
  foreach ($data as $val) {
    $q[] = "(" . str_repeat( "?,", count( $fields ) ) . "NOW())";
    foreach ($fields as $field) {
      if ( $field === 'deleted' ) $val[$field] = intval( $val[$field] ) > 0;
      $out_data[] = $val[$field];
    }
  }
  $u = array_map( function ($v) { return "`$v` = VALUES(`$v`)"; }, array_filter( $fields, function ($c) { return $c !== "ID"; } ) );
  return "INSERT INTO `$table` (" . implode( ", ", $fields ) . ", last_update) VALUES " . implode( ", ", $q )
    . " ON DUPLICATE KEY UPDATE " . implode( ", ", $u ) . ", `last_update` = NOW()";
}

/** @param string $dir
  * @return string[] */
function getStaticFiles($dir) {
  try {
    $it = new FilesystemIterator($dir);
    $ret = array();
    foreach ($it as $fileinfo) {
      $ret[] = $fileinfo->getFilename();
    }
    return $ret;
  } catch (UnexpectedValueException $_) {
    // FilesystemIterator:  Throws an UnexpectedValueException if the directory does not exist. 
    return array();
  }
}

/** @param string $server
  * @param string $endpoint
  * @param string[] $http
  * @param ?bool $json
  * @return mixed */
function _server_send($server, $endpoint, $http, $json = true) {
  $context = stream_context_create( array(
    'http' => $http,
  ) );
  $method = array_key_exists( 'method', $http ) ? $http['method'] : 'GET';
  $response = @file_get_contents( "$server?api=$endpoint", false, $context );
  if ( $response === false ) throw new Exception( "Request failed: Could not connect ($method $endpoint)" );
  $serverdata = $json ? json_decode( $response, true ) : $response;

  while ( count( $http_response_header ) > 0 ) {
    $v = array_shift( $http_response_header );
    if ( substr( $v, 0, 5 ) !== "HTTP/" ) break;
    $status_line = $v;
  }
  if ( ! isset( $status_line ) ) throw new Exception( "Request failed: Could not read status code ($method $endpoint)" );
  $status = intval( explode( " ", $status_line )[1] ); // HTTP/1.1 200 OK => 200
  if ( $status !== 200 || ( $json && !is_array( $serverdata ) ) ) throw new Exception( "Request failed: $status_line ($method $endpoint)" );
  return $serverdata;
}

function _upload_file($server, $endpoint, $file) {
  return _server_send($server, $endpoint, array(
    'method'  => 'PUT',
    'header'  => 'Content-Type: application/octet-stream',
    'content' => file_get_contents($file),
    'timeout' => 10,
    'ignore_errors' => true,
  ));
}

$famfields = array(
  'ID',
  'Name',
  'Erwachsene',
  'Kinder',
  'Ort',
  'Gruppe',
  'Schulden',
  'Karte',
  'lAnwesenheit',
  'Notizen',
  'Num',
  'Adresse',
  'Telefonnummer',
  'ProfilePic',
  'ProfilePic2',
  'deleted',
);
$ortefields = array(
  'ID',
  'Name',
  'Gruppen',
  'deleted',
);
