<?php

$last_sync = "(SELECT COALESCE(CONVERT(`val`, datetime), 0) FROM `einstellungen` WHERE `name` = 'last_sync')";

function page_sync() {
  global $conn;
  global $last_sync;
  
  echo "<!DOCTYPE html><html>\n<head>\n<title>Tischlein Deck Dich</title><meta charset=\"UTF-8\">\n";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  echo "<link href=\"?file=css\" rel=\"stylesheet\" />";
  echo "<script type=\"text/javascript\" src=\"?file=syncjs\"></script>";
  echo "</head>\n<body>\n";
  echo "<div id=\"header\" class=\"header\"><div><span><a href=\"?\"><img src=\"?file=logo\" class=\"logo\" /></a></span></div></div>\n";
  echo "<div class=\"body\">";

  if ( array_key_exists( 'synced', $_GET ) ) {
    echo "<p class=\"success alert\">Erfolgreich synchronisiert</p>";
  }
  
  try {
    $sql = "SELECT COUNT(*) AS `numOrte` FROM `orte` WHERE `deleted` = 1 OR `last_update` >= $last_sync";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $numOrte = $stmt->fetchColumn();
  } catch ( PDOException $e ) {
    echo "</select>\n";
    echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
    exit;
  }
  try {
    $sql = "SELECT COUNT(*) AS `numFamilien` FROM `familien` WHERE `deleted` = 1 OR `last_update` >= $last_sync";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $numFam = $stmt->fetchColumn();
  } catch ( PDOException $e ) {
    echo "</select>\n";
    echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
    exit;
  }

  echo "<p>Orte zu synchronisieren: <b>$numOrte</b>. Familien zu synchronisieren: <b>$numFam</b>.<br/>";
  echo "Einstellungen und Logs werden nicht synchronisiert.</p>";
  echo "<p><button id=\"start\">Start</button> &nbsp; <button onclick=\"window.close()\">Schließen</button></p>";

?>
  <div id="modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">&times;</span>
        <h2 class="modal-head">Modal Header</h2>
      </div>
      <div class="modal-body">Modal Body</div>
      <div class="modal-footer">
        <h3 class="modal-foot">Modal Footer</h3>
      </div>
    </div>
  </div>
<?php

  echo "</div></body>\n</html>";
}

?>