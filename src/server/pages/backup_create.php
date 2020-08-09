<?php

// Create csv backup
function page_backupcreate() {
  global $conn;
  
  $table = isset( $_REQUEST['table'] ) ? $_REQUEST['table'] : "familien";
  $msg = array();
  $tables = array(
    'familien' => 'Familien',
    'orte' => 'Orte',
    'einstellungen' => 'Einstellungen',
  );

  if ( isset( $_POST['page'] ) ) {
    try {
      if ( !isset( $tables[$table] ) ) throw new Exception( "Tabelle ungültig!", 1 );
      
      $sql = "SELECT * FROM `$table`";
      $stmt = $conn->prepare( $sql );
      $stmt->setFetchMode( PDO::FETCH_ASSOC );
      $stmt->execute();
      $fname = $tables[$table];

      if ( $table == 'familien' ) {
        $orte = array();
        $ostmt = $conn->query( "SELECT * FROM `orte`");
        $ostmt->setFetchMode( PDO::FETCH_ASSOC );
        foreach ( $ostmt->fetchAll() as $ort ) {
          $orte[$ort['ID']] = $ort['Name'];
        }
      }

      header( 'Content-Description: File Transfer' );
      header( 'Content-Type: application/octet-stream' );
      header( sprintf( 'Content-Disposition: attachment; filename=%s.csv', $fname ) );
      header( 'Content-Transfer-Encoding: binary' );
      header( 'Connection: Keep-Alive' );
      header( 'Expires: 0' );
      header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
      header( 'Pragma: public' );

      $res = $stmt->fetchAll();
      $out = fopen( 'php://output', 'w' );
      foreach ( $res as $i => $obj ) {
        $line = array();
        $header = array();
        foreach ( $obj as $key => $value ) {
          if ( $i == 0 ) $header[] = $key;
          if ( $table === 'familien' && $key === 'Ort' ) {
            $line[] = array_key_exists( $value, $orte ) ? $orte[$value] : "Unbekannt";
          } else {
            $line[] = $value;
          }
        }
        if ( $i == 0 ) fputcsv( $out, $header );
        fputcsv( $out, $line );
      }
      fclose( $out );
      exit;
    } catch ( Exception $e ) {
      $msg[] = array(
        "Fehler: " . $e->getMessage(),
        "error",
      );
    }
  }

  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title><meta charset=\"UTF-8\">\n\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<link href=\"?file=css\" rel=\"stylesheet\" />";
  // echo "<style>table { max-width: 100%; } table th { font-weight: bold; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; } .msg { margin: 0 10px; padding: 4px 6px; border: 1px solid gray; border-radius: 2px; } .msg.msg-ok { background-color: lightgreen; } .msg.msg-error { background-color: red; }</style>\n";
  echo "</head>\n<body>\n";
  echo "<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" class=\"logo\" /></a></span></div></div>\n";
  echo "<div class=\"body\">";
  foreach ( $msg as $m ) {
    printf( "<p class=\"msg msg-%s\">%s</p>", $m[1], $m[0] );
  }
  if ( !empty( $msg ) ) echo "<p></p>";
  echo "<form action=\"?page=backup/create\" method=\"POST\"><input type=\"hidden\" name=\"page\" value=\"backup/create\" /><select name=\"table\">";
  foreach ( $tables as $name => $display ) {
    printf( "<option value=\"%s\"%s>%s</option>", $name, $name === $table ? " selected" : "", $display );
  }
  echo "</select><br /><input type=\"submit\" value=\"Herunterladen\" /></form>";
  echo "</div></body>\n</html>";
}

?>