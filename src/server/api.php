<?php

global $conn;

$fam_data = array(
  'Name' => false,
  'Erwachsene' => false,
  'Kinder' => false,
  'Ort' => false,
  'Gruppe' => false,
  'Schulden' => false,
  'Karte' => true,
  'lAnwesenheit' => true,
  'Notizen' => true,
  'Num' => false,
  'Adresse' => true,
  'Telefonnummer' => true,
  'ProfilePic' => false,
  'ProfilePic2' => false,
);

function fields($fields, &$data, $insert=true) {
  $parts = array();
  foreach ( $fields as $field => $can_empty ) {
    if ( array_key_exists( $field, $_POST['data'] ) && ( $can_empty || ( !$can_empty && $_POST['data'][$field] !== "" ) ) ) {
      $parts[] = $insert ? $field : "$field = :$field";
      $data[":$field"] = $_POST['data'][$field] === "" ? null : $_POST['data'][$field];
    }
  }
  if ( $insert ) {
    return sprintf( "(%s) VALUES (%s)",
      implode( ", ", $parts ),
      array_reduce( $parts, function ($carry, $item) { return sprintf( "%s%s:%s", $carry, empty( $carry ) ? "" : ", ", $item ); } )
    );
  } else {
    return implode( ", ", $parts );
  }
}

require "api/get_familien.php";
require "api/familie_update.php";
require "api/familie_insert.php";
require "api/familie_delete.php";
require "api/familie_profile.php";

require "api/get_orte.php";
require "api/ort_update.php";
require "api/ort_insert.php";
require "api/ort_delete.php";

require "api/get_settings.php";
require "api/setting_update.php";

require "api/get_logs.php";
require "api/get_loginfo.php";
require "api/log_login.php";

require "api/action_backupdb.php";
require "api/action_resetfam.php";
require "api/action_deldate.php";

require "synchelpers.php";
require "api/sync.php";
require "api/sync_upload.php";
require "api/sync_download.php";

if ( array_key_exists( 'api', $_GET ) ) {
  header( "Content-Type: application/json; charset=UTF-8" );
  $msg = array( "status" => "pending" );

  switch ( $_GET['api'] ) {
    case "familie":
      api_getfam($msg);
      break;
    case "familie/update":
      api_updatefam($msg);
      break;
    case "familie/insert":
      api_newfam($msg);
      break;
    case "familie/delete":
      api_deletefam($msg);
      break;
    case "familie/profile":
      api_getfampicture($msg);
      break;
    
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

    case "log":
      api_getlogs($msg);
      break;
    case "log/info":
      api_getloginfo($msg);
      break;
    case "log/login":
      api_loglogin($msg);
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

    case "sync":
      api_sync($msg);
      break;
    case "sync_upload":
      api_sync_upload($msg);
      break;
    case "sync_download":
      api_sync_download($msg);
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
