<?php

define( 'DB_SERVER', '127.0.0.1:3307' );
define( 'DB_NAME', 'tdd_server' );
define( 'DB_USER', 'tdd' );
define( 'DB_PW', 'tdd2003' );

require "server/synchelpers.php";
require "remote-server/get_status.php";
require "remote-server/put_list.php";

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

  if ( !array_key_exists( 'api', $_GET ) ) throw new InvalidArgumentException( "Api path is required" );
  $conn = connectdb( DB_SERVER, DB_NAME, DB_USER, DB_PW ); 
  
  if ( $_SERVER["REQUEST_METHOD"] === "GET" ) {
    switch ( $_GET['api'] ) {
      case "status":
        get_status( $conn );
        break;
      default:
        throw new BadMethodCallException( "Api Method not allowed" );
    }
  } else if ( $_SERVER["REQUEST_METHOD"] === "PUT" ) {
    switch ( $_GET['api'] ) {
      case "list":
        put_list( $conn );
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
