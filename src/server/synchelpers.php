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

/** @param PDO $conn
  * @param string[] $files */
function queryMissingFiles($conn, $files) {
  $missing = array();
  $stmt = $conn->query( "SELECT DISTINCT `ProfilePic` FROM `familien` WHERE `ProfilePic` IS NOT NULL AND `ProfilePic` <> ''
                   UNION SELECT DISTINCT `ProfilePic2` FROM `familien` WHERE `ProfilePic2` IS NOT NULL AND `ProfilePic2` <> ''" );
  while ( ( $res = $stmt->fetchColumn() ) !== false ) {
    if ( !in_array( $res, $files ) ) $missing[] = $res;
  }
  return $missing;
}

class HTTPException extends \Exception {
  public $serverdata;

  public function __construct($message, $data) {
    $json = is_string( $data ) ? json_decode( $data, true ) : $data;
    if ( $json !== null ) $data = $json;
    if ( is_array( $json ) && array_key_exists( 'message', $json ) ) $message .= ". Fehler: {$json['message']}";
    parent::__construct($message);
    $this->serverdata = $data;
  }
}

define( 'HTTP_PUT', 'PUT' );
define( 'HTTP_GET', 'GET' );
/** @param string $server
  * @param string $endpoint
  * @param string $method
  * @param ?string|string[] $header
  * @param ?string $content
  * @param ?int $timeout
  * @param ?bool $json
  * @return mixed */
function server_send($server, $endpoint, $method, $header = "", $content = "", $timeout = 10, $json = true) {
  $context = stream_context_create( array(
    'http' => array(
      'method'  => $method,
      'header'  => $header,
      'content' => $content,
      'timeout' => $timeout,
      'ignore_errors' => true,
    ),
  ) );
  $response = @file_get_contents( "$server?api=$endpoint", false, $context );
  if ( $response === false ) throw new HTTPException( "Request failed: Could not connect ($method $endpoint)", $response );
  $serverdata = $json ? json_decode( $response, true ) : $response;

  while ( count( $http_response_header ) > 0 ) {
    $v = array_shift( $http_response_header );
    if ( substr( $v, 0, 5 ) !== "HTTP/" ) break;
    $status_line = $v;
  }
  if ( ! isset( $status_line ) ) throw new HTTPException( "Request failed: Could not read status code ($method $endpoint)", $serverdata );
  $status = intval( explode( " ", $status_line )[1] ); // HTTP/1.1 200 OK => 200
  if ( $status !== 200 || ( $json && !is_array( $serverdata ) ) ) throw new HTTPException( "Request failed: $status_line ($method $endpoint)", $serverdata );
  return $serverdata;
}

function upload_file($server, $endpoint, $file) {
  return server_send( $server, $endpoint, HTTP_PUT, 'Content-Type: application/octet-stream', file_get_contents($file) );
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
