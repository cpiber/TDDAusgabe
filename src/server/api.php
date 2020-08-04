<?php

require "api/get_orte.php";
require "api/ort_update.php";
require "api/ort_insert.php";
require "api/ort_delete.php";

require "api/get_settings.php";
require "api/setting_update.php";

require "api/action_backupdb.php";
require "api/action_resetfam.php";
require "api/action_deldate.php";

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

    case "setting":
      api_getsettings($msg);
      break;
    case "setting/update":
      api_updatesetting($msg);
      break;

    case "action/backupDB":
      api_backupdb($msg);
      break;
    case "action/resetFam":
      api_resetfam($msg);
      break;
    case "action/delDate":
      api_deldate($msg);
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