<?php

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
