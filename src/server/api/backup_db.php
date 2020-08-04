<?php

function api_backupdb($msg) {
  global $conn;
  
  date_default_timezone_set( "UTC" );
  $date = new DateTime();
  $d = $date->format( "Ymd-Hi" );
  $db = "tdd_backup_$d";
  $msg['db'] = $db;

  try {
    $sql = "CREATE DATABASE `$db` COLLATE utf8_unicode_ci";
    $conn->exec( $sql );
    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['step'] = 1;
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql1'] = $sql;

  try {
    $conn->exec( "USE `tdd`" );

    $sql = "SHOW TABLES";
    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $tables = $stmt->fetchAll();
    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['step'] = 2;
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql2'] = $sql;

  try {
    $conn->exec( "USE `$db`" );

    $i = 0;
    $sql = array();
    foreach ( $tables as $t ) {
      $t = $t['Tables_in_tdd'];
      $sql[$i] = "CREATE TABLE `$t` SELECT * FROM `tdd`.`$t`";
      $conn->exec( $sql[$i++] );
    }
    $msg['status'] = 'success';
  } catch ( PDOException $e ) {
    $msg['status'] = 'failure';
    $msg['step'] = 3;
    $msg['message'] = $e->getMessage();
  }
  if ( DEBUG ) $msg['sql3'] = $sql;

  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
}

?>