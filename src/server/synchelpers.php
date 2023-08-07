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
/** @param string $key
  * @param string $server
  * @param string $endpoint
  * @param string $method
  * @param ?string|string[] $header
  * @param ?string $content
  * @param ?int $timeout
  * @param ?bool $json
  * @return mixed */
function server_send($key, $server, $endpoint, $method, $header = "", $content = "", $timeout = 10, $json = true) {
  if ( is_array( $header ) ) {
    $header[] = "Authorization: Basic $key";
  } else if ( $header !== "" ) {
    $header = array( $header, "Authorization: Basic $key" );
  } else {
    $header = array( "Authorization: Basic $key" );
  }
  $ch = curl_init( "$server?api=$endpoint" );
  curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
  curl_setopt( $ch, CURLOPT_STDERR, fopen( 'curl.log', 'w' ) );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $content );
  curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
  $response = curl_exec($ch);

  if ( $response === false || curl_error( $ch ) ) {
    $err = curl_error( $ch );
    curl_close( $ch );
    throw new HTTPException( "Request failed: Could not connect ($method $endpoint)", $err );
  }
  $serverdata = $json ? json_decode( $response, true ) : $response;
  $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
  curl_close( $ch  );
  if ( $status !== 200 || ( $json && !is_array( $serverdata ) ) ) throw new HTTPException( "Request failed: $status ($method $endpoint)", $serverdata );
  return $serverdata;
}

function upload_file($key, $server, $endpoint, $file) {
  return server_send( $key, $server, $endpoint, HTTP_PUT, 'Content-Type: application/octet-stream', file_get_contents($file) );
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
