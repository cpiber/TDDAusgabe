<?php

define( 'DB_SERVER', 'localhost' );
define( 'DB_NAME', 'tdd_server' );
define( 'DB_USER', 'tdd' );
define( 'DB_PW', 'tdd2003' );
define( 'STATIC_DIR', __DIR__ . '/remote-static/' );

require "server/synchelpers.php";
require "remote-server/get_status.php";
require "remote-server/put_list.php";
require "remote-server/get_static.php";
require "remote-server/put_static.php";

function connectdb($servername, $dbname, $username, $password) {
  $c = new PDO( "mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password );
  $c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  return $c;
}

class ConflictException extends \Exception {
  /** @param array $cFam
    * @param array $cOrte */
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
  $auth = array_key_exists( 'HTTP_AUTHORIZATION', $_SERVER ) ? $_SERVER['HTTP_AUTHORIZATION'] : "";
  if ( substr( $auth, 0, 6 ) !== "Basic " ) throw new InvalidArgumentException( "Basic authorization required" );
  $key = substr( $auth, 6 );

  $conn = connectdb( DB_SERVER, DB_NAME, DB_USER, DB_PW ); 
  $conn->exec( "SET time_zone = '+00:00'" );
  $expected = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'Key'" )->fetchColumn();
  if ( $expected !== $key ) throw new InvalidArgumentException( "Invalid authorization" );

  if ( !array_key_exists( 'api', $_REQUEST ) ) throw new InvalidArgumentException( "Api path is required" );
  
  if ( $_SERVER["REQUEST_METHOD"] === "GET" ) {
    switch ( $_REQUEST['api'] ) {
      case "status":
        get_status( $conn );
        break;
      case "static":
        get_static();
        break;
      default:
        throw new BadMethodCallException( "Api Method not allowed" );
    }
  } else if ( $_SERVER["REQUEST_METHOD"] === "PUT" || $_SERVER["REQUEST_METHOD"] === "POST" ) {
    switch ( $_REQUEST['api'] ) {
      case "list":
        put_list( $conn );
        break;
      case "static":
        put_static();
        break;
      default:
        throw new BadMethodCallException( "Api Method not allowed" );
    }
  } else {
    throw new BadMethodCallException( "Method not allowed" );
  }
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
} catch ( HTTPException $e ) {
  http_response_code( 500 );
  echo json_encode( array(
    'status' => 'error',
    'message' => $e->getMessage(),
    'remote' => $e->serverdata,
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
