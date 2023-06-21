<?php

define( 'DB_SERVER', '127.0.0.1:3307' );
define( 'DB_NAME', 'tdd_server' );
define( 'DB_USER', 'tdd' );
define( 'DB_PW', 'tdd2003' );

require "server/synchelpers.php";

function connectdb($servername, $dbname, $username, $password) {
  $c = new PDO( "mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password );
  $c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  return $c;
}

class ConflictException extends \Exception {
  public function __construct($cFam, $cOrte) {
    $ret = "Beide modifiziert: ";
    if ( count( $cFam ) > 0 ) $ret .= "Familie(n) " . implode( ", ", $cFam );
    if ( count( $cOrte ) > 0 ) $ret .= "Ort(e) " . implode( ", ", $cOrte );
    $this->message = $ret;
  }
}

global $famfields, $ortefields;

try {
  header( "Content-Type: application/json" );
  
  if ( $_SERVER["REQUEST_METHOD"] === "GET" ) {
    if ( !array_key_exists( 'sync', $_GET ) || !is_numeric( $_GET['sync'] ) ) throw new InvalidArgumentException( "Sync is required" );

    $conn = connectdb( DB_SERVER, DB_NAME, DB_USER, DB_PW ); 
    $conn->exec( "LOCK TABLES `familien` READ, `orte` READ" );
    $conn->exec( "SET time_zone = '+00:00'" );
    $syncData = array();

    $last_sync = floatval( $_GET['sync'] );
    $recordsFam = "SELECT COUNT(*) FROM `familien` WHERE `last_update` > $last_sync";
    $recordsOrte = "SELECT COUNT(*) FROM `orte` WHERE `last_update` > $last_sync";

    $stmt = $conn->prepare( $recordsFam );
    $stmt->execute();
    $syncData['familien'] = $stmt->fetchColumn();

    $stmt = $conn->prepare( $recordsOrte );
    $stmt->execute();
    $syncData['orte'] = $stmt->fetchColumn();

    $conn->exec( "UNLOCK TABLES" );

    $syncData['status'] = 'success';
    echo json_encode( $syncData );
    exit;
  }


  if ( $_SERVER["REQUEST_METHOD"] !== "PUT" ) throw new BadMethodCallException( "Method not allowed" );
  $body = file_get_contents( 'php://input' );
  $body = json_decode( $body, true );
  if ( is_null( $body ) || !is_array( $body ) ) throw new InvalidArgumentException( "Invalid JSON body" );
  // validate schema
  if ( !array_key_exists( 'sync', $body ) || !array_key_exists( 'familien', $body ) || !array_key_exists( 'orte', $body ) ) throw new InvalidArgumentException( "Missing required field" );
  if ( !is_numeric( $body['sync'] ) ) throw new InvalidArgumentException( "Sync is not a number" );
  if ( !is_array( $body['familien'] ) ) throw new InvalidArgumentException( "Familien is not an array" );
  if ( !is_array( $body['orte'] ) ) throw new InvalidArgumentException( "Orte is not an array" );
  // TODO further validation

  $conn = connectdb( DB_SERVER, DB_NAME, DB_USER, DB_PW ); 
  $conn->exec( "LOCK TABLES `familien` WRITE, `orte` WRITE" );
  $conn->exec( "SET time_zone = '+00:00'" );
  $conn->beginTransaction();
  $syncData = array();

  $last_sync = floatval( $body['sync'] );
  $recordsFam = "SELECT * FROM `familien` WHERE `last_update` > $last_sync";
  $recordsOrte = "SELECT * FROM `orte` WHERE `last_update` > $last_sync";

  $stmt = $conn->prepare( $recordsFam );
  $stmt->setFetchMode( PDO::FETCH_ASSOC );
  $stmt->execute();
  $syncData['familien'] = $stmt->fetchAll();

  $stmt = $conn->prepare( $recordsOrte );
  $stmt->setFetchMode( PDO::FETCH_ASSOC );
  $stmt->execute();
  $syncData['orte'] = $stmt->fetchAll();

  $famIds = array_map( function ($v) { return $v['ID']; }, $body['familien'] );
  $orteIds = array_map( function ($v) { return $v['ID']; }, $body['orte'] );
  $localFamIds = array_map( function ($v) { return $v['ID']; }, $syncData['familien'] );
  $localOrteIds = array_map( function ($v) { return $v['ID']; }, $syncData['orte'] );

  $bothModFam = array_intersect( $famIds, $localFamIds );
  $bothModOrte = array_intersect( $orteIds, $localOrteIds );
  if ( count( $bothModFam ) > 0 || count( $bothModOrte ) > 0 ) throw new ConflictException( $bothModFam, $bothModOrte );

  if ( count( $body['familien'] ) > 0 ) {
    $sql = buildInsert( "familien", $famfields, $body['familien'], $data );
    $stmt = $conn->prepare( $sql );
    $stmt->execute( $data );
  }
  if ( count( $body['orte'] ) > 0 ) {
    $sql = buildInsert( "orte", $ortefields, $body['orte'], $data );
    $stmt = $conn->prepare( $sql );
    $stmt->execute( $data );
  }
  
  $stmt = $conn->prepare( "SELECT NOW()+0" );
  $stmt->execute();
  $syncData['sync'] = $stmt->fetchColumn();
  $conn->commit();

  $syncData['status'] = 'success';
  echo json_encode( $syncData );
} catch ( ConflictException $e ) {
  http_response_code( 409 );
  echo json_encode( array(
    'status' => 'error',
    'message' => $e->getMessage(),
  ) );
} catch ( BadMethodCallException $e ) {
  http_response_code( 405 );
  echo json_encode( array(
    'status' => 'error',
    'message' => $e->getMessage(),
  ) );
} catch ( InvalidArgumentException $e ) {
  http_response_code( 400 );
  echo json_encode( array(
    'status' => 'error',
    'message' => $e->getMessage(),
  ) );
} catch ( Exception $e ) {
  http_response_code( 500 );
  echo json_encode( array(
    'status' => 'error',
    'message' => $e->getMessage(),
  ) );
}
if ( isset( $conn ) ) {
  if ( $conn->inTransaction() ) $conn->rollBack();
  $conn->exec( "UNLOCK TABLES" );
}
