<?php


// Print-Version
function page_print() {
  global $conn;
  
  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title><meta charset=\"UTF-8\">\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<link href=\"?file=css\" rel=\"stylesheet\" />";
  echo "<style>table { width: 100%; } table th { text-align: left; } tbody > tr > * { border: 1px solid grey; padding: 5px; } h3 { margin-top: 1em; margin-bottom: 0; } span.check { width: 14px; height: 14px; border: 1px solid black; display: block; float: right; } p { font-size: 15px; }</style>\n";
  echo "</head>\n<body>\n";
  echo "<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" class=\"logo\" /></a></span></div></div>\n";
  $o = ( isset( $_GET['ort'] ) ? $_GET['ort'] : "Alle" );
  echo "<div class=\"body\"><form action=\"?page=print\" method=\"GET\">\n<input type=\"hidden\" name=\"page\" value=\"print\">\n<select id=\"ort\" name=\"ort\">\n<option value=\"Alle\">Alle</option>\n";

  try {
    $sql = "SELECT * FROM `orte`";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();

    $orte = array();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    foreach ( $stmt->fetchAll() as $r) {
      $orte[$r['Name']] = $r;
      $s = ( $r['Name'] == $o ? " selected" : "" );
      $name = htmlspecialchars($r['Name']);
      printf( "<option value=\"%s\"%s>%s</option>\n", $name, $s, $name );
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
      printf( "<option value=\"%s\"%s>Gruppe %s</option>", $i, $s, $i );
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
        printf( "<h1>%s</h1>\n<table><tbody>\n", htmlentities($ort) );
      }
      if ( $gruppe != $r['Gruppe'] ) {
        $gruppe = $r['Gruppe'];
        printf( "<tr><td colspan=\"8\"><h3>Gruppe %s</h3></td></tr>\n<tr><th>Nummer</th><th>Name</th><th>Erw. / Kinder</th><th>Preis</th><th>Schulden</th><th>Karte</th><th>Notizen</th><th>Adresse</th></tr>\n", $gruppe );
      }

      $num = $r['Num'];
      $name = htmlentities($r['Name']);
      $anw = "<span class=\"check\"></span>";
      $leute = sprintf( "%s / %s", $r['Erwachsene'], $r['Kinder'] );
      $preis = $einst['Preis'];
      $preis = strtr( $preis, array( 'e' => intval($r['Erwachsene']), 'k' => intval($r['Kinder']) ) );
      ob_start();
      try {
        $preis = eval( sprintf( "return floatval(%s);", preg_replace( '/[^0-9\+\-\*\/\(\)\.><=]/', '', $preis ) ) );
        // keine Zahl -> Fehler
        if ( $preis === false ) {
          $preis = "<i>Fehler in der Preis-Formel</i>";
        } else {
          $preis = sprintf( "%s€", number_format( $preis, 2 ) );
        }
      } catch ( ParseError $e ) {
        $preis = "<i>Fehler in der Preis-Formel</i>";
      }
      ob_clean();
      $schuld = sprintf( "%s€", number_format( floatval($r['Schulden']), 2 ) );
      date_default_timezone_set( "UTC" );
      $karte = strtotime( $r['Karte'] );
      $karte = date( "d. m. Y", $karte );
      $karte = ( $r['Karte'] == "" || $r['Karte'] == "0000-00-00" ? "" : $karte );
      $notiz = htmlentities($r['Notizen']);
      $addr = htmlentities($r['Adresse']);
      printf( "<tr><td>%s</td><td><span>%s</span>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $num, $name, $anw, $leute, $preis, $schuld, $karte, $notiz, $addr );

    }

  } catch ( PDOException $e ) {
    echo "<i>Fehler beim Abrufen der Familien</i><br>" . $e->getMessage();

  }

  echo "</div></body>\n</html>";
}

?>