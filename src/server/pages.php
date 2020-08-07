<?php

require "pages/print.php";
require "pages/card.php";
require "pages/backup_create.php";
require "pages/backup_load.php";

if ( array_key_exists( 'page', $_GET ) ) {
  switch ( $_GET['page'] ) {
    case "print":
      page_print();
      break;
    case "card":
      page_card();
      break;
    case "backup/create":
      page_backupcreate();
      break;
    case "backup/load":
      page_backupload();
      break;
      
    default:
      http_response_code( 404 );
      echo "<html>\n<head>\n<title>Tischlein Deck Dich - 404</title>\n";
      echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
      echo "<link href=\"?file=css\" rel=\"stylesheet\" />";
      echo "</head><body>\n";
      echo "<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"?file=logo\" class=\"logo\" /></a></span></div></div>\n";
      echo "<h1>Fehler 404 Nicht gefunden</h1>";
      echo "<div class=\"body\"><p>Die gefragte Seite wurde nicht gefunden.</p></div>";
      echo "</body></html>";
      break;
  }
  $conn = null;
  exit;
}


?>