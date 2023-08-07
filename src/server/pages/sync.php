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
  
  $conn->exec( "SET time_zone = '+00:00'" );
  $synctime = floatval( $conn->query( $last_sync )->fetchColumn() );

  $sql = "SELECT COUNT(*) AS `numOrte` FROM `orte` WHERE `deleted` = 1 OR `last_update` > $last_sync";
  if ( $synctime === 0 ) $sql = "SELECT COUNT(*) AS `numOrte` FROM `orte`";
  $numOrte = $conn->query( $sql )->fetchColumn();

  $sql = "SELECT COUNT(*) AS `numFamilien` FROM `familien` WHERE `deleted` = 1 OR `last_update` > $last_sync";
  if ( $synctime === 0 ) $sql = "SELECT COUNT(*) AS `numFamilien` FROM `familien`";
  $numFam = $conn->query( $sql )->fetchColumn();

  $numFiles = count( getStaticFiles( STATIC_DIR ) );

  try {
    $sync = $conn->query( "(SELECT COALESCE(CONVERT(`val`, datetime)+0, 0) FROM `einstellungen` WHERE `name` = 'last_sync_servertime')" )->fetchColumn();
    $server = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncServer'" )->fetchColumn();
    if ( !$server || ( substr( $server, 0, 7 ) !== "http://" && substr( $server, 0, 8 ) !== "https://" ) ) throw new Exception( "Not a valid server $server" );
    $key = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'SyncKey'" )->fetchColumn();
  } catch ( Exception $e ) {
    echo "<i>Fehler bei abrufen des Servers</i><br>" . $e->getMessage();
    exit;
  }
  try {
    $serverdata = server_send( $key, $server, "status&sync=$sync", HTTP_GET ); 
    if ( is_null( $serverdata ) || !is_array( $serverdata ) || $serverdata['status'] !== 'success' ) throw new Exception( "Error communicating with server: $serverdata" );
  } catch ( HTTPException $e ) {
    echo "<i>Fehler bei abrufen des Servers</i><br>" . $e->getMessage() . " - " . strval( $e->serverdata );
    exit;
  } catch ( Exception $e ) {
    echo "<i>Fehler bei abrufen des Servers</i><br>" . $e->getMessage();
    exit;
  }

  echo "<p>Lokale Orte zu synchronisieren: <b>$numOrte</b>. Lokale Familien zu synchronisieren: <b>$numFam</b>.<br/>";
  echo "Entfernte Orte zu synchronisieren: <b>{$serverdata['orte']}</b> (geschätzt). Entfernte Familien zu synchronisieren: <b>{$serverdata['familien']}</b> (geschätzt).<br/>";
  echo "Differenz in Dateien: "; echo abs(intval($serverdata['static']) - $numFiles); echo ".<br/>";
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
