<?php


require "api/backup_db.php";
require "api/reset_fam.php";
require "api/get_orte.php";
require "api/ort_update.php";
require "api/ort_insert.php";
require "api/ort_delete.php";

if ( array_key_exists( 'api', $_GET ) ) {
  header( "Content-Type: application/json; charset=UTF-8" );
  $msg = array( "status" => "pending" );

  switch ( $_GET['api'] ) {
    case "ort":
      api_getorte($msg);
      break;
    case "ort/update":
      api_updateort($msg);
      break;
    case "ort/insert":
      api_newort($msg);
      break;
    case "ort/delete":
      api_deleteort($msg);
      break;

    case "backup_db":
      api_backupdb($msg);
      break;

    case "reset_fam":
      api_resetfam($msg);
      break;
      
    default:
      http_response_code( 404 );
      echo '{"status":"failure","message":"Unknown endpoint"}';
      break;
  }
  $conn = null;
  exit;
}


?>