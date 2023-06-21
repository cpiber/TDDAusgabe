<?php

$last_sync = "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync')";

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
  
  $conn->exec( "SET time_zone = '+00:00'" );
  try {
    $sql = "SELECT COUNT(*) AS `numOrte` FROM `orte` WHERE `deleted` = 1 OR `last_update` >= $last_sync";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $numOrte = $stmt->fetchColumn();
  } catch ( PDOException $e ) {
    echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
    exit;
  }
  try {
    $sql = "SELECT COUNT(*) AS `numFamilien` FROM `familien` WHERE `deleted` = 1 OR `last_update` >= $last_sync";

    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $numFam = $stmt->fetchColumn();
  } catch ( PDOException $e ) {
    echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
    exit;
  }
  try {
    $stmt = $conn->prepare( $last_sync );
    $stmt->execute();
    $sync = $stmt->fetchColumn();
    $stmt = $conn->prepare( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncServer'" );
    $stmt->execute();
    $server = $stmt->fetchColumn();
    if ( !$server || ( substr( $server, 0, 7 ) !== "http://" && substr( $server, 0, 8 ) !== "https://" ) ) throw new Exception( "Not a valid server $server" );
  } catch ( Exception $e ) {
    echo "<i>Fehler bei abrufen des Servers</i><br>" . $e->getMessage();
    exit;
  }
  try {
    $context = stream_context_create( array(
      'http' => array(
        'method'  => 'GET',
        'ignore_errors' => true,
      ),
    ) );
    $response = @file_get_contents( $server . "?sync=" . $sync, false, $context );
    $serverdata = json_decode( $response, true );
    if ( is_null( $serverdata ) || !is_array( $serverdata ) || $serverdata['status'] != 'success' ) throw new Exception( "Error communicating with server: $response" );
  } catch ( Exception $e ) {
    echo "<i>Fehler bei abrufen des Servers</i><br>" . $e->getMessage();
    exit;
  }

  echo "<p>Lokale Orte zu synchronisieren: <b>$numOrte</b>. Lokale Familien zu synchronisieren: <b>$numFam</b>.<br/>";
  echo "Entfernte Orte zu synchronisieren: <b>{$serverdata['orte']}</b> (geschätzt). Entfernte Familien zu synchronisieren: <b>{$serverdata['familien']}</b> (geschätzt).<br/>";
  echo "Einstellungen und Logs werden nicht synchronisiert.</p>";
  echo "<p><button id=\"start\">Start</button></p>";

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