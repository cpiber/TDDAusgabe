<?php


// Print-Version
if ( isset( $_GET['print'] ) ) {
  echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<style>table { width: 100%; } table th { text-align: left; } tbody > tr > * { border: 1px solid grey; padding: 5px; } h3 { margin-top: 1em; margin-bottom: 0; } span.check { width: 14px; height: 14px; border: 1px solid black; display: block; float: right; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; }</style>\n";
  echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
  $o = ( isset( $_GET['ort'] ) ? $_GET['ort'] : "Alle" );
  echo "<div class=\"body\"><form action=\"?print\" method=\"GET\">\n<input type=\"hidden\" name=\"print\" value=true>\n<select id=\"ort\" name=\"ort\">\n<option value=\"Alle\">Alle</option>\n";

  try {
    $sql = "SELECT * FROM `orte`";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();

    $orte = array();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    foreach ( $stmt->fetchAll() as $r) {
      $orte[$r['Name']] = $r;
      $s = ( $r['Name'] == $o ? " selected" : "" );
      echo "<option value=\"" . $r['Name'] . "\"$s>" . iconv( "iso8859-9", "utf-8", rawurldecode($r['Name'])) . "</option>\n";
    }
    $break = false;

  } catch ( PDOException $e ) {
    echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
    $break = true;

  }
  echo "</select>\n";
  if ( $break ) exit;
  echo "<select id=\"gruppe\" name=\"gruppe\">\n<option value=\"Alle\">Alle</option>\n";
  if ( $o != "Alle" ) {
    $g = ( isset( $_GET['gruppe'] ) ? $_GET['gruppe'] : "Alle" );
    $ort = $orte[$o];
    for ( $i = 1; $i <= $ort['Gruppen']; $i++ ) {
      $s = ( $i == $g ? " selected" : "" );
      echo "<option value=\"$i\"$s>Gruppe $i</option>";
    }

  } else {
    $g = "Alle";
  }
  echo "</select>\n";
  echo "<input type=\"submit\" value=\"OK\">\n</form>";

  // Get settings
  try {
    $sql = "SELECT * FROM `einstellungen`";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();

    $orte = array();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    foreach ( $stmt->fetchAll() as $r) {
      $einst[$r['Name']] = rawurldecode( $r['Val'] );
    }

  } catch ( PDOException $e ) {
    echo "<i>Fehler bei abrufen der Einstellungen</i><br>" . $e->getMessage();
    exit;

  }

  // Get Familien in selected Ort
  try {
    if ( $o == "Alle" ) { $o = "%"; }
    if ( $g == "Alle" ) { $g = "%"; }
    $sql = "SELECT * FROM `familien` WHERE `Ort` LIKE :ort AND `Gruppe` LIKE :gruppe ORDER BY Ort, Gruppe, Num, ID";
    
    $stmt = $conn->prepare( $sql );
    $stmt->execute( array( ':ort' => $o, ':gruppe' => $g ) );

    $ort = ""; $gruppe = -1;
    $result = $stmt->setFetchMode( PDO::FETCH_ASSOC );
    foreach ( $stmt->fetchAll() as $r ) {
      if ( $ort != $r['Ort'] ) {
        if ( $ort != "" ) {
          echo "</tbody></table>\n";
        }
        $ort = $r['Ort']; $gruppe = -1;
        echo "<h1>".iconv( "iso8859-9", "utf-8", rawurldecode($ort) )."</h1>\n<table><tbody>\n";
      }
      if ( $gruppe != $r['Gruppe'] ) {
        $gruppe = $r['Gruppe'];
        echo "<tr><td colspan=\"8\"><h3>Gruppe $gruppe</h3></td></tr>\n<tr><th>Nummer</th><th>Name</th><th>Erw. / Kinder</th><th>Preis</th><th>Schulden</th><th>Karte</th><th>Notizen</th><th>Adresse</th></tr>\n";
      }

      $num = $r['Num'];
      $name = iconv( "iso8859-9", "utf-8", rawurldecode($r['Name']) );
      $anw = "<span class=\"check\"></span>";
      $leute = $r['Erwachsene'] . " / " . $r['Kinder'];
      $preis = $einst['Preis'];
      $preis = strtr( $preis, array( 'e' => (int)$r['Erwachsene'], 'k' => (int)$r['Kinder'] ) );
      ob_start();
      try {
        $preis = eval( "return (int)(" . preg_replace( '/[^0-9\+\-\*\/\(\)\.><=]/', '', $preis ) . ");" );
        //keine Zahl -> Fehler
        if ( $preis === false ) {
          ob_clean();
          $preis = "<i>Fehler in der Preis-Formel</i>";
        } else {
          $preis = number_format( $preis, 2 ) . "€";
        }
      } catch ( ParseError $e ) {
        $preis = "<i>Fehler in der Preis-Formel</i>";
      }
      $schuld = $r['Schulden'] . "€";
      date_default_timezone_set( "UTC" );
      $karte = strtotime( $r['Karte'] );
      $karte = date( "d. m. Y", $karte );
      $karte = ( $r['Karte'] == "" || $r['Karte'] == "0000-00-00" ? "" : $karte );
      $notiz = iconv( "iso8859-9", "utf-8", rawurldecode($r['Notizen']) );
      $addr = iconv( "iso8859-9", "utf-8", rawurldecode($r['Adresse']) );
      echo "<tr><td>$num</td><td><span>$name</span>$anw</td><td>$leute</td><td>$preis</td><td>$schuld</td><td>$karte</td><td>$notiz</td><td>$addr</td></tr>\n";

    }

  } catch ( PDOException $e ) {
    echo "<i>Fehler beim Abrufen der Familien</i><br>" . $e->getMessage();

  }

  echo "</div></body>\n</html>";

  exit;
}

// Load csv backup
if ( isset( $_GET['load_backup'] ) ) {
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
  if ( isset( $_POST['load_backup'] ) ) {
    try {
      $toEncode = tablesCoding( $table );
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
                if ( $name != 'Personen' && $name != 'Straße' ) $names[] = $name;
                if ( $name == 'Personen' && !in_array( 'Erwachsene', $names ) ) $names[] = 'Erwachsene';
                if ( $name == 'Personen' && !in_array( 'Kinder', $names ) ) $names[] = 'Kinder';
                if ( $name == 'Straße' && !in_array( 'Adresse', $names ) ) $names[] = 'Adresse';
              } else {
                $msg[] = array(
                  "Spalte " . $name . " nicht verfügbar",
                  "warn",
                );
              }
            }
          }
          $keys = array_map( function( $val ) { return ":" . $val; }, $names );
          $insertSql = "INSERT INTO " . $table . " (" . implode( ", ", $names ) . ") VALUES (" . implode( ", ", $keys ) . ");";
          $updateSql = "UPDATE " . $table . " SET ";
          $first = true;
          for ( $i = 0; $i < sizeof($keys); $i++ ) {
            if ( $names[$i] == 'ID' ) continue;
            $updateSql .= ( !$first ? ", " : "" ) . $names[$i] . " = " . $keys[$i];
            $first = false;
          }
          $updateSql .= " WHERE ID = :ID;";

          if ( isset( $used_cols['ID'] ) || isset( $used_cols['Name'] ) ) {
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
                      "Zeile " . strval( $cur + 2 ) . ": Name ungültig",
                      "warn",
                    );
                  } elseif ( $name == "ID" && !is_numeric( $objs[$cur][$name] ) ) {
                    $msg[] = array(
                      "Zeile " . strval( $cur + 2 ) . ": ID ungültig",
                      "warn",
                    );
                  }
                } else {
                  $msg[] = array(
                    "Zeile " . strval( $cur + 2 ) . " enthält Spalte " . $name . " nicht.",
                    "warn",
                  );
                }
              }
            }
            foreach ( $objs as $i => $obj ) {
              if ( ( isset( $obj['Name'] ) && empty( $obj['Name'] ) ) || ( isset( $obj['ID'] ) && !is_numeric($obj['ID']) ) ) {
                $msg[] = array(
                  "Überspringe Zeile " . strval( $i + 2 ),
                  "warn",
                );
                continue;
              }
              foreach ( $obj as $key => $value ) {
                $obj[$key] = iconv( "utf-8", "iso8859-9//TRANSLIT//IGNORE", $obj[$key] );
              }
              foreach ( $toEncode as $name ) {
                if ( isset( $obj[$name] ) ) {
                  $obj[$name] = rawurlencode( $obj[$name] );
                }
              }
              if ( isset( $obj['Personen'] ) ) {
                $matches = array();
                if ( preg_match("/(\d+)(?:\/(\d+))?/", $obj['Personen'], $matches ) ) {
                  $obj['Erwachsene'] = $matches[1];
                  $obj['Kinder'] = isset( $matches[2] ) ? $matches[2] : 0;
                } else {
                  $msg[] = array(
                    "Personen in Zeile " . strval( $i + 2 ) . " ungültig. Auf 0/0 gesetzt.",
                    "warn",
                  );
                  $obj['Erwachsene'] = 0;
                  $obj['Kinder'] = 0;
                }
              }
              $id = isset( $obj['ID'] ) ? $obj['ID'] : 0;
              $res = null;
              if ( isset( $obj['ID'] ) ) {
                $stmt = $conn->prepare( "SELECT ID FROM " . $table . " WHERE `ID` = :ID LIMIT 1;");
                $stmt->execute( array( ":ID" => $obj['ID'] ) );
              } elseif ( isset( $obj['Name'] ) ) {
                $stmt = $conn->prepare( "SELECT ID FROM " . $table . " WHERE `Name` = :Name LIMIT 1;");
                $stmt->execute( array( ":Name" => $obj['Name'] ) );
              }
              $res = $stmt->fetch( PDO::FETCH_ASSOC );
              if ( $res && isset( $res['ID'] ) ) $id = $res['ID'];

              $set = array();
              if ( $id ) $set[":ID"] = $id;
              foreach ( $obj as $key => $value ) {
                if ( $key == "ID" || $key == "Personen" ) continue;
                if ( ( $key == "lAnwesenheit" || $key == "Karte" ) && $value == "0000-00-00" ) {
                  $set[":".$key] = null;
                  continue;
                }
                $set[":".$key] = $value;
              }
              
              $sql = $id == 0 ? $insertSql : $updateSql;
              $stmt = $conn->prepare( $sql );
              $stmt->execute( $set );
            }
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

  echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<style>table { max-width: 100%; } table th { font-weight: bold; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; } .msg { margin: 0 10px; padding: 4px 6px; border: 1px solid gray; border-radius: 2px; } .msg.msg-ok { background-color: lightgreen; } .msg.msg-error { background-color: red; }</style>\n";
  echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
  foreach ( $msg as $m ) {
    echo "<p class=\"msg msg-" . $m[1] . "\">" . $m[0] . "</p>";
  }
  if ( !empty( $msg ) ) echo "<p></p>";
  echo "<div class=\"body\">";
  echo "<form action=\"?load_backup\" method=\"GET\"><input type=\"hidden\" name=\"load_backup\" value=\"true\" /><select name=\"table\">";
  $tables = array(
    'familien' => 'Familien',
    'orte' => 'Orte',
    'einstellungen' => 'Einstellungen',
    'logs' => 'Logs',
  );
  foreach ( $tables as $name => $display ) {
    echo "<option value=\"" . $name . "\" " . ( $name == $table ? "selected" : "" ) . ">" . $display . "</option>";
  }
  echo "</select><input type=\"submit\" value=\"Wählen\" /></form>";
  echo "<form action=\"?load_backup\" method=\"POST\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"load_backup\" value=\"true\" /><input type=\"hidden\" name=\"table\" value=\"" . $table . "\" />\n<table><tbody>\n<tr><th>Spalte</th><th>Laden</th>\n";
  foreach ( $selected_cols as $name => $enabled ) {
    echo "<tr><td><label for=\"" . $name . "\">" . $name . "</label></td><td><input type=\"checkbox\" id=\"" . $name . "\" name=\"cols[" . $name . "]\" " . ( $enabled ? "checked" : "" ) . " value=\"on\" /></td></tr>\n";
  }
  echo "<tr><td><i>Alle</i></td><td><input type=\"checkbox\" id=\"toggle_all\" checked /></td></tr>\n";
  echo "</tbody></table>\n";
  echo "<p><label title=\"Zeichen zwischen Spalten. Für Excel-Export ;\">CSV-Trennzeichen: <input type=\"text\" name=\"delimiter\" placeholder=\",\" size=\"1\" /></label><br /><label>Datei: <input type=\"file\" name=\"file\" /></label></p>\n";
  echo "<p><input type=\"submit\" value=\"Backup laden\" /></p>\n";
  echo "</form>\n";
  echo "<script>window.onload = function(){var boxes = document.getElementsByTagName('input'); document.getElementById('toggle_all').addEventListener('click', function() {for (var i = 0; i < boxes.length; i++) {if (boxes[i].type == 'checkbox') {boxes[i].checked = this.checked;}}});};</script>";
  echo "</div></body>\n</html>";

  exit;
}

// Create csv backup
if ( isset( $_GET['create_backup'] ) ) {
  $table = isset( $_REQUEST['table'] ) ? $_REQUEST['table'] : "familien";
  $msg = array();
  $tables = array(
    'familien' => 'Familien',
    'orte' => 'Orte',
    'einstellungen' => 'Einstellungen',
  );

  if ( isset( $_POST['create_backup'] ) ) {
    try {
      if ( !isset( $tables[$table] ) ) throw new Exception( "Tabelle ungültig!", 1 );
      
      $stmt = $conn->prepare( "SELECT * FROM " . $table );
      $stmt->execute();
      $fname = $tables[$table];

      $toDecode = tablesCoding( $table );

      header( 'Content-Description: File Transfer' );
      header( 'Content-Type: application/octet-stream' );
      header( 'Content-Disposition: attachment; filename=' . $fname . '.csv' );
      header( 'Content-Transfer-Encoding: binary' );
      header( 'Connection: Keep-Alive' );
      header( 'Expires: 0' );
      header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
      header( 'Pragma: public' );
      $res = $stmt->fetchAll( PDO::FETCH_ASSOC );
      $out = fopen( 'php://output', 'w' );
      foreach ( $res as $i => $obj ) {
        $line = array();
        $header = array();
        foreach ( $obj as $key => $value ) {
          if ( $i == 0 ) $header[] = $key;
          if ( in_array( $key, $toDecode ) ) $value = rawurldecode( $value );
          $value = iconv( "iso8859-9", "utf-8", $value );
          $line[] = $value;
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

  echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<style>table { max-width: 100%; } table th { font-weight: bold; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; } .msg { margin: 0 10px; padding: 4px 6px; border: 1px solid gray; border-radius: 2px; } .msg.msg-ok { background-color: lightgreen; } .msg.msg-error { background-color: red; }</style>\n";
  echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
  foreach ( $msg as $m ) {
    echo "<p class=\"msg msg-" . $m[1] . "\">" . $m[0] . "</p>";
  }
  if ( !empty( $msg ) ) echo "<p></p>";
  echo "<div class=\"body\">";
  echo "<form action=\"?create_backup\" method=\"POST\"><input type=\"hidden\" name=\"create_backup\" value=\"true\" /><select name=\"table\">";
  foreach ( $tables as $name => $display ) {
    echo "<option value=\"" . $name . "\" " . ( $name == $table ? "selected" : "" ) . ">" . $display . "</option>";
  }
  echo "</select><br /><input type=\"submit\" value=\"Herunterladen\" /></form>";
  echo "</div></body>\n</html>";

  exit;
}

function tablesCoding( $table ) {
  if ( $table == 'familien' ) {
    return array(
      'Name',
      'Ort',
      'Notizen',
      'Adresse',
    );
  } elseif ( $table == 'orte' ) {
    return array(
      'Name',
    );
  } elseif ( $table == 'einstellungen' ) {
    return array(
      'Name',
      'Val',
    );
  } else {
    return array();
  }
}

// Karte
if ( isset( $_GET['karte'] ) ) {
  echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";

  $d = isset( $_POST['designs'] ) ? $_POST['designs'] : '%5B%5D';
  $f = isset( $_POST['familie'] ) ? $_POST['familie'] : '%7B%7D';
  $designs = iconv( "iso8859-9", "utf-8", rawurldecode( $d ) ); $designs = preg_replace( "/[\n\r]/", "", $designs );
  $familie = iconv( "iso8859-9", "utf-8", rawurldecode( $f ) ); $familie = preg_replace( "/[\n\r]/", "", $familie );
  ?><style>html, body { margin: 0 } #testCanvas { border: 1px solid black }</style>
  <script type="text/javascript">
    var designs = JSON.parse( "<?php echo addslashes($designs); ?>" );
    var familie = JSON.parse( "<?php echo addslashes($familie); ?>" );
    for ( var i in familie ) {
      familie[i] = unescape( familie[i] );
    }

    var formats = {
      A3: [ 297, 420 ],
      A4: [ 210, 297 ],
      A5: [ 148, 210 ]
    };
    var pxConst = 3.7795275591;

    window.onload = function() {
      var d = document.getElementById( 'design-select' );

      for ( var i = 0; i < designs.length; i++ ) {
        var e = document.createElement( 'option' );
        e.value = i;
        var t = document.createTextNode( designs[i].name );
        e.appendChild( t );
        d.appendChild( e );
      }

      d.addEventListener( 'change', changeDesign );
      changeDesign();

      var b = document.getElementById( 'drucken' );
      b.addEventListener( 'click', function() {
        var c = document.getElementById( 'testCanvas' ),
          m = document.getElementById( 'menu' );
        c.style.border = "none";
        m.style.display = "none";
        window.print();
        c.style.border = "";
        m.style.display = "";
      } );
    };

    function changeDesign() {
      var d = document.getElementById( 'design-select' ),
        c = document.getElementById( 'testCanvas' );
      if ( d.value === "" ) return;
      design = designs[d.value];

      c.innerHTML = "";

      //Set canvas size
      var f = formats[design.format];
      var format = design.format;
      if ( typeof(f) !== "undefined" ) {
        c.style.height = f[0] * pxConst;
        c.style.width = f[1] * pxConst;
        c.style.border = "";
      } else if ( typeof(format) !== "undefined" && (format.match(/x/g) || []).length == 1 ) {
        f = design.format.split( "x" );
        c.style.height = f[0] * pxConst;
        c.style.width = f[1] * pxConst;
        c.style.border = "";
      } else if ( (typeof(format) !== "undefined" && (format === "none" || format === "")) || typeof(format) === "undefined" ) {
        c.style.height = "";
        c.style.width = "";
        c.style.border = "none";
      } else {
        console.debug( "Invalid format!" );
        return;
      }

      //Add specified elements
      if ( typeof(design.elements) !== "undefined" && design.elements.constructor.name === "Array" ) {
        for ( var i = 0; i < design.elements.length; i ++ ) {
          var e = design.elements[i];
          var h = "<div style=\"";

          if ( typeof(e.css) !== "undefined" && typeof(e.position) === "undefined" ) {
            h += ";" + e.css + "\"";
          }
          if ( typeof(e.css) !== "undefined" && typeof(e.position) !== "undefined" ) {
            if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
              h += ";" + e.css + ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
            }
          }
          if ( typeof(e.css) === "undefined" && typeof(e.position) !== "undefined" ) {
            if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
              h += ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
            }
          }
          h += "\" >";
          if ( typeof(e.html) !== "undefined" ) {
            html = unescape(e.html);
            for ( var prop in familie ) {
              var rg = new RegExp( "\(\?:\^\\$\|\(\?:\(\[\^\\\\\]\)\\$\)\)" + prop, "g" );
              html = html.replace( rg, "$1" + familie[prop] );
            }
            html = html.replace( /\n/g, '<br>' );
            html = html.replace( /\\/g, '' );
            h += html;
          }
          h += "</div>";

          c.innerHTML += h;
        }
      }
    }

    function escapeRegExp( s ) {
      return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }
  </script>
  <?php
  echo "</head>\n<body>\n";

  echo "<div id=\"testCanvas\" style=\"position:relative\"></div>\n";
  echo "<div id=\"menu\">\n<select id=\"design-select\">\n";
  //echo "<option value=\"\" selected disabled hidden></option>";
  echo "</select>\n";
  echo "<button id=\"drucken\" value=\"Drucken\">Drucken</button>\n</div>\n";
  echo "</body>\n</html>";
  exit;
}


?>