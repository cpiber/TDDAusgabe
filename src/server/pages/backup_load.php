<?php

// Load csv backup
function page_backupload() {
  global $conn;

  if ( !isset( $_REQUEST['table'] ) || $_REQUEST['table'] == 'familien' ) {
    $table = 'familien';
    $available_cols = array(
      'ID' => true,
      'Name' => true,
      'Erwachsene' => true,
      'Kinder' => true,
      'Personen' => false,
      'Ort' => true,
      'Gruppe' => true,
      'Schulden' => true,
      'Karte' => true,
      'lAnwesenheit' => true,
      'Notizen' => true,
      'Num' => true,
      'Adresse' => true,
      'Straße' => false,
      'Telefonnummer' => true,
    );
  } elseif ( $_REQUEST['table'] == 'orte' ) {
    $available_cols = array(
      'ID' => true,
      'Name' => true,
      'Gruppen' => true,
    );
  } elseif ( $_REQUEST['table'] == 'einstellungen' ) {
    $available_cols = array(
      'ID' => true,
      'Name' => true,
      'Val' => true,
    );
  } else {
    $available_cols = array();
  }
  if ( !isset( $table ) ) $table = $_REQUEST['table'];
  $msg = array();
  if ( isset( $_POST['page'] ) ) {
    try {
      $cols = isset( $_POST['cols'] ) ? $_POST['cols'] : array();
      $selected_cols = array();
      foreach ( $available_cols as $name => $_ ) {
        if ( !array_key_exists( $name, $cols ) ) {
          $selected_cols[$name] = false;
        } else {
          $selected_cols[$name] = true;
        }
      }

      if ( !empty( $_FILES["file"]["tmp_name"] ) ) {
        ini_set( 'auto_detect_line_endings', TRUE );
        $file = $_FILES["file"]["tmp_name"];
        $file = fopen( $file, "r" );
        if ( empty( $_POST['delimiter'] ) ) {
          $delimiter = ",";
        } else {
          $delimiter = $_POST['delimiter'];
        }
        $header = fgetcsv( $file, 0, $delimiter );
        if ( $header === null || $header === false ) {
          $msg[] = array(
            "Datei invalid!",
            "error",
          );
        } else {
          $used_cols = array();
          $names = array();
          $header[0] = preg_replace( '/[\x00-\x1F\x7F-\xFF]/', '', $header[0] );
          // find columns
          foreach ( $selected_cols as $name => $enabled ) {
            if ( $enabled ) {
              if ( ( $col = array_search( $name, $header ) ) !== false ) {
                $used_cols[$name] = $col;
                if ( $table === 'familien' ) {
                  if ( $name != 'Personen' && $name != 'Straße' ) $names[] = $name;
                  if ( $name == 'Personen' && !in_array( 'Erwachsene', $names ) ) $names[] = 'Erwachsene';
                  if ( $name == 'Personen' && !in_array( 'Kinder', $names ) ) $names[] = 'Kinder';
                  if ( $name == 'Straße' && !in_array( 'Adresse', $names ) ) $names[] = 'Adresse';
                } else {
                  $names[] = $name;
                }
              } else {
                $msg[] = array(
                  sprintf( "Spalte %s nicht verfügbar", $name ),
                  "warn",
                );
              }
            }
          }
          // get orte
          if ( $table === 'familien' ) {
            $orte = array();
            $ostmt = $conn->query( "SELECT * FROM `orte`");
            $ostmt->setFetchMode( PDO::FETCH_ASSOC );
            foreach ( $ostmt->fetchAll() as $ort ) {
              $orte[$ort['Name']] = $ort['ID'];
            }
          }
          $keys = array_map( function( $val ) { return sprintf( ":%s", $val ); }, $names );
          $insertSql = sprintf( "INSERT INTO %s ( %s ) VALUES ( %s );", $table, implode( ", ", $names ), implode( ", ", $keys ) );
          $updateSql = sprintf( "UPDATE %s SET ", $table );
          $first = true;
          for ( $i = 0; $i < sizeof($keys); $i++ ) {
            if ( $names[$i] == 'ID' ) continue;
            $updateSql = sprintf( "%s%s%s = %s", $updateSql, !$first ? ", " : "", $names[$i], $keys[$i] );
            $first = false;
          }
          $updateSql .= " WHERE ID = :ID;";
          $insertstmt = $conn->prepare( $insertSql );
          $updatestmt = $conn->prepare( $updateSql );
          $idfromname = $conn->prepare( sprintf( "SELECT ID FROM %s WHERE `Name` = :Name LIMIT 1", $table ) );

          
          if ( isset( $used_cols['ID'] ) || isset( $used_cols['Name'] ) ) {
            $conn->beginTransaction();

            // parse content
            $objs = array();
            while ( ( $data = fgetcsv( $file ) ) !== false ) {
              $cur = sizeof($objs);
              $objs[] = array();
              foreach ( $used_cols as $name => $col ) {
                if ( sizeof($data) > $col ) {
                  if ( $name == "Straße" ) $name = "Adresse";
                  $objs[$cur][$name] = isset( $objs[$cur][$name] ) ? $objs[$cur][$name] + $data[$col] : $data[$col];
                  if ( $name == "Name" && empty( $objs[$cur][$name] ) ) {
                    $msg[] = array(
                      sprintf( "Zeile %s: Name ungültig", strval( $cur + 2 ) ),
                      "warn",
                    );
                  } elseif ( $name == "ID" && !is_numeric( $objs[$cur][$name] ) ) {
                    $msg[] = array(
                      sprintf( "Zeile %s: ID ungültig", strval( $cur + 2 ) ),
                      "warn",
                    );
                  }
                } else {
                  $msg[] = array(
                    sprintf( "Zeile %s enthält Spalte %s nicht.", strval( $cur + 2 ), $name ),
                    "warn",
                  );
                }
              }
            }
            foreach ( $objs as $i => $obj ) {
              if ( ( isset( $obj['Name'] ) && empty( $obj['Name'] ) ) || ( isset( $obj['ID'] ) && !is_numeric($obj['ID']) ) ) {
                $msg[] = array(
                  sprintf( "Überspringe Zeile %s", strval( $i + 2 ) ),
                  "warn",
                );
                continue;
              }
              if ( isset( $obj['Ort'] ) ) {
                $obj['Ort'] = array_key_exists( $obj['Ort'], $orte ) ? $orte[$obj['Ort']] : 0;
              }
              if ( isset( $obj['Personen'] ) ) {
                $matches = array();
                if ( preg_match("/(\d+)(?:\/(\d+))?/", $obj['Personen'], $matches ) ) {
                  $obj['Erwachsene'] = $matches[1];
                  $obj['Kinder'] = isset( $matches[2] ) ? $matches[2] : 0;
                } else {
                  $msg[] = array(
                    sprintf( "Personen in Zeile %s ungültig. Auf 0/0 gesetzt.", strval( $cur + 2 ) ),
                    "warn",
                  );
                  $obj['Erwachsene'] = 0;
                  $obj['Kinder'] = 0;
                }
              }
              $id = isset( $obj['ID'] ) ? $obj['ID'] : 0;
              if ( isset( $obj['Name'] ) ) {
                $idfromname->execute( array( ":Name" => $obj['Name'] ) );
                $res = $idfromname->fetch( PDO::FETCH_ASSOC );
                if ( $res && isset( $res['ID'] ) ) $id = $res['ID'];
              }

              $set = array();
              if ( $id ) $set[":ID"] = $id;
              foreach ( $obj as $key => $value ) {
                $name = sprintf( ":%s", $key );
                if ( $key == "ID" || $key == "Personen" ) continue;
                if ( ( $key == "lAnwesenheit" || $key == "Karte" ) && ( $value == "0000-00-00" || $value == "" ) ) {
                  $set[$name] = null;
                  continue;
                }
                $set[$name] = $value;
              }
              
              if ( $id == 0 ) {
                $insertstmt->execute( $set );
              } else {
                $updatestmt->execute( $set );
              }
            }

            $conn->commit();

            $msg[] = array(
              "Erfolgreich importiert.",
              "ok",
            );
          } else {
            $msg[] = array(
              "ID oder Name müssen gegeben sein!",
              "error",
            );
          }
        }
        fclose( $file );
        ini_set( 'auto_detect_line_endings', FALSE );
      } else {
        $msg[] = array(
          "Keine Datei gegeben!",
          "error",
        );
      }
    } catch ( Exception $e ) {
      $msg[] = array(
        "Fehler: " . $e->getMessage(),
        "error",
      );
    }
  } else {
    $selected_cols = $available_cols;
  }

  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title><meta charset=\"UTF-8\">\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<link href=\"?file=css\" rel=\"stylesheet\" />";
  echo "</head>\n<body>\n";
  echo "<div id=\"header\" class=\"header\"><div><span><a href=\"?\"><img src=\"?file=logo\" class=\"logo\" /></a></span></div></div>\n";
  echo "<div class=\"body\">";
  foreach ( $msg as $m ) {
    printf( "<p class=\"msg msg-%s\">%s</p>", $m[1], $m[0] );
  }
  if ( !empty( $msg ) ) echo "<p></p>";
  echo "<form action=\"?page=backup/load\" method=\"GET\"><input type=\"hidden\" name=\"page\" value=\"backup/load\" /><select name=\"table\">";
  $tables = array(
    'familien' => 'Familien',
    'orte' => 'Orte',
    'einstellungen' => 'Einstellungen',
    'logs' => 'Logs',
  );
  foreach ( $tables as $name => $display ) {
    printf( "<option value=\"%s\"%s>%s</option>", $name, $name === $table ? " selected" : "", $display );
  }
  echo "</select><input type=\"submit\" value=\"Wählen\" /></form>";
  echo "<form action=\"?page=backup/load\" method=\"POST\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"page\" value=\"backup/load\" /><input type=\"hidden\" name=\"table\" value=\"" . $table . "\" />\n<table><tbody>\n<tr><th>Spalte</th><th>Laden</th>\n";
  foreach ( $selected_cols as $name => $enabled ) {
    printf( "<tr><td><label for=\"%s\">%s</label></td><td><input type=\"checkbox\" id=\"%s\" name=\"cols[%s]\"%s value=\"on\" /></td></tr>\n", $name, $name, $name, $name, $enabled ? " checked" : "" );
  }
  echo "<tr><td><i>Alle</i></td><td><input type=\"checkbox\" id=\"toggle_all\" checked /></td></tr>\n";
  echo "</tbody></table>\n";
  echo "<p><label title=\"Zeichen zwischen Spalten. Für Excel-Export ;\">CSV-Trennzeichen: <input type=\"text\" name=\"delimiter\" placeholder=\",\" size=\"1\" /></label><br /><label>Datei: <input type=\"file\" name=\"file\" /></label></p>\n";
  echo "<p><input type=\"submit\" value=\"Backup laden\" /></p>\n";
  if ( $table === 'familien' ) echo "<p><b>Achtung:</b> Orte müssen bereits in der Datenbank sein. Unbekannte Orte werden auf 'Unbekannt' gesetzt.</p>";
  echo "<p>Info: Wenn ID angewählt ist, werden existierende Einträge mit der selben ID aktualisiert. In dem Fall wird nichts unternommen, sollte die ID nicht existieren.</p>";
  echo "</form>\n";
  echo "<script>window.onload = function(){var boxes = document.getElementsByTagName('input'); document.getElementById('toggle_all').addEventListener('click', function() {for (var i = 0; i < boxes.length; i++) {if (boxes[i].type == 'checkbox') {boxes[i].checked = this.checked;}}});};</script>";
  echo "</div></body>\n</html>";
}

?>